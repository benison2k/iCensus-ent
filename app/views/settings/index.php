<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Settings</title>
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/settings_new.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body class="<?= $theme==='dark'?'dark-mode':'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Settings</h2></div>
<main class="dashboard">

<div id="ajaxResultModal" class="modal" data-show="false">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p id="ajaxResultMessage"></p>
    </div>
</div>

<div class="settings-container">
    <div class="settings-tabs">
        <button type="button" class="tab-button active" data-tab="account">
            <span class="material-icons">person</span> Account
        </button>
        <button type="button" class="tab-button" data-tab="security">
            <span class="material-icons">security</span> Security
        </button>
        <button type="button" class="tab-button" data-tab="preferences">
            <span class="material-icons">tune</span> Preferences
        </button>
        <button type="button" class="tab-button" data-tab="info">
            <span class="material-icons">info</span> Web App Info
        </button>
    </div>

    <div class="settings-content">
        <?php
        // Include partial views for each tab
        include __DIR__ . '/_account.php';
        include __DIR__ . '/_security.php';
        include __DIR__ . '/_preferences.php';
        include __DIR__ . '/_info.php';
        ?>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script type="module" src="<?= $base_url ?>/assets/js/settings/settings.js"></script>

</body>
</html>