<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db   = new Database($config);
$auth = new Auth($db);
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
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/analytics.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Analytics Dashboard</h2></div>

<main class="analytics-dashboard">
    <div class="analytics-controls">
        <button id="saveLayoutBtn" class="control-btn save-btn">
            <span class="material-icons">save</span> Save Layout
        </button>
        <button id="resetLayoutBtn" class="control-btn reset-btn">
            <span class="material-icons">refresh</span> Reset Layout
        </button>
    </div>
    <div class="analytics-grid" id="analyticsGrid">
        <div class="stat-card" data-id="stat-total-residents">
            <div class="stat-icon"><span class="material-icons">people</span></div>
            <div class="stat-info">
                <p>Total Residents</p>
                <h3 id="totalResidents">...</h3>
            </div>
        </div>
        <div class="stat-card" data-id="stat-total-households">
            <div class="stat-icon"><span class="material-icons">house</span></div>
            <div class="stat-info">
                <p>Total Households</p>
                <h3 id="totalHouseholds">...</h3>
            </div>
        </div>
        <div class="stat-card" data-id="stat-male-count">
            <div class="stat-icon"><span class="material-icons">male</span></div>
            <div class="stat-info">
                <p>Male</p>
                <h3 id="maleCount">...</h3>
            </div>
        </div>
        <div class="stat-card" data-id="stat-female-count">
            <div class="stat-icon"><span class="material-icons">female</span></div>
            <div class="stat-info">
                <p>Female</p>
                <h3 id="femaleCount">...</h3>
            </div>
        </div>
        <div class="stat-card" data-id="stat-senior-count">
            <div class="stat-icon"><span class="material-icons">elderly</span></div>
            <div class="stat-info">
                <p>Seniors (60+)</p>
                <h3 id="seniorCount">...</h3>
            </div>
        </div>

        <?php 
        $charts = [
            'chart-household-size' => 'Household Size Distribution',
            'chart-gender' => 'Gender Distribution',
            'chart-age' => 'Age Distribution',
            'chart-purok' => 'Population by Purok',
            'chart-barangay' => 'Population by Barangay',
            'chart-status' => 'Resident Status',
            'chart-civil-status' => 'Civil Status',
            'chart-blood-type' => 'Blood Type Distribution',
            'chart-nationality' => 'Nationality Distribution'
        ];
        
        foreach ($charts as $id => $title): ?>
        <div class="chart-card" data-id="<?= $id ?>">
            <div class="chart-header">
                <h3><?= $title ?></h3>
                <div class="chart-settings">
                    <span class="material-icons">settings</span>
                    <div class="size-selector">
                        <div class="size-option" data-size="1">1x</div>
                        <div class="size-option" data-size="2">2x</div>
                        <div class="size-option" data-size="3">3x</div>
                    </div>
                </div>
            </div>
            <canvas id="<?= str_replace('chart-', '', $id) ?>Chart"></canvas>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/analytics.js"></script>

</body>
</html>