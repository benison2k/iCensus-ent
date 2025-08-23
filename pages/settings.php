<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Initialize DB and Auth
$db = new Database($config);
$auth = new Auth($db);
$auth->refreshUserSession($_SESSION['user']['id']); // Line 22

// Re-read session after refresh
$user = $_SESSION['user']; // Line 23
$theme = $user['theme'] ?? 'light'; // Line 24

// Modal setup
$modalMessage = $_SESSION['modal']['message'] ?? '';
$modalType = $_SESSION['modal']['type'] ?? '';
unset($_SESSION['modal']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Settings</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Hide scrollbars during loading -->
<style>
    body { overflow: hidden; }
</style>

</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Settings</h2></div>
<main class="dashboard">

<?php if ($modalMessage):
    $id="resultModal"; $message=$modalMessage; $type=$modalType;
    include __DIR__ . '/../components/modal.php';
endif; ?>

<div class="settings-grid">

    <!-- Account Card -->
    <div class="card settings-card">
        <span class="material-icons card-icon">person</span>
        <h3 class="card-title">Account</h3>

        <!-- Username Form -->
        <form action="../core/settings_process.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
            <button type="submit" name="update_username">
                <span class="material-icons">save</span> Save Username
            </button>
        </form>

        <hr style="margin:1.5rem 0; border-color:#eee;">

        <!-- Password Form -->
        <form action="../core/settings_process.php" method="POST" id="passwordForm">
            <label>Current Password</label>
            <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
            <button type="button" id="verifyCurrentBtn" style="margin-top:0.5rem;">Verify Password</button>
            <span id="verifyMessage" style="margin-left:1rem;color:red;"></span>

            <div id="newPasswordFields" style="display:none; margin-top:1rem;">
                <label>New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password" required>

                <label>Confirm Password</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:1.2rem;"></span>
                </div>

                <button type="submit" name="update_password" id="passwordSubmit" style="margin-top:1rem;" disabled>
                    <span class="material-icons">save</span> Save Password
                </button>
            </div>
        </form>
    </div>

    <!-- Preferences Card -->
    <div class="card settings-card">
        <span class="material-icons card-icon">tune</span>
        <h3 class="card-title">Preferences</h3>
        <form>
            <label>Theme</label><br>
            <label class="switch">
                <input type="checkbox" id="themeSwitch" <?= $theme==='dark'?'checked':''; ?>>
                <span class="slider round">
                    <span class="material-icons icon sun">light_mode</span>
                    <span class="material-icons icon moon">dark_mode</span>
                </span>
            </label>
            <span id="themeLabel"><?= $theme==='dark'?'Dark Mode':'Light Mode'; ?></span>
        </form>
    </div>

    <!-- Security Card -->
    <div class="card settings-card">
        <span class="material-icons card-icon">security</span>
        <h3 class="card-title">Security</h3>
        <form action="../core/settings_process.php" method="POST">
            <label><input type="checkbox" name="2fa" value="1" <?= ($user['two_fa'] ?? 0)?'checked':''; ?>> Enable 2FA</label>
            <button type="submit" name="update_security"><span class="material-icons">save</span> Save</button>
        </form>
    </div>

</div>
</main>

<script>
// Password verification & match
const currentPassword = document.getElementById('current_password');
const verifyBtn = document.getElementById('verifyCurrentBtn');
const verifyMessage = document.getElementById('verifyMessage');
const newPasswordFields = document.getElementById('newPasswordFields');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const matchIcon = document.getElementById('passwordMatchIcon');
const submitBtn = document.getElementById('passwordSubmit');

let currentPasswordValid = false;

verifyBtn.addEventListener('click', () => {
    const current = currentPassword.value.trim();
    if(current === '') {
        verifyMessage.textContent = 'Please enter your current password';
        return;
    }
    verifyMessage.textContent = '';

    fetch('../core/verify_password.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({current_password: current})
    })
    .then(res => res.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if(data.status === 'success') {
                currentPasswordValid = true;
                newPasswordFields.style.display = 'block';
                verifyMessage.textContent = '';
            } else {
                currentPasswordValid = false;
                newPasswordFields.style.display = 'none';
                password.value = '';
                confirmPassword.value = '';
                matchIcon.innerHTML = '';
                submitBtn.disabled = true;
                verifyMessage.textContent = data.message || 'Incorrect current password';
            }
        } catch(e) {
            console.error('JSON parse error:', e, text);
            verifyMessage.textContent = 'Error verifying password (invalid response)';
        }
    })
    .catch(err => {
        console.error('AJAX error:', err);
        verifyMessage.textContent = 'Error verifying password (network or PHP)';
    });
});

function checkPasswordMatch() {
    if(!currentPasswordValid || password.value === '' || confirmPassword.value === '') {
        matchIcon.innerHTML = '';
        submitBtn.disabled = true;
        return;
    }
    if(password.value === confirmPassword.value) {
        matchIcon.innerHTML = '<span class="material-icons" style="color:green;">check_circle</span>';
        submitBtn.disabled = false;
    } else {
        matchIcon.innerHTML = '<span class="material-icons" style="color:red;">cancel</span>';
        submitBtn.disabled = true;
    }
}

password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);

// Theme toggle
const themeSwitch = document.getElementById('themeSwitch');
const themeLabel = document.getElementById('themeLabel');
const body = document.body;

// Ensure checkbox matches body class on load
themeSwitch.checked = body.classList.contains('dark-mode');
themeLabel.textContent = themeSwitch.checked ? 'Dark Mode' : 'Light Mode';

themeSwitch.addEventListener('change', () => {
    const theme = themeSwitch.checked ? 'dark' : 'light';
    body.classList.toggle('dark-mode', theme==='dark');
    themeLabel.textContent = theme==='dark' ? 'Dark Mode' : 'Light Mode';

    fetch('../core/update_theme.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({theme: theme})
    })
    .then(res => res.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if(data.status === 'success') {
                themeSwitch.checked = data.theme === 'dark';
                body.classList.toggle('dark-mode', data.theme==='dark');
                themeLabel.textContent = data.theme==='dark' ? 'Dark Mode' : 'Light Mode';
            } else {
                console.error('Theme update failed', data.message);
            }
        } catch(e) {
            console.error('Theme JSON parse error', e, text);
        }
    })
    .catch(err => console.error('AJAX error', err));
});
</script>

<?php if($modalMessage): ?>
<script src="../assets/js/modal.js"></script>
<script>if(typeof initModal==='function'){ initModal('resultModal'); }</script>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>

<!-- Restore scroll after everything loads -->
<script>
window.addEventListener('load', () => {
    document.body.style.overflow = '';
});
</script>

</body>
</html>
