<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

// --- Bouncer ---
if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$user = $_SESSION['user'];
$pdo = $db->getPdo();

// Get user role from session for permission checks
$user_role = $_SESSION['user']['role_name'] ?? '';

$action = $_GET['action'] ?? $_POST['action'] ?? 'save';

function post($key) {
    return $_POST[$key] ?? '';
}

switch($action) {

    // Fetch a single resident for modal
    case 'get':
        $resident_id = $_GET['resident_id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM residents WHERE id=?");
        $stmt->execute([$resident_id]);
        $resident = $stmt->fetch(PDO::FETCH_ASSOC);
        if($resident) {
            echo json_encode(['status'=>'success','resident'=>$resident]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Resident not found']);
        }
        break;

    // Delete resident
    case 'delete':
        // ADDED: Only Admins can delete
        if ($user_role !== 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
            exit;
        }
        $resident_id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM residents WHERE id=?");
        $stmt->execute([$resident_id]);
        echo json_encode(['status'=>'success']);
        break;

    // Filter residents (AJAX)
    case 'filter':
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $gender = $_GET['gender'] ?? '';
        $age_min = $_GET['age_min'] ?? '';
        $age_max = $_GET['age_max'] ?? '';
        $purok = $_GET['purok'] ?? '';
        $barangay = $_GET['barangay'] ?? '';
        
        $sql = "SELECT * FROM residents";
        $conditions = [];
        $params = [];

        if($search) {
            $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ? OR CONCAT(house_no, ' ', street) LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if($status) { $conditions[] = "status = ?"; $params[] = $status; }
        if($gender) { $conditions[] = "gender = ?"; $params[] = $gender; }
        if($purok) { $conditions[] = "purok = ?"; $params[] = $purok; }
        if($barangay) { $conditions[] = "barangay = ?"; $params[] = $barangay; }

        if(!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filtered = array_filter($residents, function($r) use($age_min, $age_max){
             if (empty($r['dob']) || $r['dob'] === '0000-00-00') {
                $age = null;
            } else {
                $age = (new DateTime($r['dob']))->diff(new DateTime())->y;
            }
            if($age_min !== '' && $age < (int)$age_min) return false;
            if($age_max !== '' && $age > (int)$age_max) return false;
            return true;
        });

        // Add age field for table
        $final_residents = array_map(function($r){
            if (empty($r['dob']) || $r['dob'] === '0000-00-00') {
                $r['age'] = 'N/A';
            } else {
                $r['age'] = (new DateTime($r['dob']))->diff(new DateTime())->y;
            }
            return $r;
        }, array_values($filtered));

        echo json_encode(['status'=>'success','residents'=>$final_residents]);
        break;

    // Save or update resident
    case 'save':
    default:
        // ADDED: Admins and Encoders can save
        if ($user_role !== 'Admin' && $user_role !== 'Clerk/Encoder') {
            echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
            exit;
        }
        $resident_id = post('resident_id');

        $data = [
            'first_name' => post('first_name'),
            'middle_name'=> post('middle_name'),
            'last_name'   => post('last_name'),
            'suffix'      => post('suffix'),
            'nickname'    => post('nickname'),
            'dob'         => post('dob'),
            'gender'      => post('gender'),
            'civil_status'=> post('civil_status'),
            'blood_type'  => post('blood_type'),
            'nationality' => post('nationality'),
            'contact_number'=> post('contact_number'),
            'email'       => post('email'),
            'emergency_name'=> post('emergency_name'),
            'emergency_relation'=> post('emergency_relation'),
            'emergency_number'=> post('emergency_number'),
            'house_no'    => post('house_no'),
            'street'      => post('street'),
            'purok'       => post('purok'),
            'barangay'    => post('barangay'),
            'head_of_household'=> post('head_of_household'),
            'relationship'=> post('relationship'),
            'status'      => post('status')
        ];

        if(empty($resident_id)) {
            $data['date_added'] = date('Y-m-d H:i:s');
            $fields = array_keys($data);
            $placeholders = array_fill(0,count($fields),'?');
            $stmt = $pdo->prepare("INSERT INTO residents (".implode(",",$fields).") VALUES (".implode(",",$placeholders).")");
            $stmt->execute(array_values($data));
            $_SESSION['modal'] = ['message'=>'Resident added successfully','type'=>'success'];
        } else {
            $data['last_updated'] = date('Y-m-d H:i:s');
            $setStr = implode(',', array_map(fn($f)=>"$f=?", array_keys($data)));
            $stmt = $pdo->prepare("UPDATE residents SET $setStr WHERE id=?");
            $values = array_values($data);
            $values[] = $resident_id;
            $stmt->execute($values);
            $_SESSION['modal'] = ['message'=>'Resident updated successfully','type'=>'success'];
        }

        // Detect if this is AJAX or normal form submit
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
            echo json_encode(['status'=>'success']);
        } else {
            header("Location: ../pages/residents.php");
        }
        break;
}