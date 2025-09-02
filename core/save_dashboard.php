<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$userId = $_SESSION['user']['id'];
$db = new Database($config);
$pdo = $db->getPdo();

// Read the raw POST data since it's JSON
$json = file_get_contents('php://input');
$data = json_decode($json);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON received.']);
    exit;
}

$dashboardConfig = json_encode($data->dashboard_config);

try {
    // Check if a dashboard config already exists for the user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_dashboards WHERE user_id = ?");
    $stmt->execute([$userId]);
    $exists = (bool) $stmt->fetchColumn();

    if ($exists) {
        // Update existing dashboard
        $stmt = $pdo->prepare("UPDATE user_dashboards SET dashboard_config = ? WHERE user_id = ?");
        $stmt->execute([$dashboardConfig, $userId]);
    } else {
        // Insert new dashboard config
        $stmt = $pdo->prepare("INSERT INTO user_dashboards (user_id, dashboard_config) VALUES (?, ?)");
        $stmt->execute([$userId, $dashboardConfig]);
    }

    echo json_encode(['success' => true, 'message' => 'Dashboard saved successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
