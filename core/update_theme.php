<?php
session_start();
$config = require __DIR__ . '/config.php';  // <-- assign returned array
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$theme = $_POST['theme'] ?? 'light';
$theme = $theme==='dark' ? 'dark' : 'light';
$userId = $_SESSION['user']['id'];

try {
    $db = new Database($config);
    $conn = $db->getPdo();

    $stmt = $conn->prepare("UPDATE users SET theme=? WHERE id=?");
    $stmt->execute([$theme, $userId]);

    // Refresh session data
    $auth = new Auth($db);
    $auth->refreshUserSession($userId);

    echo json_encode(['status'=>'success','theme'=>$theme]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
