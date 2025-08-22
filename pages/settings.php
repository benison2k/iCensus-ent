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
$auth->refreshUserSession($_SESSION['user']['id']); // refresh session

$user = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';

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

        <form action="../core/settings_process.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
            <button type="submit" name="update_username">
                <span class="material-icons">save</span> Save Username
            </button>
        </form>

        <hr style="margin:1.5rem 0; border-color:#eee;">

        <form action="../core/settings_process.php" method="POST" id="passwordForm">
            <label>New Password</label>
            <input type="password" name="password" id="password" placeholder="Enter new password" required>
            <label>Confirm Password</label>
            <div style="position:relative;">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:1.2rem;"></span>
            </div>
            <button type="submit" name="update_password" id="passwordSubmit" disabled>
                <span class="material-icons">save</span> Save Password
            </button>
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

<!-- Password Match JS -->
<script>
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const matchIcon = document.getElementById('passwordMatchIcon');
const submitBtn = document.getElementById('passwordSubmit');

function checkPasswordMatch() {
    if(confirmPassword.value===''){ matchIcon.innerHTML=''; submitBtn.disabled=true; return; }
    if(password.value===confirmPassword.value){ matchIcon.innerHTML='<span class="material-icons" style="color:green;">check_circle</span>'; submitBtn.disabled=false; }
    else{ matchIcon.innerHTML='<span class="material-icons" style="color:red;">cancel</span>'; submitBtn.disabled=true; }
}
password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);
</script>

<!-- Theme Toggle JS -->
<script>
const themeSwitch = document.getElementById('themeSwitch');
const themeLabel = document.getElementById('themeLabel');
const body = document.body;

themeSwitch.addEventListener('change', () => {
    const theme = themeSwitch.checked ? 'dark' : 'light';
    body.classList.toggle('dark-mode', theme==='dark');
    themeLabel.textContent = theme==='dark' ? 'Dark Mode' : 'Light Mode';

    fetch('../core/update_theme.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'theme=' + encodeURIComponent(theme)
    })
    .then(res => res.json())
    .then(data => { if(data.status!=='success') console.error('Theme update failed', data.message); })
    .catch(err => console.error('AJAX error:', err));
});
</script>

<?php if($modalMessage): ?>
<script src="../assets/js/modal.js"></script>
<script>initModal('resultModal');</script>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
