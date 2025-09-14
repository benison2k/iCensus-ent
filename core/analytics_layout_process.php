<?php
session_start();
header('Content-Type: application/json');

// Suppress errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$db = new Database($config);
$pdo = $db->getPdo();

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load') {
    $stmt = $pdo->prepare("SELECT layout FROM user_analytics_layouts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['layout'])) {
        // We have a layout, send it back
        echo $result['layout'];
    } else {
        // No layout saved, send empty array
        echo json_encode([]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $layoutData = file_get_contents('php://input');
    
    // Validate that we received valid JSON
    if (json_decode($layoutData) === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid layout data received.']);
        exit;
    }
    
    // Use INSERT ... ON DUPLICATE KEY UPDATE
    $stmt = $pdo->prepare("
        INSERT INTO user_analytics_layouts (user_id, layout) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE layout = ?
    ");
    
    try {
        $stmt->execute([$userId, $layoutData, $layoutData]);
        echo json_encode(['success' => true, 'message' => 'Layout saved.']);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Layout Save Error: " . $e->getMessage()); // Log error for debugging
        echo json_encode(['success' => false, 'message' => 'Failed to save layout.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}