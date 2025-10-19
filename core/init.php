<?php
// core/init.php
session_start();

// Define the base URL of your application.
define('BASE_URL', '/iCensus-ent/public');

// --- CRITICAL FIX START: Allow OTP verification pages to bypass redirect ---

// Get the current route
$current_route = str_replace(BASE_URL, '', strtok($_SERVER['REQUEST_URI'], '?'));
$current_route = trim($current_route, '/');

// Define pages that are allowed without a full user session
$allowed_pages = [
    'login',
    'home',
    '', // Root path
    'verify-otp',
    'resend-otp',
];

// Check for pages allowed without full login
$is_allowed_page = in_array($current_route, $allowed_pages);

// Check if we are in the middle of a 2FA flow
$is_in_2fa_flow = isset($_SESSION['2fa_required']) && $_SESSION['2fa_required'] === true;

// If the user session doesn't exist, redirect to login immediately, 
// UNLESS the user is trying to access an allowed page OR is in the 2FA flow.
if (!isset($_SESSION['user']) && !$is_allowed_page && !$is_in_2fa_flow) {
    header("Location: " . BASE_URL . "/login");
    exit;
}

// --- CRITICAL FIX END ---

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

// This function is for legacy use; direct checks in controllers are preferred.
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        // Use the defined BASE_URL for consistency
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}