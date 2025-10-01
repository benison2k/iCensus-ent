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

<main class="dashboard">
    <div class="card-grid" style="grid-template-columns: 1fr; max-width: 600px;">
        <a href="/iCensus-ent/public/residents" class="card clickable-card">
            <span class="material-icons card-icon">groups</span>
            <h3 class="card-title">Manage Residents</h3>
            <p class="card-desc">Add, search, and update resident information.</p>
        </a>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>