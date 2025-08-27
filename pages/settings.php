<?php
session_start();
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// --------------------------
// Modal Message Setup (from session flash)
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
    <link rel="stylesheet" href="../assets/css/modal.css"> <!-- Modal Styles -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome">
    <h2>Settings</h2>
</div>

<main class="dashboard">

    <?php
    // Include modal dynamically if there is a message
    if (!empty($modalMessage)) {
        $id = "resultModal";
        $message = $modalMessage;
        $type = $modalType;
        include __DIR__ . '/../components/modal.php';
    }
    ?>

    <div class="settings-grid">

        <!-- Account Settings Card -->
        <div class="card settings-card">
            <span class="material-icons card-icon">person</span>
            <h3 class="card-title">Account</h3>

            <!-- Username Form -->
            <form action="../core/settings_process.php" method="POST">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                <button type="submit" name="update_username">
                    <span class="material-icons">save</span> Save Username
                </button>
            </form>

            <hr style="margin:1.5rem 0; border-color:#eee;">

            <!-- Password Form -->
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
                <label>Theme</label>
                <select name="theme">
                    <option value="light" <?= $user['theme']==='light'?'selected':''; ?>>Light</option>
                    <option value="dark" <?= $user['theme']==='dark'?'selected':''; ?>>Dark</option>
                </select>

                <label>Language</label>
                <select name="language">
                    <option value="en" <?= $user['language']==='en'?'selected':''; ?>>English</option>
                    <option value="fil" <?= $user['language']==='fil'?'selected':''; ?>>Filipino</option>
                </select>

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
                    <input type="checkbox" name="2fa" value="1" <?= $user['two_fa'] ? 'checked' : ''; ?>>
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
