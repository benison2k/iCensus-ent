<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';
// Include the new functions file
if (file_exists(__DIR__ . '/../core/functions.php')) {
    require_once __DIR__ . '/../core/functions.php';
}

if(!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$userId = $_SESSION['user']['id'];
$username_session = $_SESSION['user']['username'];

try {
    if(isset($_POST['update_username'])) {
        $username = trim($_POST['username']);
        if($username === '') throw new Exception('Username cannot be empty');
        
        log_action('INFO', 'SETTINGS_UPDATE', "User '{$username_session}' changed their username to '{$username}'.");
        
        $auth->updateUsername($userId, $username);
        $_SESSION['modal'] = ['message'=>'Username updated successfully','type'=>'success'];
    }

    if(isset($_POST['update_password'])) {
        // ... (password validation logic) ...
        
        log_action('INFO', 'SETTINGS_UPDATE', "User '{$username_session}' changed their password.");

        $auth->updatePassword($userId, $password);
        $_SESSION['modal'] = ['message'=>'Password updated successfully','type'=>'success'];
    }

    if(isset($_POST['update_security'])) {
        $twoFA = isset($_POST['2fa']) ? 1 : 0;
        $status = $twoFA ? 'enabled' : 'disabled';

        log_action('INFO', 'SETTINGS_UPDATE', "User '{$username_session}' {$status} 2FA.");

        $auth->updateTwoFA($userId, $twoFA);
        $_SESSION['modal'] = ['message'=>'Security settings updated','type'=>'success'];
    }

} catch(Exception $e) {
    log_action('ERROR', 'SYSTEM_ERROR', 'Error in settings_process.php: ' . $e->getMessage());
    $_SESSION['modal'] = ['message'=>$e->getMessage(),'type'=>'error'];
}

header("Location: ../pages/settings.php");
exit;