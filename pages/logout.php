<?php
session_start();

// Include necessary files for logging
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/functions.php';

// Create a database connection to use for logging
$config = require __DIR__ . '/../core/config.php';
$db = new Database($config);

// NEW: Log the logout event before destroying the session
if (isset($_SESSION['user'])) {
    log_action('INFO', 'USER_LOGOUT', "User '" . $_SESSION['user']['username'] . "' logged out.");
}

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;