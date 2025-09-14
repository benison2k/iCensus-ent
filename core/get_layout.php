<?php
session_start();
header('Content-Type: application/json');
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode([]); // Return empty layout if not logged in
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();

$stmt = $pdo->prepare("SELECT layout FROM user_analytics_layouts WHERE user_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$result = $stmt->fetchColumn();

// If the user has no saved layout, provide a default one.
if (!$result || empty(json_decode($result))) {
    $defaultLayout = json_encode([
        // Added 'keepAspectRatio' => true to each item
        ['id' => 'gender', 'x' => 0, 'y' => 0, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
        ['id' => 'age', 'x' => 4, 'y' => 0, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
        ['id' => 'status', 'x' => 8, 'y' => 0, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
        ['id' => 'purok', 'x' => 0, 'y' => 2, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
        ['id' => 'civil_status', 'x' => 4, 'y' => 2, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
        ['id' => 'blood_type', 'x' => 8, 'y' => 2, 'w' => 4, 'h' => 2, 'keepAspectRatio' => true],
    ]);
    echo $defaultLayout;
} else {
    echo $result;
}