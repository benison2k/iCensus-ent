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

$userId = $_SESSION['user']['id'];

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Delete the layout setting for the user
    $stmt = $pdo->prepare("DELETE FROM user_analytics_layouts WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Also delete chart size settings
    $stmt = $pdo->prepare("DELETE FROM user_chart_settings WHERE user_id = ?");
    $stmt->execute([$userId]);

    echo json_encode(['status' => 'success', 'message' => 'Layout reset successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $e->getMessage()]);
}