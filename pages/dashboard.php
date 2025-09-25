<?php
session_start();

// --- Bouncer ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] != 'Barangay Admin') { // <-- UPDATED from 'Clerk'
    http_response_code(403);
    die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>");
}
// --- End Bouncer ---

// Include config + core
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

// Auth check
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db   = new Database($config);
$auth = new Auth($db);

// Refresh session to get latest user data (theme, etc.)
$auth->refreshUserSession($_SESSION['user']['id']);

$user  = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Welcome, <?= htmlspecialchars($user['full_name']); ?>!</h2>
</div>

<main class="dashboard">
    <div class="card-grid">
        <a href="../pages/residents.php" class="card clickable-card">
            <span class="material-icons card-icon">groups</span>
            <h3 class="card-title">Residents</h3>
            <p class="card-desc">Manage and view registered residents</p>
        </a>

        <a href="../pages/analytics.php" class="card clickable-card">
            <span class="material-icons card-icon">analytics</span>
            <h3 class="card-title">Analytics</h3>
            <p class="card-desc">View reports and insights</p>
        </a>

        <a href="../pages/settings.php" class="card clickable-card">
            <span class="material-icons card-icon">settings</span>
            <h3 class="card-title">Settings</h3>
            <p class="card-desc">Adjust system preferences</p>
        </a>

        <a href="../pages/about.php" class="card clickable-card">
            <span class="material-icons card-icon">info</span>
            <h3 class="card-title">About</h3>
            <p class="card-desc">Learn more about the system</p>
        </a>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Prevent scrolling until animations finish
    document.body.style.overflow = "hidden";
    setTimeout(() => {
        document.body.style.overflow = "";
    }, 1000); // match animation duration
});
</script>

</body>
</html>