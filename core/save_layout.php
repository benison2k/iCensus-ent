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

$layout = $_POST['layout'] ?? null;
$userId = $_SESSION['user']['id'];

// Validate the received data
if (!$layout || !json_decode($layout)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid layout data']);
    exit;
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Prepare a statement to insert or update the layout setting.
    $stmt = $pdo->prepare(
        "INSERT INTO user_analytics_layouts (user_id, layout) VALUES (:user_id, :layout)
         ON DUPLICATE KEY UPDATE layout = :layout_update"
    );
    
    // Bind parameters and execute
    $stmt->execute([
        ':user_id' => $userId,
        ':layout' => $layout,
        ':layout_update' => $layout
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Layout saved successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $e->getMessage()]);
}