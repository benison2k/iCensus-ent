<?php
session_start();
header('Content-Type: application/json');
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();
$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("DELETE FROM user_analytics_layouts WHERE user_id = ?");
$stmt->execute([$userId]);

echo json_encode(['status' => 'success']);