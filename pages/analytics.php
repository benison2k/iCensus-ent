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
        <div class="chart-container" id="gender_chart_div"></div>
        <div class="chart-container" id="age_chart_div"></div>
        <div class="chart-container" id="status_chart_div"></div>
        <div class="chart-container" id="purok_chart_div"></div>
        <div class="chart-container" id="barangay_chart_div"></div>
        <div class="chart-container" id="civil_status_chart_div"></div>
        <div class="chart-container" id="blood_type_chart_div"></div>
        <div class="chart-container" id="residency_status_chart_div"></div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/analytics.js"></script>
</body>
</html>