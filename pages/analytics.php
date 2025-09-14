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
<link rel="stylesheet" href="../assets/css/analytics.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Analytics Dashboard</h2>
</div>

<main class="analytics-container">
    <div class="chart-grid">
        <div class="chart-container">
            <div class="chart-title">Gender Distribution</div>
            <div class="chart-div" id="gender_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Age Distribution</div>
            <div class="chart-div" id="age_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Resident Status</div>
            <div class="chart-div" id="status_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Population by Purok</div>
            <div class="chart-div" id="purok_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Population by Barangay</div>
            <div class="chart-div" id="barangay_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Civil Status</div>
            <div class="chart-div" id="civil_status_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Blood Type</div>
            <div class="chart-div" id="blood_type_chart_div"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Residency Status</div>
            <div class="chart-div" id="residency_status_chart_div"></div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/analytics.js"></script>
</body>
</html>