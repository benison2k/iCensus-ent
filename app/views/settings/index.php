<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Settings</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/settings.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
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

    <div class="card settings-card">
        <span class="material-icons card-icon">person</span>
        <h3 class="card-title">Account</h3>

        <form action="<?= $base_url ?>/settings/process" method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
            <button type="submit" name="update_username"><span class="material-icons">save</span> Save Username</button>
        </form>

        <hr style="margin:1.5rem 0;">

        <form action="<?= $base_url ?>/settings/process" method="POST" id="passwordForm">
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
                    <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);"></span>
                </div>
                <button type="submit" name="update_password" id="passwordSubmit" style="margin-top:1rem;" disabled>
                    <span class="material-icons">save</span> Save Password
                </button>
            </div>
        </form>
    </div>

    <div class="card settings-card">
        <span class="material-icons card-icon">tune</span>
        <h3 class="card-title">Preferences</h3>
        <form>
            <label>Theme</label><br>
            <label class="switch">
                <input type="checkbox" id="themeSwitch" <?= $theme==='dark'?'checked':''; ?>>
                <span class="slider round"></span>
            </label>
            <span id="themeLabel"><?= $theme==='dark'?'Dark Mode':'Light Mode'; ?></span>
        </form>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
// Password verification & match logic
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
    if(current === '') return;

    fetch('<?= $base_url ?>/settings/verify-password', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({current_password: current})
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            currentPasswordValid = true;
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = '';
        } else {
            currentPasswordValid = false;
            newPasswordFields.style.display = 'none';
            verifyMessage.textContent = data.message || 'Incorrect password';
        }
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

// Theme toggle logic
const themeSwitch = document.getElementById('themeSwitch');
themeSwitch.addEventListener('change', () => {
    const theme = themeSwitch.checked ? 'dark' : 'light';
    document.body.classList.toggle('dark-mode', theme === 'dark');
    document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';

    fetch('<?= $base_url ?>/settings/theme', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ theme: theme })
    });
});
</script>
</body>
</html>