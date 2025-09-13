<?php
session_start();
header('Content-Type: application/json');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$chartId = $_POST['chart_id'] ?? null;
$size = $_POST['size'] ?? null;
$userId = $_SESSION['user']['id'];

if (!$chartId || !$size || !in_array($size, [1, 2, 3])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided']);
    exit;
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    $stmt = $pdo->prepare(
        "INSERT INTO user_chart_settings (user_id, chart_id, size) VALUES (:user_id, :chart_id, :size)
         ON DUPLICATE KEY UPDATE size = :size_update"
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':chart_id' => $chartId,
        ':size' => $size,
        ':size_update' => $size
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Chart size saved']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}