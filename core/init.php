<?php
// core/init.php
session_start();

// Define the base URL of your application.
// This ensures redirects always go to the correct place.
define('BASE_URL', '/iCensus-ent/public');

// This function is for legacy use; direct checks in controllers are preferred.
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        // Use the defined BASE_URL for consistency
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}

// Session timeout (1800 = 30 minutes)
$timeout = 1800; 

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Start a new, clean session for the login page
    session_start();

    // Store a friendly message to show the user
    $_SESSION['timeout_message'] = "You have been logged out due to inactivity.";
    
    // Use the defined BASE_URL for a clean and consistent redirect
    header("Location: " . BASE_URL . "/login");
    exit;
}

// Update last activity timestamp on each page load
$_SESSION['LAST_ACTIVITY'] = time();