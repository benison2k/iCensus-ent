<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$auth->refreshUserSession($_SESSION['user']['id']);

$user = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/analytics_layout_fix.css"> 
    <link rel="stylesheet" href="../assets/css/analytics.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack-all.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Analytics Dashboard</h2>
</div>

<main class="dashboard">
    <div class="dashboard-card">
        <div class="controls-wrapper">
            <div class="tooltip-container">
                <span class="material-icons info-icon">info</span>
                <div class="tooltip-text">You can drag and drop the charts to rearrange the layout.</div>
            </div>
            <div class="buttons-container">
                <button id="reset-layout-btn" style="padding: 0.5rem 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; border: none; border-radius: 8px; background-color: #f44336; color: white; margin-right: 0.5rem;">
                    <span class="material-icons">refresh</span> Reset Layout
                </button>
                <button id="save-layout-btn" style="padding: 0.5rem 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; border: none; border-radius: 8px;">
                    <span class="material-icons">save</span> Save Layout
                </button>
            </div>
        </div>
        
        <hr class="separator-line">

        <div class="grid-stack"></div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/analytics.js"></script>
</body>
</html>