<?php 
require_once __DIR__ . '/auth_check.php'; 

// Modal setup for feedback messages
$modalMessage = $_SESSION['modal']['message'] ?? '';
$modalType = $_SESSION['modal']['type'] ?? '';
unset($_SESSION['modal']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Database Tools</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/modal.css">
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

<div class="settings-grid" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
    <div class="card settings-card">
        <span class="material-icons card-icon">cloud_download</span>
        <h3 class="card-title">Database Backup</h3>
        <p style="margin-bottom: 1rem; color: #555;">Create a full backup of the system database. The backup file will be saved on the server.</p>
        <form action="db_tools_process.php" method="POST">
            <button type="submit" name="action" value="backup_db">
                <span class="material-icons">download</span> Run Backup
            </button>
        </form>
    </div>

    <div class="card settings-card">
        <span class="material-icons card-icon">cloud_upload</span>
        <h3 class="card-title">Database Restore</h3>
        <p style="margin-bottom: 1rem; color: #555;">Restore the system database from a SQL backup file. WARNING: This will overwrite ALL existing data.</p>
        <form action="db_tools_process.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="backup_file" accept=".sql" required style="margin-bottom: 1rem;">
            <button type="submit" name="action" value="restore_db">
                <span class="material-icons">upload</span> Run Restore
            </button>
        </form>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="../assets/js/modal.js"></script>

</body>
</html>