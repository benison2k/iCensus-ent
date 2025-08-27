<?php
session_start();
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- New CSS -->

    <link rel="stylesheet" href="../assets/css/dashboard.css"> <!-- New CSS -->
    <!-- Material Icons CDN -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome">
        <h2>Welcome, <?= htmlspecialchars($user['full_name']); ?>!</h2>
    </div>

    <main class="dashboard">
        <div class="card-grid">
            <div class="card clickable-card">
                <span class="material-icons card-icon">person</span>
                <h3 class="card-title">Users</h3>
                <p class="card-desc">Manage and view registered users</p>
            </div>
            <div class="card clickable-card">
                <span class="material-icons card-icon">analytics</span>
                <h3 class="card-title">Analytics</h3>
                <p class="card-desc">View reports and insights</p>
            </div>
            <a href="../pages/settings.php" class="card clickable-card">
                <span class="material-icons card-icon">settings</span>
                <h3 class="card-title">Settings</h3>
                <p class="card-desc">Adjust system preferences</p>
            </a>
            <!-- About card now links to about.php -->
            <a href="../pages/about.php" class="card clickable-card">
                <span class="material-icons card-icon">info</span>
                <h3 class="card-title">About</h3>
                <p class="card-desc">Learn more about the system</p>
            </a>
        </div>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
