<?php 
// This is our secure gatekeeper for the System Admin area
require_once __DIR__ . '/auth_check.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - System Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Welcome, <?= htmlspecialchars($user['full_name']); ?>!</h2>
    <p style="margin-top: 0.5rem;">You are in the central control panel for the iCensus system.</p>
</div>

<main class="dashboard">
    <div class="card-grid">
        <a href="manage_users.php" class="card clickable-card">
            <span class="material-icons card-icon">manage_accounts</span>
            <h3 class="card-title">Manage Users</h3>
            <p class="card-desc">Add, edit, and manage Encoder accounts.</p>
        </a>

        <a href="db_tools.php" class="card clickable-card">
            <span class="material-icons card-icon">storage</span>
            <h3 class="card-title">Database Tools</h3>
            <p class="card-desc">Perform system backups and maintenance.</p>
        </a>
        
        <a href="system_logs.php" class="card clickable-card"> <span class="material-icons card-icon">receipt_long</span>
            <h3 class="card-title">System Logs</h3>
            <p class="card-desc">View system-wide activity and error logs.</p>
        </a>

        <a href="../pages/settings.php" class="card clickable-card">
            <span class="material-icons card-icon">settings</span>
            <h3 class="card-title">My Settings</h3>
            <p class="card-desc">Adjust your personal account preferences.</p>
        </a>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>