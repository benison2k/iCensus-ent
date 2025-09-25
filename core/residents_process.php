<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';
// Include the central logging functions file
if (file_exists(__DIR__ . '/../core/functions.php')) {
    require_once __DIR__ . '/../core/functions.php';
}

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

$user_role = $_SESSION['user']['role_name'] ?? '';
$action = $_GET['action'] ?? $_POST['action'] ?? 'save';

function post($key) {
    return $_POST[$key] ?? '';
}

try {
    switch($action) {

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

        case 'delete':
            // Only Barangay Admins can delete
            if ($user_role !== 'Barangay Admin') {
                echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
                exit;
            }
            $resident_id = $_POST['id'] ?? 0;

            // Log the deletion action before it happens
            log_action('INFO', 'RESIDENT_DELETE', "Resident record ID#{$resident_id} was deleted.");

            $stmt = $pdo->prepare("DELETE FROM residents WHERE id=?");
            $stmt->execute([$resident_id]);
            echo json_encode(['status'=>'success']);
            break;

        case 'filter':
            // This part remains the same
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $gender = $_GET['gender'] ?? '';
            $age_min = $_GET['age_min'] ?? '';
            $age_max = $_GET['age_max'] ?? '';
            $purok = $_GET['purok'] ?? '';
            $barangay = $_GET['barangay'] ?? '';
            
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age FROM residents";
            $conditions = [];
            $params = [];

            if($search) {
                $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            if($status) { $conditions[] = "status = ?"; $params[] = $status; }
            if($gender) { $conditions[] = "gender = ?"; $params[] = $gender; }
            if($purok) { $conditions[] = "purok = ?"; $params[] = $purok; }
            if($barangay) { $conditions[] = "barangay = ?"; $params[] = $barangay; }
            if($age_min) { $conditions[] = "TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?"; $params[] = $age_min; }
            if($age_max) { $conditions[] = "TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?"; $params[] = $age_max; }

            if(!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status'=>'success','residents'=>$residents]);
            break;

        case 'save':
        default:
            // Admins and Encoders can save
            $allowed_roles = ['Barangay Admin', 'Encoder'];
            if (!in_array($user_role, $allowed_roles)) {
                echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
                exit;
            }
            $resident_id = post('resident_id');

            $data = [
                'first_name' => post('first_name'), 'middle_name'=> post('middle_name'),
                'last_name'   => post('last_name'), 'suffix'      => post('suffix'),
                'nickname'    => post('nickname'), 'dob'         => post('dob'),
                'gender'      => post('gender'), 'civil_status'=> post('civil_status'),
                'blood_type'  => post('blood_type'), 'nationality' => post('nationality'),
                'contact_number'=> post('contact_number'), 'email'       => post('email'),
                'emergency_name'=> post('emergency_name'), 'emergency_relation'=> post('emergency_relation'),
                'emergency_number'=> post('emergency_number'), 'house_no'    => post('house_no'),
                'street'      => post('street'), 'purok'       => post('purok'),
                'barangay'    => post('barangay'), 'head_of_household'=> post('head_of_household'),
                'relationship'=> post('relationship'), 'status'      => post('status')
            ];

            if(empty($resident_id)) {
                // CREATE new resident
                $data['date_added'] = date('Y-m-d H:i:s');
                $fields = array_keys($data);
                $placeholders = array_fill(0,count($fields),'?');
                $stmt = $pdo->prepare("INSERT INTO residents (".implode(",",$fields).") VALUES (".implode(",",$placeholders).")");
                $stmt->execute(array_values($data));
                $new_resident_id = $pdo->lastInsertId();

                log_action('INFO', 'RESIDENT_CREATE', "New resident record created: " . post('first_name') . " " . post('last_name') . " (ID#{$new_resident_id}).");
                $_SESSION['modal'] = ['message'=>'Resident added successfully','type'=>'success'];

            } else {
                // UPDATE existing resident
                $stmt_before = $pdo->prepare("SELECT * FROM residents WHERE id=?");
                $stmt_before->execute([$resident_id]);
                $original_data = $stmt_before->fetch(PDO::FETCH_ASSOC);

                $data['last_updated'] = date('Y-m-d H:i:s');
                $setStr = implode(',', array_map(fn($f)=>"$f=?", array_keys($data)));
                $stmt = $pdo->prepare("UPDATE residents SET $setStr WHERE id=?");
                $values = array_values($data);
                $values[] = $resident_id;
                $stmt->execute($values);

                $changes = [];
                foreach ($data as $key => $value) {
                    if (isset($original_data[$key]) && $original_data[$key] != $value) {
                        $changes[] = "{$key} changed from '{$original_data[$key]}' to '{$value}'";
                    }
                }
                $details = "Updated resident ID#{$resident_id}. ";
                if (!empty($changes)) {
                    $details .= "Changes: " . implode(', ', $changes) . ".";
                } else {
                    $details .= "No data fields were changed.";
                }

                log_action('INFO', 'RESIDENT_UPDATE', $details);
                $_SESSION['modal'] = ['message'=>'Resident updated successfully','type'=>'success'];
            }

            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
                echo json_encode(['status'=>'success']);
            } else {
                header("Location: ../pages/residents.php");
            }
            break;
    }
} catch (Exception $e) {
    log_action('ERROR', 'DB_ERROR', 'Error in residents_process.php: ' . $e->getMessage());
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
        echo json_encode(['status'=>'error', 'message'=>'An internal error occurred.']);
    } else {
        $_SESSION['modal'] = ['message'=>'An internal error occurred.', 'type'=>'error'];
        header("Location: ../pages/residents.php");
    }
}
exit;