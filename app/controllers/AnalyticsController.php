<?php
// app/controllers/AnalyticsController.php
require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display the main analytics page.
     */
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
        ];
        
        view('analytics/index', $data);
    }

    /**
     * Provide chart data based on the requested metric.
     * Replaces core/analytics_data.php
     */
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

    /**
     * Get the user's saved layout.
     * Replaces core/get_layout.php
     */
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

    /**
     * Save the user's layout.
     * Replaces core/save_layout.php
     */
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
}