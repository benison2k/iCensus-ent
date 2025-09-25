<?php
// sysadmin/auth_check.php
// This script will be included at the top of every page in the /sysadmin/ folder.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bouncer: Check if user is logged in and is a System Admin.
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] != 'System Admin') {
    // Redirect non-system admins to the main login page
    header("Location: ../pages/login.php");
    exit;
}

// Include core files needed for all admin pages
$config = require __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

$db = new Database($config);
$auth = new Auth($db);

// Refresh session data on each page load to keep it current
$auth->refreshUserSession($_SESSION['user']['id']); 

// Make user and theme variables available to all admin pages
$user = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';
?>