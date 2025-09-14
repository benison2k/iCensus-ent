<?php
session_start();
header('Content-Type: application/json');
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$layout_data = file_get_contents('php://input');
if (json_decode($layout_data) === null) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();
$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("INSERT INTO user_analytics_layouts (user_id, layout) VALUES (?, ?) ON DUPLICATE KEY UPDATE layout = ?");
$stmt->execute([$userId, $layout_data, $layout_data]);

echo json_encode(['status' => 'success']);