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
<style>
/* Custom styles for the password/OTP form */
.password-otp-group {
    border: 1px solid var(--border-color, #ccc);
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    background: var(--card-bg-light);
}
body.dark-mode .password-otp-group {
    background: var(--card-bg-dark);
    border-color: #555;
}
.password-otp-group h4 { margin-top: 0; font-size: 1.1rem; color: var(--text-dark); }
#otpPasswordField { text-align: center; font-weight: 700; letter-spacing: 2px; }
#resendOtpBtnPass { font-size: 0.85rem; cursor: pointer; text-decoration: underline !important; color: #0d6efd !important; margin-top: 5px; display: block; }
#passwordOtpError { margin-top: 10px; }

/* Styles for 2FA Toggle Modal */
#otpToggleModal .modal-content, #otpUnbindModal .modal-content { max-width: 400px; }
#otpToggleInput, #otpUnbindInput { text-align: center; font-size: 1.5rem; font-weight: 700; letter-spacing: 5px; }
#resendToggleOtpBtn, #resendUnbindOtpBtn { font-size: 0.85rem; cursor: pointer; text-decoration: underline !important; color: #0d6efd !important; margin-top: 10px; }
#otpToggleError, #otpUnbindError { margin-top: 10px; }

/* Styles for Web App Info Tab */
.info-group { margin-bottom: 1.5rem; }
.info-group h4 { font-size: 1.2rem; color: #333; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem; }
body.dark-mode .info-group h4 { color: #fff; border-bottom-color: #4a5a6a; }
.info-item { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.info-item strong { font-weight: 600; }

.button-group { display: flex; gap: 0.5rem; align-items: center; }
#unbindEmailBtn { background-color: #dc3545; }
#unbindEmailBtn:hover { background-color: #c82333; }
body.dark-mode #unbindEmailBtn { background-color: #c82333; }
body.dark-mode #unbindEmailBtn:hover { background-color: #a71d2a; }
</style>
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