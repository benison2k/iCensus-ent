<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';

if(!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$userId = $_SESSION['user']['id'];

try {
    if(isset($_POST['update_username'])) {
        $username = trim($_POST['username']);
        if($username === '') throw new Exception('Username cannot be empty');
        $auth->updateUsername($userId, $username);
        $_SESSION['modal'] = ['message'=>'Username updated successfully','type'=>'success'];
    }

    if(isset($_POST['update_password'])) {
        $current = $_POST['current_password'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if(!$auth->verifyPassword($userId, $current)) {
            throw new Exception('Current password is incorrect');
        }
        if($password !== $confirm) throw new Exception('Passwords do not match');

        $auth->updatePassword($userId, $password);
        $_SESSION['modal'] = ['message'=>'Password updated successfully','type'=>'success'];
    }

    if(isset($_POST['update_security'])) {
        $twoFA = isset($_POST['2fa']) ? 1 : 0;
        $auth->updateTwoFA($userId, $twoFA);
        $_SESSION['modal'] = ['message'=>'Security settings updated','type'=>'success'];
    }

} catch(Exception $e) {
    $_SESSION['modal'] = ['message'=>$e->getMessage(),'type'=>'error'];
}

header("Location: ../pages/settings.php");
exit;
