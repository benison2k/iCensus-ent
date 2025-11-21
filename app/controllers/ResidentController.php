<?php
// /app/controllers/ResidentController.php
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/Residents.php';
// Note: Csrf class implementation is assumed to be available via init.php or autoload

class ResidentController {
    
    public function index() {
        // Authenticate user
        $user_role = $_SESSION['user']['role_name'] ?? '';
        if (!in_array($user_role, ['Barangay Admin', 'Encoder'])) {
             http_response_code(403);
             die("<h1>403 Forbidden</h1>");
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);

        $residentModel = new Resident($db);
        
        $viewMode = $_GET['view'] ?? 'approved';
        $isPendingView = ($user_role === 'Barangay Admin' && $viewMode === 'pending');

        if ($isPendingView) {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
            $pendingData = $residentModel->getPendingPaginated($page, $pageSize);
            $residents = $pendingData['residents'];
            $totalResidents = $pendingData['total'];
            $totalPages = $pendingData['totalPages'];
        } else {
            $residents = $residentModel->getAll();
        }

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'residents' => $residents,
            'household_heads' => $residentModel->getHouseholdHeads(),
            'civil_statuses' => $residentModel->getDistinctValues('civil_status'),
            'blood_types' => $residentModel->getDistinctValues('blood_type'),
            'nationalities' => $residentModel->getDistinctValues('nationality'),
            'residency_statuses' => $residentModel->getDistinctValues('residency_status'),
            'relationships' => $residentModel->getDistinctValues('relationship'),
            'educations' => $residentModel->getDistinctValues('educational_attainment'),
            'occupations' => $residentModel->getDistinctValues('occupation'),
            'ownership_statuses' => $residentModel->getDistinctValues('ownership_status'),
            'isPendingView' => $isPendingView,
            'pending_count' => ($user_role === 'Barangay Admin') ? $residentModel->getPendingCount() : 0,
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        
        if ($isPendingView) {
            $data['totalResidents'] = $totalResidents;
            $data['totalPages'] = $totalPages;
            $data['currentPage'] = $page;
            $data['pageSize'] = $pageSize;
        }
        
        unset($_SESSION['modal']);

        view('residents/index', $data);
    }
    
