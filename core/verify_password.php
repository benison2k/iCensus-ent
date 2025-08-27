<?php
session_start();

// Disable output buffering & hide errors from breaking JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // hide output errors

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';

header('Content-Type: application/json');

function respond($status, $message, $extra = []) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    echo json_encode(array_merge(['status'=>$status, 'message'=>$message], $extra));
    exit;
}

// Validate session
if (!isset($_SESSION['user'])) respond('error', 'Not logged in');

// Validate input
if (empty($_POST['current_password'])) respond('error', 'No password provided');

try {
    $db = new Database($config);
    $auth = new Auth($db);
} catch (Exception $e) {
    respond('error', 'DB init failed: '.$e->getMessage());
}

$userId = $_SESSION['user']['id'];
$currentPassword = $_POST['current_password'];

try {
    $stmt = $db->getPdo()->prepare("SELECT password, username FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) respond('error', 'User not found');

    $hash = $user['password'];
    $verify = password_verify($currentPassword, $hash);

    // Log to server error log
    error_log("DEBUG verify_password.php: input='$currentPassword', stored_hash='$hash', verify_result=" . ($verify ? 'true' : 'false'));

    respond($verify ? 'success' : 'error',
        $verify ? 'Password correct' : 'Incorrect password',
        ['user_id'=>$userId,'username'=>$user['username']]
    );

} catch (Exception $e) {
    respond('error', 'Verification failed: '.$e->getMessage());
}
