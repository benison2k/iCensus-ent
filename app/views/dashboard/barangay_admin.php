<?php
// Define the single, correct base URL for all links on this page.
$base_url = '/iCensus-ent/public'; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Dashboard</title>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
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
        <a href="<?= $base_url ?>/residents" class="card clickable-card">
            
            <?php // This block adds the notification badge if there are pending residents ?>
            <?php if (isset($pending_count) && $pending_count > 0): ?>
                <span class="notification-badge"><?= $pending_count ?></span>
            <?php endif; ?>

            <span class="material-icons card-icon">groups</span>
            <h3 class="card-title">Residents</h3>
            <p class="card-desc">Manage and view registered residents</p>
        </a>

        <a href="<?= $base_url ?>/analytics" class="card clickable-card">
            <span class="material-icons card-icon">analytics</span>
            <h3 class="card-title">Analytics</h3>
            <p class="card-desc">View reports and insights</p>
        </a>

        <a href="<?= $base_url ?>/settings" class="card clickable-card">
            <span class="material-icons card-icon">settings</span>
            <h3 class="card-title">Settings</h3>
            <p class="card-desc">Adjust system preferences</p>
        </a>

        <a href="<?= $base_url ?>/about" class="card clickable-card">
            <span class="material-icons card-icon">info</span>
            <h3 class="card-title">About</h3>
            <p class="card-desc">Learn more about the system</p>
        </a>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>