<?php
// core/init.php
session_start();

// Redirect to login if user is not authenticated
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../pages/login.php");
        exit;
    }
}

// Session timeout (30 minutes)
$timeout = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../pages/login.php?timeout=1");
    exit;
}

// Update last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();
