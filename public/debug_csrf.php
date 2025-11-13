<?php
// public/debug_csrf.php
require_once __DIR__ . '/../core/init.php';

echo "<h1>CSRF Debugger</h1>";

// 1. Check Session Status
echo "<h3>1. Session Status</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is ACTIVE.<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is NOT active. (Check init.php)<br>";
}

// 2. Check Token Generation
echo "<h3>2. Token Storage</h3>";
if (isset($_SESSION['csrf_token'])) {
    echo "✅ Token exists in session: <code>" . htmlspecialchars($_SESSION['csrf_token']) . "</code><br>";
} else {
    echo "❌ No token in session! (Csrf::generate() failed or session was wiped)<br>";
}

// 3. Check CSRF Class
echo "<h3>3. Class Availability</h3>";
if (class_exists('Csrf')) {
    echo "✅ Csrf class is loaded.<br>";
    echo "Current generated field: " . htmlspecialchars(Csrf::getField()) . "<br>";
} else {
    echo "❌ Csrf class NOT found.<br>";
}
?>