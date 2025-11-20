<?php
// Define the base URL for assets and links
$base_url = '/iCensus-ent/public'; 

// NOTE: The greeting_name is now passed from the controller, cleaning up the view logic.
$greetingName = $greeting_name ?? 'Encoder'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Encoder Dashboard</title>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<main class="dashboard encoder-dashboard">
    
    <div class="dashboard-hero">
        <div class="hero-content">
            <h1>Hello, <?= htmlspecialchars($greetingName); ?>!</h1>
            <p>Have a productive day recording census data.</p>
        </div>
        <div class="hero-date">
            <span class="material-icons">calendar_today</span>
            <span><?= date('l, F j, Y') ?></span>
        </div>
    </div>

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

        <a href="<?= $base_url ?>/residents?filter_status=pending&filter_encoder=<?= $user['id'] ?>" class="card stat-card clickable-card">
            <div class="stat-icon-wrapper bg-orange">
                <span class="material-icons">hourglass_top</span>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= $stats['pending'] ?? 0 ?></h3>
                <p class="stat-label">Pending Approval</p>
            </div>
        </a>

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
    
    <div class="content-split">
        
        <div class="card recent-activity-card">
            <div class="card-header-flex">
                <h3>Recent Submissions</h3>
                <span class="material-icons" style="opacity:0.5;">history</span>
            </div>
            
            <div class="activity-list">
                <?php if (empty($recent_activity)): ?>
                    <div class="empty-state">
                        <p>No recent activity recorded.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $activity['approval_status'] == 'approved' ? 'success' : 'pending' ?>">
                                <span class="material-icons">person</span>
                            </div>
                            <div class="activity-info">
                                <span class="activity-name">
                                    <?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?>
                                </span>
                                <span class="activity-time">
                                    <?= time_elapsed_string($activity['created_at']) ?>
                                </span>
                            </div>
                            <div class="activity-status">
                                <?php if($activity['approval_status'] == 'approved'): ?>
                                    <span class="status-badge success">Approved</span>
                                <?php else: ?>
                                    <span class="status-badge pending">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions-column">
            <a href="<?= $base_url ?>/residents" class="card action-card clickable-card">
                <div class="action-icon-box">
                    <span class="material-icons">person_add</span>
                </div>
                <div class="action-details">
                    <h3 class="action-title">Manage Residents</h3>
                    <p class="action-desc">Add, search, and update records.</p>
                </div>
                <div class="action-arrow">
                    <span class="material-icons">arrow_forward</span>
                </div>
            </a>
            
            <div class="card info-card">
                <span class="material-icons info-icon">lightbulb</span>
                <p><strong>Tip:</strong> Ensure all required fields are filled before submitting to speed up approval.</p>
            </div>
        </div>

    </div>

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>