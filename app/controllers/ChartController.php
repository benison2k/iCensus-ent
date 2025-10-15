<?php
// app/controllers/ChartController.php

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../models/Chart.php'; 

class ChartController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        // This line is important for the log_action function to work
        $GLOBALS['db'] = new Database(require __DIR__ . '/../../config/database.php');
    }

    public function save() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if (empty($_POST['title']) || empty($_POST['chart_type']) || empty($_POST['aggregate_function'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required chart data.']);
            exit;
        }

        $data = [
            'user_id' => $_SESSION['user']['id'],
            'title' => trim($_POST['title']),
            'chart_type' => $_POST['chart_type'],
            'aggregate_function' => $_POST['aggregate_function'],
            'aggregate_column' => ($_POST['aggregate_function'] === 'AVG') ? 'dob' : '*',
            'group_by_column' => !empty($_POST['group_by_column']) ? $_POST['group_by_column'] : null,
            'filter_conditions' => !empty($_POST['filters']) ? json_encode(array_values($_POST['filters'])) : null
        ];

        $chartModel = new Chart($GLOBALS['db']);
        $chartId = $chartModel->save($data);

        if ($chartId) {
            log_action('INFO', 'CHART_SAVED', "User saved chart definition ID#{$chartId}.");
            $newChartDef = $chartModel->find($chartId);
            echo json_encode(['status' => 'success', 'message' => 'Chart saved successfully!', 'chart' => $newChartDef]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save chart to the database.']);
        }
        
        exit;
    }

    public function update() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartId = $_POST['chart_id'] ?? null;
        if (!$chartId) {
            echo json_encode(['status' => 'error', 'message' => 'Chart ID is missing.']);
            exit;
        }

        $data = [
            'title' => trim($_POST['title']),
            'chart_type' => $_POST['chart_type'],
            'aggregate_function' => $_POST['aggregate_function'],
            'aggregate_column' => ($_POST['aggregate_function'] === 'AVG') ? 'dob' : '*',
            'group_by_column' => !empty($_POST['group_by_column']) ? $_POST['group_by_column'] : null,
            'filter_conditions' => !empty($_POST['filters']) ? json_encode(array_values($_POST['filters'])) : null
        ];

        $chartModel = new Chart($GLOBALS['db']);
        if ($chartModel->update($chartId, $data)) {
            log_action('INFO', 'CHART_UPDATED', "User updated chart definition ID#{$chartId}.");
            $updatedChart = $chartModel->find($chartId);
            echo json_encode(['status' => 'success', 'message' => 'Chart updated successfully!', 'chart' => $updatedChart]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update chart.']);
        }
        exit;
    }

    public function delete() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartId = $_POST['chart_id'] ?? null;
        if (!$chartId) {
            echo json_encode(['status' => 'error', 'message' => 'Chart ID is missing.']);
            exit;
        }

        $chartModel = new Chart($GLOBALS['db']);
        if ($chartModel->delete($chartId)) {
            log_action('INFO', 'CHART_DELETED', "User deleted chart definition ID#{$chartId}.");
            echo json_encode(['status' => 'success', 'message' => 'Chart deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete chart.']);
        }
        exit;
    }
    
    public function get() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartId = $_GET['id'] ?? null;
        if (!$chartId) {
            echo json_encode(['status' => 'error', 'message' => 'No chart ID provided.']);
            exit;
        }

        $chartModel = new Chart($GLOBALS['db']);
        $chart = $chartModel->find($chartId);

        if ($chart) {
            echo json_encode(['status' => 'success', 'chart' => $chart]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Chart not found.']);
        }
        exit;
    }

    public function preview() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartDef = [
            'chart_type' => $_POST['chart_type'] ?? 'PieChart',
            'aggregate_function' => $_POST['aggregate_function'] ?? 'COUNT',
            'aggregate_column' => ($_POST['aggregate_function'] === 'AVG') ? 'dob' : '*',
            'group_by_column' => !empty($_POST['group_by_column']) ? $_POST['group_by_column'] : null,
            'filter_conditions' => !empty($_POST['filters']) ? json_encode(array_values($_POST['filters'])) : null
        ];

        try {
            $chartModel = new Chart($GLOBALS['db']);
            $chartData = $chartModel->getDataForChart($chartDef);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Could not generate preview: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getData() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartId = $_GET['chart_id'] ?? null;
        if (!$chartId) {
            echo json_encode(['error' => 'No Chart ID provided.']);
            exit;
        }

        try {
            $chartModel = new Chart($GLOBALS['db']);
            $chartDef = $chartModel->find($chartId);

            if (!$chartDef) {
                echo json_encode(['error' => 'Chart not found.']);
                exit;
            }
            
            if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                $chartDef['start_date'] = $_GET['start_date'];
                $chartDef['end_date'] = $_GET['end_date'];
            }

            $chartData = $chartModel->getDataForChart($chartDef);

            $response = [
                'status' => 'success',
                'title' => $chartDef['title'],
                'type' => $chartDef['chart_type'],
                'data' => $chartData
            ];
            echo json_encode($response);

        } catch (Exception $e) {
            log_action('ERROR', 'CHART_DATA_FAIL', "Failed to get data for chart ID#{$chartId}: " . $e->getMessage());
            echo json_encode(['error' => 'An internal error occurred while fetching chart data.']);
        }
        exit;
    }

    public function getUserCharts() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $chartModel = new Chart($GLOBALS['db']);
        $charts = $chartModel->findAllByUserId($_SESSION['user']['id']);

        echo json_encode(['status' => 'success', 'charts' => $charts]);
        exit;
    }
}