<?php
session_start();
header('Content-Type: application/json');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

// Security check: ensure user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$metric = $_POST['metric'] ?? null;
$chartType = $_POST['chartType'] ?? null;
$validChartTypes = ['bar', 'pie', 'doughnut', 'line'];

// Validate the received data
if (!$metric || !$chartType || !in_array($chartType, $validChartTypes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Prepare a statement to insert or update the chart setting.
    // This handles both new and existing metrics gracefully.
    $stmt = $pdo->prepare(
        "INSERT INTO chart_settings (metric, chart_type) VALUES (:metric, :chart_type)
         ON DUPLICATE KEY UPDATE chart_type = :chart_type_update"
    );
    
    // Bind parameters and execute
    $stmt->execute([
        ':metric' => $metric,
        ':chart_type' => $chartType,
        ':chart_type_update' => $chartType
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Chart type updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $e->getMessage()]);
}