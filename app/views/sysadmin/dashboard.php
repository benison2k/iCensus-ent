<?php
// The controller now handles the authentication check.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - System Admin</title>
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/dashboard.css">
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
        <a href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/sysadmin/users" class="card clickable-card">
            <span class="material-icons card-icon">manage_accounts</span>
            <h3 class="card-title">Manage Users</h3>
            <p class="card-desc">Add, edit, and manage all user accounts.</p>
        </a>

        <a href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/sysadmin/db-tools" class="card clickable-card">
            <span class="material-icons card-icon">storage</span>
            <h3 class="card-title">Database Tools</h3>
            <p class="card-desc">Perform system backups and maintenance.</p>
        </a>
        
        <a href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/sysadmin/logs" class="card clickable-card">
            <span class="material-icons card-icon">receipt_long</span>
            <h3 class="card-title">System Logs</h3>
            <p class="card-desc">View system-wide activity and error logs.</p>
        </a>

        <a href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/settings" class="card clickable-card">
            <span class="material-icons card-icon">settings</span>
            <h3 class="card-title">My Settings</h3>
            <p class="card-desc">Adjust your personal account preferences.</p>
        </a>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>