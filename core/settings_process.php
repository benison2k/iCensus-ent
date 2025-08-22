<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$userId = $_SESSION['user']['id'];

try {
    // ---------------------------
    // Update Username
    // ---------------------------
    if(isset($_POST['update_username'])) {
        $username = trim($_POST['username']);
        if($username === '') throw new Exception('Username cannot be empty');

        $auth->updateUsername($userId, $username);  // <-- use Auth method
        $_SESSION['modal'] = ['message'=>'Username updated successfully','type'=>'success'];
    }

    // ---------------------------
    // Update Password
    // ---------------------------
    if(isset($_POST['update_password'])) {
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        if($password !== $confirm) throw new Exception('Passwords do not match');

        $auth->updatePassword($userId, $password);  // <-- use Auth method
        $_SESSION['modal'] = ['message'=>'Password updated successfully','type'=>'success'];
    }

    // ---------------------------
    // Update Security (2FA)
    // ---------------------------
    if(isset($_POST['update_security'])) {
        $twoFA = isset($_POST['2fa']) ? 1 : 0;
        $auth->updateTwoFA($userId, $twoFA); // <-- use Auth method
        $_SESSION['modal'] = ['message'=>'Security settings updated','type'=>'success'];
    }

} catch(Exception $e) {
    $_SESSION['modal'] = ['message'=>$e->getMessage(),'type'=>'error'];
}

// Redirect back to settings page
header("Location: ../pages/settings.php");
exit;
