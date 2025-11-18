<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Encoder Dashboard</title>
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/style.css">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Welcome, <?= htmlspecialchars($user['full_name']); ?>!</h2>
    <p style="opacity: 0.7; margin-top: 0.5rem;">Here is your activity overview for today.</p>
</div>

<main class="dashboard encoder-dashboard">
    
    <div class="stats-grid">
        
        <div class="card stat-card">
            <div class="stat-icon-wrapper bg-blue">
                <span class="material-icons">today</span>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= $stats['today'] ?? 0 ?></h3>
                <p class="stat-label">Entries Today</p>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-icon-wrapper bg-orange">
                <span class="material-icons">hourglass_top</span>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= $stats['pending'] ?? 0 ?></h3>
                <p class="stat-label">Pending Approval</p>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-icon-wrapper bg-green">
                <span class="material-icons">check_circle</span>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= $stats['approved'] ?? 0 ?></h3>
                <p class="stat-label">Total Approved</p>
            </div>
        </div>

    </div>
    
    <div class="actions-grid">
        <a href="/iCensus-ent/public/residents" class="card action-card clickable-card">
            <div class="action-icon-box">
                <span class="material-icons">groups</span>
            </div>
            <div class="action-details">
                <h3 class="action-title">Manage Residents</h3>
                <p class="action-desc">Add new records, search database, and update resident information.</p>
            </div>
            <div class="action-arrow">
                <span class="material-icons">arrow_forward</span>
            </div>
        </a>
    </div>

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>