<?php
// app/controllers/AnalyticsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) { header('Location: /iCensus-ent/public/login'); exit; }
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
    }

    public function index() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'available_columns' => [
                'first_name' => 'First Name', 'last_name' => 'Last Name', 'dob' => 'Date of Birth',
                'gender' => 'Gender', 'civil_status' => 'Civil Status', 'purok' => 'Purok'
            ],
            'available_charts' => [
                'gender' => 'Gender Distribution', 'age' => 'Age Groups',
                'purok' => 'Population by Purok', 'civil_status' => 'Civil Status',
            ]
        ];
        view('analytics/index', $data);
    }

    public function data() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $metric = $_GET['metric'] ?? '';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        echo json_encode($analyticsModel->getChartData($metric));
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
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function resetLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $success = $analyticsModel->deleteLayoutForUser($_SESSION['user']['id']);
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function generateReport() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $reportData = $analyticsModel->getDataForReport($_POST);
        view('analytics/report', $reportData);
    }
}