    public function findByAddress() {
        // No CSRF check needed for GET requests typically
        header('Content-Type: application/json');
        $house_no = $_GET['house_no'] ?? '';
        $street = $_GET['street'] ?? '';
        $purok = $_GET['purok'] ?? '';

        if (empty($house_no) || empty($street) || empty($purok)) {
            echo json_encode([]);
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $residentModel = new Resident($db);

        $residents = $residentModel->findByAddress($house_no, $street, $purok);
        echo json_encode($residents);
        exit;
    }

    public function process() {
        header('Content-Type: application/json');
        
        // Removed global CSRF check here.
        
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $residentModel = new Resident($db);
        
        $action = $_REQUEST['action'] ?? 'save';

        try {
            switch ($action) {
                case 'get':
                    // GET action (Read-only) - NO CSRF CHECK REQUIRED HERE
                    
                    // FIX: Use $_REQUEST for robust ID capture (covers GET/POST/id/resident_id)
                    $resident_id = $_REQUEST['id'] ?? $_REQUEST['resident_id'] ?? null;

                    if (!$resident_id) {
                        echo json_encode(['status' => 'error', 'message' => 'Resident ID is missing.']);
                        exit;
                    }
                    
                    // FIX: Explicitly cast ID to integer to prevent type mismatch in model query
                    $resident_id = (int)$resident_id;

                    // FIX: Use findAnyStatus() method to ensure pending residents are found.
                    if (method_exists($residentModel, 'findAnyStatus')) {
                         $resident = $residentModel->findAnyStatus($resident_id);
                    } else {
                         // Fallback to the original find() method if the new method is not implemented
                         $resident = $residentModel->find($resident_id);
                    }

                    if (!$resident) {
                        echo json_encode(['status' => 'error', 'message' => 'Resident not found.']);
                        exit;
                    }

                    echo json_encode(['status' => 'success', 'resident' => $resident]);
                    break;

                case 'save':
                    // FIX: CSRF Check applied only to WRITE operations
                    if (!class_exists('Csrf') || !Csrf::verify($_POST['csrf_token'] ?? '')) {
                        echo json_encode(['status' => 'error', 'message' => 'Security Token Invalid. Please reload.']);
                        exit;
                    }
                    // END FIX

                    $resident_id_post = $_POST['resident_id'] ?? null;
                    $is_new = empty($resident_id_post);
                    
                    if (!$is_new) {
                        // FIX: Explicitly cast ID to integer for lookup
                        $resident_id_post = (int)$resident_id_post;

                        // FIX: Use findAnyStatus when fetching old data for comparison
                        if (method_exists($residentModel, 'findAnyStatus')) {
                             $old_data = $residentModel->findAnyStatus($resident_id_post);
                        } else {
                             $old_data = $residentModel->find($resident_id_post);
                        }
                        $_POST['resident_id'] = $resident_id_post; // Restore casted ID
                    }
                    
                    if ($is_new) {
                        $_POST['encoded_by'] = $_SESSION['user']['id'];
                    }
                    
                    // Remove csrf_token from POST data before saving
                    unset($_POST['csrf_token']);

                    $residentId = $residentModel->save($_POST);
                    $full_name = htmlspecialchars($_POST['first_name'] . ' ' . $_POST['last_name']);

                    if ($is_new) {
                        log_action('INFO', 'RESIDENT_CREATE', "New resident record created: {$full_name} (ID#{$residentId}).");
                        $message = 'New resident added successfully!';
                    } else {
                        // FIX: Explicitly cast ID to integer for lookup
                        $residentId = (int)$residentId; 

                        // FIX: Use findAnyStatus when fetching new data for comparison
                        if (method_exists($residentModel, 'findAnyStatus')) {
                             $new_data = $residentModel->findAnyStatus($residentId);
                        } else {
                             $new_data = $residentModel->find($residentId);
                        }
                        
                        $changes = array_diff_assoc($new_data, $old_data);
                        $log_details = "Updated resident ID#{$residentId}.";
                        if (!empty($changes)) {
                            $log_details .= " Changes: ";
                            foreach($changes as $key => $value) {
                                $log_details .= "{$key} changed from '{$old_data[$key]}' to '{$value}', ";
                            }
                            $log_details = rtrim($log_details, ', ');
                            $log_details .= ".";
                        }
                        log_action('INFO', 'RESIDENT_UPDATE', $log_details);
                        $message = 'Resident updated successfully!';
                    }
                    
                    // FIX: Use findAnyStatus to get the final saved resident data
                    if (method_exists($residentModel, 'findAnyStatus')) {
                         $savedResident = $residentModel->findAnyStatus($residentId);
                    } else {
                         $savedResident = $residentModel->find($residentId);
                    }

                    echo json_encode(['status' => 'success', 'message' => $message, 'resident' => $savedResident, 'is_new' => $is_new]);
                    break;
                
                case 'delete':
                    // FIX: CSRF Check applied only to WRITE operations
                    if (!class_exists('Csrf') || !Csrf::verify($_POST['csrf_token'] ?? '')) {
                        echo json_encode(['status' => 'error', 'message' => 'Security Token Invalid. Please reload.']);
                        exit;
                    }
                    // END FIX

                    if ($_SESSION['user']['role_name'] === 'Encoder') {
                        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete residents.']);
                        exit;
                    }
                    // FIX: Explicitly cast ID to integer for lookup
                    $id_to_delete = (int)($_POST['id'] ?? 0);

                    // Use findAnyStatus here if deleting unapproved records is allowed
                    $resident_to_delete = $residentModel->find($id_to_delete);
                    if($resident_to_delete) {
                        $residentModel->delete($id_to_delete);
                        $full_name = htmlspecialchars($resident_to_delete['first_name'] . ' ' . $resident_to_delete['last_name']);
                        log_action('INFO', 'RESIDENT_DELETE', "Resident record for {$full_name} (ID#{$id_to_delete}) was deleted.");
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Resident deleted successfully.']);
                    break;
            }
        } catch (Exception $e) {
            log_action('ERROR', 'DB_ERROR', 'Error in ResidentController: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
        }
        exit;
    }

    public function approve() {
        if ($_SESSION['user']['role_name'] !== 'Barangay Admin') { die("Forbidden"); }
        
        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $GLOBALS['db'] = $db;
        $residentModel = new Resident($db);

        // FIX: Explicitly cast ID to integer for lookup
        $residentId = (int)($_GET['id'] ?? 0);
        
        if ($residentId) {
            $residentModel->approve($residentId, $_SESSION['user']['id']);
            log_action('INFO', 'RESIDENT_APPROVED', "Admin approved resident entry ID#{$residentId}.");
            $_SESSION['modal'] = ['message' => 'Resident approved successfully.', 'type' => 'success'];
        }
        
        header("Location: /iCensus-ent/public/residents?view=pending");
        exit;
    }
    
    public function searchHeads() {
        header('Content-Type: application/json');
        $term = $_GET['term'] ?? '';
    
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $residentModel = new Resident($db);
    
        $heads = $residentModel->searchHeads($term);
        echo json_encode($heads);
        exit;
    }

    public function reject() {
        if ($_SESSION['user']['role_name'] !== 'Barangay Admin') { die("Forbidden"); }

        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $GLOBALS['db'] = $db;
        $residentModel = new Resident($db);
        
        // FIX: Explicitly cast ID to integer for lookup
        $residentId = (int)($_GET['id'] ?? 0);
        
        if ($residentId) {
            $residentModel->reject($residentId);
            log_action('INFO', 'RESIDENT_REJECTED', "Admin rejected pending resident entry ID#{$residentId}.");
            $_SESSION['modal'] = ['message' => 'Resident entry rejected.', 'type' => 'success'];
        }

        header("Location: /iCensus-ent/public/residents?view=pending");
        exit;
    }

    public function approveAll() {
        if ($_SESSION['user']['role_name'] !== 'Barangay Admin') { die("Forbidden"); }
        
        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $GLOBALS['db'] = $db;
        $residentModel = new Resident($db);

        $approvedCount = $residentModel->approveAll($_SESSION['user']['id']);
        
        if ($approvedCount > 0) {
            log_action('INFO', 'RESIDENT_APPROVE_ALL', "Admin approved all {$approvedCount} pending resident entries.");
            $_SESSION['modal'] = ['message' => "Successfully approved all {$approvedCount} residents.", 'type' => 'success'];
        } else {
            $_SESSION['modal'] = ['message' => "No pending residents to approve.", 'type' => 'info'];
        }
        
        header("Location: /iCensus-ent/public/residents?view=pending");
        exit;
    }
}