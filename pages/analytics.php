<?php
session_start();
require __DIR__ . '/../core/init.php';
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
<link rel="stylesheet" href="../assets/css/analytics.css">
<link rel="stylesheet" href="../assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Analytics Dashboard</h2></div>

<main class="dashboard analytics-dashboard">
    <div class="card-grid" id="chart-grid">
        </div>
    
    <div class="add-chart-container">
        <button id="add-chart-btn" class="card clickable-card">
            <span class="material-icons card-icon">add_chart</span>
            <h3 class="card-title">Add New Chart</h3>
            <p class="card-desc">Create a new customizable chart</p>
        </button>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<?php include __DIR__ . '/../components/chart_modal.php'; ?>

<script src="../assets/js/analytics.js"></script>
</body>
</html>