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
</div>

<main class="dashboard" style="flex-direction: column; gap: 2rem; padding-top: 1rem;">
    
    <div class="card" style="max-width: 900px; width: 90%; padding: 2rem;">
        <h3 class="card-title" style="text-align: left; margin-bottom: 2rem; font-size: 1.3rem;">Your Activity Summary</h3>
        <div style="display: flex; justify-content: space-around; text-align: center; gap: 1rem;">
            
            <div>
                <span class="material-icons card-icon" style="font-size: 3rem;">today</span>
                <p style="font-size: 2.5rem; font-weight: 600; margin: 0.5rem 0; line-height: 1;"><?= $stats['today'] ?? 0 ?></p>
                <p class="card-desc">Entries Today</p>
            </div>

            <div>
                <span class="material-icons card-icon" style="font-size: 3rem;">hourglass_top</span>
                <p style="font-size: 2.5rem; font-weight: 600; margin: 0.5rem 0; line-height: 1;"><?= $stats['pending'] ?? 0 ?></p>
                <p class="card-desc">Pending Approval</p>
            </div>

            <div>
                <span class="material-icons card-icon" style="font-size: 3rem;">check_circle</span>
                <p style="font-size: 2.5rem; font-weight: 600; margin: 0.5rem 0; line-height: 1;"><?= $stats['approved'] ?? 0 ?></p>
                <p class="card-desc">Total Approved</p>
            </div>

        </div>
    </div>
    
    <a href="/iCensus-ent/public/residents" class="card clickable-card" style="max-width: 900px; width: 90%;">
        <span class="material-icons card-icon">groups</span>
        <h3 class="card-title">Manage Residents</h3>
        <p class="card-desc">Add, search, and update resident information.</p>
    </a>

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>