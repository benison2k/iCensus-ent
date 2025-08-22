<?php
session_start();
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// --------------------------
// Safe fallback for theme
// --------------------------
$theme = $user['theme'] ?? 'light';

// --------------------------
// Modal Message Setup
// --------------------------
$modalMessage = '';
$modalType = '';
if (isset($_SESSION['modal'])) {
    $modalMessage = $_SESSION['modal']['message'];
    $modalType = $_SESSION['modal']['type'];
    unset($_SESSION['modal']); // clear flash after showing once
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Settings</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
/* Smooth transition for theme change */
body {
    transition: background-color 0.4s, color 0.4s;
}
.settings-card {
    transition: background-color 0.4s, color 0.4s, transform 0.2s;
}

/* Dark Mode Styles */
body.dark-mode {
    background-color: #121212;
    color: #e0e0e0;
}
body.dark-mode .settings-card {
    background-color: #1e1e1e;
    color: #e0e0e0;
}
body.dark-mode input, 
body.dark-mode select {
    background-color: #2c2c2c;
    color: #e0e0e0;
    border-color: #555;
}
body.dark-mode button {
    background-color: #388e3c;
}
body.dark-mode button:hover {
    background-color: #1b5e20;
}

/* Toggle Switch Styles */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.switch input { display:none; }
.slider {
  position: absolute;
  cursor: pointer;
  top:0; left:0; right:0; bottom:0;
  background-color:#ccc;
  transition:0.4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height:26px;
  width:26px;
  left:4px;
  bottom:4px;
  background-color:white;
  transition:0.4s;
  border-radius:50%;
}
input:checked + .slider {
  background-color:#2e7d32;
}
input:checked + .slider:before {
  transform: translateX(26px);
}
/* Optional Sun/Moon icons inside slider */
.slider .icon {
  position: absolute;
  font-size: 14px;
  top:50%;
  transform:translateY(-50%);
}
.slider .sun { left:6px; }
.slider .moon { right:6px; }
</style>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : ''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Settings</h2>
</div>

<main class="dashboard">

<?php if (!empty($modalMessage)): ?>
    <?php
        $id = "resultModal";
        $message = $modalMessage;
        $type = $modalType;
        include __DIR__ . '/../components/modal.php';
    ?>
<?php endif; ?>

<div class="settings-grid">

    <!-- Account Settings Card -->
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

    <!-- Preferences Settings Card -->
    <div class="card settings-card">
        <span class="material-icons card-icon">tune</span>
        <h3 class="card-title">Preferences</h3>
        <form action="../core/settings_process.php" method="POST">

            <label>Theme</label><br>
            <label class="switch">
                <input type="checkbox" name="theme" value="dark" <?= $theme === 'dark' ? 'checked' : ''; ?>>
                <span class="slider round">
                    <span class="icon sun">☀️</span>
                    <span class="icon moon">🌙</span>
                </span>
            </label>
            <span id="themeLabel"><?= $theme === 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>

            <br><br>

            <label>Language</label>
            <select name="language">
                <option value="en" <?= ($user['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="fil" <?= ($user['language'] ?? 'en') === 'fil' ? 'selected' : ''; ?>>Filipino</option>
            </select>

            <br><br>

            <button type="submit" name="update_preferences">
                <span class="material-icons">save</span> Save
            </button>
        </form>
    </div>

    <!-- Security Settings Card -->
    <div class="card settings-card">
        <span class="material-icons card-icon">security</span>
        <h3 class="card-title">Security</h3>
        <form action="../core/settings_process.php" method="POST">
            <label>
                <input type="checkbox" name="2fa" value="1" <?= ($user['two_fa'] ?? 0) ? 'checked' : ''; ?>>
                Enable 2FA
            </label>
            <button type="submit" name="update_security">
                <span class="material-icons">save</span> Save
            </button>
        </form>
    </div>

</div>
</main>

<!-- JS for password match -->
<script>
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const matchIcon = document.getElementById('passwordMatchIcon');
const submitBtn = document.getElementById('passwordSubmit');

function checkPasswordMatch() {
    if(confirmPassword.value==='') { matchIcon.textContent=''; submitBtn.disabled=true; return; }
    if(password.value===confirmPassword.value) { matchIcon.textContent='✔️'; matchIcon.style.color='green'; submitBtn.disabled=false; }
    else { matchIcon.textContent='❌'; matchIcon.style.color='red'; submitBtn.disabled=true; }
}
password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);
</script>

<!-- JS for instant theme toggle -->
<script>
const themeSwitch = document.querySelector('.switch input');
const themeLabel = document.getElementById('themeLabel');
const body = document.body;

themeSwitch.addEventListener('change', () => {
    if(themeSwitch.checked) {
        body.classList.add('dark-mode');
        themeLabel.textContent = 'Dark Mode';
    } else {
        body.classList.remove('dark-mode');
        themeLabel.textContent = 'Light Mode';
    }
});
</script>

<!-- Initialize modal -->
<?php if(!empty($modalMessage)): ?>
<script src="../assets/js/modal.js"></script>
<script>
    initModal('resultModal');
</script>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
