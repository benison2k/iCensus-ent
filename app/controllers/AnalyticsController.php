<?php
// app/controllers/AnalyticsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Analytics1.php'; // NEW: Include the new model
require_once __DIR__ . '/../models/Residents.php';


class AnalyticsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) { header('Location: /iCensus-ent/public/login'); exit; }
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db; // For logging
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
    }

    /**
     * NEW: API endpoint to fetch data for a single dynamic chart.
     */
    public function dynamicData() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartId = $_GET['chart_id'] ?? 0;
        $userId = $_SESSION['user']['id'];

        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics1($db); // Use the new Analytics1 model
        
        $data = $analyticsModel->getDynamicChartData($chartId, $userId);
        
        echo json_encode($data);
        exit;
    }

    /**
     * NEW: API endpoint to get the list of all available charts for the current user.
     */
    public function getCharts() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['id'];
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics1($db); // Use the new Analytics1 model

        $charts = $analyticsModel->getAvailableCharts($userId);

        echo json_encode(['status' => 'success', 'charts' => $charts]);
        exit;
    }
    
    /**
     * NEW: API endpoint to save a chart definition.
     */
    public function saveChart() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        try {
            $db = new Database(require __DIR__ . '/../../config/database.php');
            $analyticsModel = new Analytics1($db);
            
            // The saveChart method will handle both creating and updating
            $chartId = $analyticsModel->saveChart($_POST, $_SESSION['user']['id']);
            
            log_action('INFO', 'CHART_SAVED', "User saved chart definition ID#{$chartId}.");
            echo json_encode(['status' => 'success', 'message' => 'Chart saved successfully!', 'chart_id' => $chartId]);

        } catch (Exception $e) {
            log_action('ERROR', 'CHART_SAVE_FAIL', 'Error saving chart: ' . $e->getMessage());
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // --- Method to get filtered residents ---
    public function getFilteredResidents() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $db = new Database(require __DIR__ . '/../../config/database.php');
        $residentModel = new Resident($db);

        $filters = $_GET; 

        $residents = $residentModel->getFiltered($filters);
        echo json_encode(['status' => 'success', 'residents' => $residents]);
        exit;
    }

    public function index() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'available_columns' => [
                'full_name' => 'Full Name', 'address' => 'Full Address', 'dob' => 'Date of Birth',
                'age' => 'Age', 'gender' => 'Gender', 'civil_status' => 'Civil Status',
                'contact_number' => 'Contact Number', 'email' => 'Email', 'blood_type' => 'Blood Type',
                'nationality' => 'Nationality', 'status' => 'Resident Status', 'date_added' => 'Date Added'
            ],
            'available_charts' => [
                'gender' => 'Gender Distribution', 'age' => 'Age Groups', 'purok' => 'Population by Purok',
                'civil_status' => 'Civil Status', 'blood_type' => 'Blood Type', 'nationality' => 'Nationality',
            ]
        ];
        view('analytics/index', $data);
    }

    // This is the old data endpoint, which can be removed later.
    public function data() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $metric = $_GET['metric'] ?? '';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        echo json_encode($analyticsModel->getChartData($metric, $startDate, $endDate));
        exit;
    }

    public function getLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        echo json_encode($analyticsModel->getLayoutForUser($_SESSION['user']['id']));
        exit;
    }

    public function saveLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $layout_data = file_get_contents('php://input');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $success = $analyticsModel->saveLayoutForUser($_SESSION['user']['id'], $layout_data);
        if($success) {
            log_action('INFO', 'ANALYTICS_LAYOUT_SAVE', 'User saved their analytics dashboard layout.');
        }
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function resetLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $success = $analyticsModel->deleteLayoutForUser($_SESSION['user']['id']);
        if($success) {
            log_action('INFO', 'ANALYTICS_LAYOUT_RESET', 'User reset their analytics dashboard layout to default.');
        }
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function generateReport() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $reportData = $analyticsModel->getDataForReport($_POST);
        log_action('INFO', 'REPORT_GENERATED', 'User generated a custom report.');
        view('analytics/report', $reportData);
    }
}
