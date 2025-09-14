<?php
session_start();
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

// Refresh session to get latest user data
$auth->refreshUserSession($_SESSION['user']['id']);

$user  = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Analytics</title>

<link href="https://cdn.jsdelivr.net/npm/gridstack@7.2.3/dist/gridstack.min.css" rel="stylesheet"/>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/analytics.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Population Analytics</h2>
    <p>Drag, drop, and resize cards to customize your view. <button id="save-layout-btn" class="button">Save Layout</button></p>
</div>

<main class="analytics-dashboard">
    <div class="grid-stack">
        
        <div class="grid-stack-item" gs-id="gender">
            <div class="grid-stack-item-content chart-card">
                <div id="gender_chart_div" class="chart-container"></div>
            </div>
        </div>
        
        <div class="grid-stack-item" gs-id="age_group">
            <div class="grid-stack-item-content chart-card">
                <div id="age_group_chart_div" class="chart-container"></div>
            </div>
        </div>

        <div class="grid-stack-item" gs-id="civil_status">
            <div class="grid-stack-item-content chart-card">
                <div id="civil_status_chart_div" class="chart-container"></div>
            </div>
        </div>

        <div class="grid-stack-item" gs-id="purok">
            <div class="grid-stack-item-content chart-card">
                <div id="purok_chart_div" class="chart-container"></div>
            </div>
        </div>

        <div class="grid-stack-item" gs-id="blood_type">
            <div class="grid-stack-item-content chart-card">
                <div id="blood_type_chart_div" class="chart-container"></div>
            </div>
        </div>

        <div class="grid-stack-item" gs-id="status">
            <div class="grid-stack-item-content chart-card">
                <div id="status_chart_div" class="chart-container"></div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/gridstack@7.2.3/dist/gridstack-all.js"></script>

<script src="../assets/js/analytics.js"></script>
<script src="../assets/js/analytics_grid.js"></script>

</body>
</html>