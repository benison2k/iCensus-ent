<?php
// app/controllers/AnalyticsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: /iCensus-ent/public/login');
            exit;
        }
        // Add session refresh on every page load
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
    }

    public function index() {
        $this->checkAuth();
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'puroks' => $analyticsModel->getDistinct('purok'),
            'civil_statuses' => $analyticsModel->getDistinct('civil_status'),
            'available_columns' => [
                'full_name' => 'Full Name', 'address' => 'Full Address', 'dob' => 'Date of Birth',
                'age' => 'Age', 'gender' => 'Gender', 'civil_status' => 'Civil Status',
                'contact_number' => 'Contact Number', 'email' => 'Email', 'blood_type' => 'Blood Type',
                'nationality' => 'Nationality', 'status' => 'Resident Status', 'date_added' => 'Date Added'
            ],
            'available_charts' => [
                'gender' => 'Gender Distribution (Pie)', 'age' => 'Age Groups (Column)',
                'purok' => 'Population by Purok (Bar)', 'civil_status' => 'Civil Status (Donut)',
                'blood_type' => 'Blood Types (Pie)', 'nationality' => 'Nationality (Bar)',
            ]
        ];
        
        view('analytics/index', $data);
    }

    public function data() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $metric = $_GET['metric'] ?? '';
        if (empty($metric)) {
            echo json_encode(['error' => 'Metric not specified']);
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);
        
        $chartData = $analyticsModel->getChartData($metric);
        echo json_encode($chartData);
        exit;
    }

    public function getLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);
        
        $layout = $analyticsModel->getLayoutForUser($_SESSION['user']['id']);
        echo json_encode($layout);
        exit;
    }

    public function saveLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $layout_data = file_get_contents('php://input');
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);

        $success = $analyticsModel->saveLayoutForUser($_SESSION['user']['id'], $layout_data);
        
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function resetLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);

        $success = $analyticsModel->deleteLayoutForUser($_SESSION['user']['id']);
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function generateReport() {
        $this->checkAuth();
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $analyticsModel = new Analytics($db);
        
        // This is a placeholder for your report generation logic
        $reportData = []; // You would populate this from the model

        view('analytics/report', $reportData);
    }
}