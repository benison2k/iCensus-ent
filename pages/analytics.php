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
    <div style="text-align: right; margin-bottom: 1rem;">
        <button id="saveLayoutBtn" style="padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; background: #4caf50; color: #fff;">
            <span class="material-icons" style="vertical-align: middle;">save</span> Save Layout
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

        <div class="chart-card" data-id="chart-household-size">
            <h3>Household Size Distribution</h3>
            <canvas id="householdSizeChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-gender">
            <h3>Gender Distribution</h3>
            <canvas id="genderChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-age">
            <h3>Age Distribution</h3>
            <canvas id="ageChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-purok">
            <h3>Population by Purok</h3>
            <canvas id="purokChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-barangay">
            <h3>Population by Barangay</h3>
            <canvas id="barangayChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-status">
            <h3>Resident Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-civil-status">
            <h3>Civil Status</h3>
            <canvas id="civilStatusChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-blood-type">
            <h3>Blood Type Distribution</h3>
            <canvas id="bloodTypeChart"></canvas>
        </div>
        <div class="chart-card" data-id="chart-nationality">
            <h3>Nationality Distribution</h3>
            <canvas id="nationalityChart"></canvas>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/analytics.js"></script>

</body>
</html>