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

    /**
     * Saves a new chart definition from the builder to the database.
     */
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
            'filter_conditions' => null
        ];

        if (!empty($_POST['filters'])) {
            $data['filter_conditions'] = json_encode(array_values($_POST['filters']));
        }

        $chartModel = new Chart($GLOBALS['db']);
        $chartId = $chartModel->save($data);

        if ($chartId) {
            log_action('INFO', 'CHART_SAVED', "User saved chart definition ID#{$chartId}.");
            echo json_encode(['status' => 'success', 'message' => 'Chart saved successfully!', 'chart_id' => $chartId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save chart to the database.']);
        }
        
        exit;
    }

    /**
     * Generates a chart preview without saving to the database.
     */
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

    /**
     * Handles API requests to get data for a specific chart.
     */
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

    /**
     * Fetches all chart definitions for the logged-in user.
     */
    public function getUserCharts() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $chartModel = new Chart($GLOBALS['db']);
        $charts = $chartModel->findAllByUserId($_SESSION['user']['id']);

        echo json_encode(['status' => 'success', 'charts' => $charts]);
        exit;
    }
}