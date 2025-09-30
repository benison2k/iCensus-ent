<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Database Tools</title>
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/settings.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Database Tools</h2></div>

<main class="dashboard">
<?php if ($modalMessage):
    $id="resultModal"; $message=$modalMessage; $type=$modalType;
    include __DIR__ . '/../components/modal.php';
endif; ?>

<div class="settings-grid">
    <div class="card settings-card">
        <span class="material-icons card-icon">cloud_download</span>
        <h3 class="card-title">Database Backup</h3>
        <p>Create a full backup of the system database.</p>
        <form action="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/sysadmin/db-tools/process" method="POST">
            <button type="submit" name="action" value="backup_db">
                <span class="material-icons">download</span> Run Backup
            </button>
        </form>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>