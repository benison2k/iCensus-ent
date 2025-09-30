<?php
// /app/controllers/ResidentController.php
require_once __DIR__ . '/../models/Resident.php';

class ResidentController {
    
    /**
     * Display the residents page with all residents.
     */
    public function index() {
        // Authenticate user
        $user_role = $_SESSION['user']['role_name'] ?? '';
        if (!in_array($user_role, ['Barangay Admin', 'Encoder'])) {
             http_response_code(403);
             die("<h1>403 Forbidden</h1>");
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $residentModel = new Resident($db);
        
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'residents' => $residentModel->getAll(), // Pass all residents to the view
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);

        view('residents/index', $data);
    }

    /**
     * Handle all AJAX requests (get, save, delete, filter).
     * This method replaces residents_process.php
     */
    public function process() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $residentModel = new Resident($db);
        
        $action = $_REQUEST['action'] ?? 'save';

        try {
            switch ($action) {
                case 'get':
                    $resident = $residentModel->find($_GET['resident_id']);
                    echo json_encode(['status' => 'success', 'resident' => $resident]);
                    break;

                case 'save':
                    $residentId = $residentModel->save($_POST);
                    log_action('INFO', empty($_POST['resident_id']) ? 'RESIDENT_CREATE' : 'RESIDENT_UPDATE', "Resident ID#{$residentId} was saved.");
                    $_SESSION['modal'] = ['message' => 'Resident saved successfully', 'type' => 'success'];
                    echo json_encode(['status' => 'success']);
                    break;
                
                case 'delete':
                    $residentModel->delete($_POST['id']);
                    log_action('INFO', 'RESIDENT_DELETE', "Resident record ID#{$_POST['id']} was deleted.");
                    echo json_encode(['status' => 'success']);
                    break;
            }
        } catch (Exception $e) {
            log_action('ERROR', 'DB_ERROR', 'Error in ResidentController: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
        }
        exit;
    }
}