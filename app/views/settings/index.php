<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Settings</title>
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/settings.css">
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

.password-otp-group h4 {
    margin-top: 0;
    font-size: 1.1rem;
    color: var(--text-dark);
}
#otpPasswordField {
    text-align: center;
    font-weight: 700;
    letter-spacing: 2px;
}

#resendOtpBtnPass {
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: underline !important;
    color: #0d6efd !important;
    margin-top: 5px;
    display: block;
}
#passwordOtpError {
    margin-top: 10px;
}
</style>

</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Settings</h2></div>
<main class="dashboard">

<div id="ajaxResultModal" class="modal" data-show="false">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p id="ajaxResultMessage"></p>
    </div>
</div>

<div class="settings-grid">

    <div class="card settings-card">
        <span class="material-icons card-icon">person</span>
        <h3 class="card-title">Account</h3>
        
        <form id="emailForm" method="POST">
             <label>Email Address (for 2FA)</label>
             <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email address" required>
             <input type="hidden" name="update_email" value="1">
             <button type="submit"><span class="material-icons">save</span> Save Email</button>
         </form>

        <form id="usernameForm" method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
            <input type="hidden" name="update_username" value="1">
            <button type="submit"><span class="material-icons">save</span> Save Username</button>
        </form>

        <hr style="margin:1.5rem 0;">

        <form id="passwordForm" method="POST">
            <label>Current Password</label>
            <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
            <button type="button" id="verifyCurrentBtn" style="margin-top:0.5rem;">Verify Password</button>
            
            <span id="verifyMessage" style="margin-left:1rem;color:red;"></span>
            <div class="error-text" id="passwordOtpError" style="margin-bottom: 0;"></div>

            <div id="newPasswordFields" style="display:none; margin-top:1rem;">
                
                <div id="otpRequirement" style="display:none;" class="password-otp-group">
                    <h4>OTP Required</h4>
                    <p style="font-size:0.9rem; margin-bottom: 5px;">OTP sent to your email for security.</p>
                    <input type="text" name="otp" id="otpPasswordField" placeholder="Enter OTP" required maxlength="6" pattern="\d{6}" inputmode="numeric">
                    <a href="#" id="resendOtpBtnPass" style="display:none;">Resend Code</a>
                    <span id="passCooldownTimer" class="small text-muted" style="margin-top: 5px; display: none;"></span>
                </div>
                
                <label style="margin-top:15px;">New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password" required>
                <label>Confirm Password</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);"></span>
                </div>
                <input type="hidden" name="update_password" value="1">
                <button type="submit" id="passwordSubmit" style="margin-top:1rem;" disabled>
                    <span class="material-icons">save</span> Save Password
                </button>
            </div>
        </form>
    </div>
    
    <div class="card settings-card">
        <span class="material-icons card-icon">security</span>
        <h3 class="card-title">Two-Factor Authentication</h3>
        <p style="font-size:0.9rem; color:#555; margin-bottom: 1rem;">
            Enable 2FA via email for an extra layer of security on login.
        </p>
        <form id="twoFaForm">
            <label>Enable 2FA</label><br>
            <label class="switch">
                <input type="checkbox" id="twoFaSwitch" <?= $user['two_fa'] == 1 ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
            <span id="twoFaLabel" style="margin-left: 1rem;"><?= $user['two_fa'] == 1 ? 'Enabled' : 'Disabled'; ?></span>
        </form>
        <p style="font-size:0.8rem; color:#777; margin-top: 1.5rem;">
            *Note: Two-Factor Authentication requires a valid email address to be set above.
        </p>
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
// --- GLOBAL VARS ---
const BASE_URL = '<?= $base_url ?>';
const USER_ROLE = '<?= $_SESSION['user']['role_name'] ?>';
const IS_ADMIN = USER_ROLE === 'System Admin';
const COOLDOWN_DURATION = 60; // Must match PHP


// --- AJAX FORM SUBMISSION & MESSAGE MODAL ---
const ajaxModal = document.getElementById('ajaxResultModal');
const ajaxMessage = document.getElementById('ajaxResultMessage');
const ajaxModalContent = ajaxModal.querySelector('.modal-content');
const ajaxCloseBtn = ajaxModal.querySelector('.close');

ajaxCloseBtn.onclick = () => ajaxModal.style.display = "none";
window.onclick = (event) => { if (event.target === ajaxModal) ajaxModal.style.display = "none"; };

function showAjaxResult(message, type = 'success') {
    ajaxMessage.textContent = message;
    ajaxModalContent.className = 'modal-content ' + type;
    ajaxModal.style.display = 'block';
    setTimeout(() => { ajaxModal.style.display = "none"; }, 4000);
}

async function handleFormSubmit(form, url) {
    try {
        const formData = new URLSearchParams(new FormData(form));
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (response.ok) {
            showAjaxResult(result.message, 'success');
            // Reset forms after success
            if (form.id === 'passwordForm') {
                resetPasswordForm(); // Custom reset function
            }
            if (form.id === 'emailForm' || form.id === 'usernameForm') {
                 window.location.reload();
            }
        } else {
            showAjaxResult(result.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showAjaxResult('A network error occurred. Please try again.', 'error');
    }
}

// Attach listeners to forms
document.getElementById('usernameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    handleFormSubmit(this, BASE_URL + '/settings/process');
});

document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    handleFormSubmit(this, BASE_URL + '/settings/process');
});

// --- PASSWORD CHANGE LOGIC (STEPPED) ---
const currentPassword = document.getElementById('current_password');
const verifyBtn = document.getElementById('verifyCurrentBtn');
const verifyMessage = document.getElementById('verifyMessage');
const newPasswordFields = document.getElementById('newPasswordFields');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const matchIcon = document.getElementById('passwordMatchIcon');
const passwordSubmit = document.getElementById('passwordSubmit');
const otpRequirement = document.getElementById('otpRequirement');
const otpPasswordField = document.getElementById('otpPasswordField');
const resendOtpBtnPass = document.getElementById('resendOtpBtnPass');
const passCooldownTimer = document.getElementById('passCooldownTimer');
const passwordOtpError = document.getElementById('passwordOtpError');

let currentPasswordVerified = false;
let otpCooldownInterval = null;

// Initially hide OTP field if not Admin
if (IS_ADMIN) {
    otpRequirement.style.display = 'none';
}


// Helper to reset the entire password change section
function resetPasswordForm() {
    document.getElementById('passwordForm').reset();
    currentPasswordVerified = false;
    newPasswordFields.style.display = 'none';
    verifyMessage.textContent = '';
    passwordSubmit.disabled = true;
    passwordOtpError.textContent = '';
    currentPassword.disabled = false;
    clearInterval(otpCooldownInterval);
    resendOtpBtnPass.style.display = 'none';
    passCooldownTimer.style.display = 'none';
    passCooldownTimer.textContent = '';
    // Ensure OTP requirement is hidden again
    if (IS_ADMIN) {
        otpRequirement.style.display = 'none';
    }
}

// Cooldown logic for password change
function startPassCooldown(duration) {
    clearInterval(otpCooldownInterval);
    let timeRemaining = duration;
    
    resendOtpBtnPass.style.display = 'none';
    passCooldownTimer.style.display = 'block';
    passwordOtpError.textContent = ''; // Clear previous error

    otpCooldownInterval = setInterval(() => {
        let seconds = timeRemaining % 60;
        let display = seconds < 10 ? "0" + seconds : seconds;
        
        passCooldownTimer.textContent = `Resend available in ${display}s`;
        
        if (timeRemaining <= 0) {
            clearInterval(otpCooldownInterval);
            resendOtpBtnPass.style.display = 'block';
            passCooldownTimer.style.display = 'none';
            passwordOtpError.textContent = 'The cooldown has expired. You may resend the code.';
            passwordOtpError.style.color = '#0d6efd';
        }
        timeRemaining--;
    }, 1000);
}

// STEP 1: Verify Current Password / Send OTP
verifyBtn.addEventListener('click', async () => {
    const current = currentPassword.value.trim();
    if(current === '') return;
    
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Checking...';
    verifyMessage.textContent = '';
    
    try {
        const formData = new URLSearchParams({current_password: current});
        // Call the modified verify-password endpoint
        const response = await fetch(BASE_URL + '/settings/verify-password', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            // Non-Admin: Proceed directly to new password fields
            currentPasswordVerified = true;
            otpRequirement.style.display = 'none';
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = 'Password verified.';
            verifyMessage.style.color = 'green';
            currentPassword.disabled = true; // Lock current password field
            
        } else if (result.status === 'otp_sent') {
            // Admin: OTP sent, show OTP field
            currentPasswordVerified = true;
            otpRequirement.style.display = 'block';
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = result.message;
            verifyMessage.style.color = 'green';
            currentPassword.disabled = true; // Lock current password field
            
            // Start cooldown timer
            startPassCooldown(COOLDOWN_DURATION);

        } else {
            // Error: Incorrect password or missing admin email
            currentPasswordVerified = false;
            newPasswordFields.style.display = 'none';
            verifyMessage.textContent = result.message || 'Incorrect password.';
            verifyMessage.style.color = 'red';
            currentPassword.disabled = false;
        }
    } catch (error) {
        verifyMessage.textContent = 'Network error during verification.';
        verifyMessage.style.color = 'red';
        currentPassword.disabled = false;
    } finally {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify Password';
    }
});


// Resend OTP for Password Change
resendOtpBtnPass.addEventListener('click', async (e) => {
    e.preventDefault();
    passwordOtpError.textContent = 'Sending...';
    passwordOtpError.style.color = '#0d6efd';
    
    try {
        const response = await fetch(BASE_URL + '/settings/resendPasswordChangeOtp');
        const result = await response.json();
        
        if (result.status === 'success') {
            passwordOtpError.textContent = result.message;
            passwordOtpError.style.color = 'green';
            startPassCooldown(COOLDOWN_DURATION); 
            
        } else if (result.status === 'cooldown') {
            passwordOtpError.textContent = result.message;
            passwordOtpError.style.color = 'red';
            startPassCooldown(result.cooldown_remaining); 
            
        } else {
            passwordOtpError.textContent = result.message;
            passwordOtpError.style.color = 'red';
            // Re-show button if general error
            resendOtpBtnPass.style.display = 'block'; 
        }
    } catch (error) {
        passwordOtpError.textContent = 'Network error while trying to resend.';
        passwordOtpError.style.color = 'red';
    }
});


// STEP 2: Password Match Validation
function checkPasswordMatch() {
    const passwordValid = password.value !== '' && password.value === confirmPassword.value;
    
    if (password.value === '' || confirmPassword.value === '') {
        matchIcon.innerHTML = '';
        passwordSubmit.disabled = true;
        return;
    }
    
    if(passwordValid) {
        matchIcon.innerHTML = '<span class="material-icons" style="color:green;">check_circle</span>';
        passwordSubmit.disabled = false;
    } else {
        matchIcon.innerHTML = '<span class="material-icons" style="color:red;">cancel</span>';
        passwordSubmit.disabled = true;
    }
}
password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);


// STEP 3: Final Password Submission (AJAX)
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!currentPasswordVerified || password.value !== confirmPassword.value) {
        passwordOtpError.textContent = 'Please verify current password and ensure new passwords match.';
        passwordOtpError.style.color = 'red';
        return;
    }
    
    // Check OTP field for admin submission
    if (IS_ADMIN && otpPasswordField.value.trim().length !== 6) {
        passwordOtpError.textContent = 'Please enter the 6-digit OTP.';
        passwordOtpError.style.color = 'red';
        return;
    }

    const form = this;
    passwordSubmit.disabled = true;
    passwordSubmit.textContent = 'Saving...';
    passwordOtpError.textContent = '';
    
    // Submit the form which includes: current_password, password, confirm_password, update_password=1, and otp (if visible/admin)
    handleFormSubmit(form, BASE_URL + '/settings/process').then(() => {
        passwordSubmit.disabled = false;
        passwordSubmit.innerHTML = '<span class="material-icons">save</span> Save Password';
    });
});


// Theme toggle logic (omitted for brevity)
const themeSwitch = document.getElementById('themeSwitch');
themeSwitch.addEventListener('change', () => {
    const theme = themeSwitch.checked ? 'dark' : 'light';
    document.body.classList.toggle('dark-mode', theme === 'dark');
    document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';

    fetch(BASE_URL + '/settings/theme', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ theme: theme })
    });
});

// 2FA Toggle Logic (omitted for brevity)
const twoFaSwitch = document.getElementById('twoFaSwitch');
const twoFaLabel = document.getElementById('twoFaLabel');

if (twoFaSwitch) {
    twoFaSwitch.addEventListener('change', async () => {
        const isEnabled = twoFaSwitch.checked;
        const newStatus = isEnabled ? 1 : 0;
        
        twoFaSwitch.disabled = true;

        try {
            const response = await fetch(BASE_URL + '/settings/toggleTwoFA', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ two_fa: newStatus })
            });
            const result = await response.json();

            if (result.status === 'success') {
                twoFaLabel.textContent = isEnabled ? 'Enabled' : 'Disabled';
                showAjaxResult(result.message, 'success');
            } else {
                twoFaSwitch.checked = !isEnabled;
                twoFaLabel.textContent = isEnabled ? 'Disabled' : 'Enabled';
                showAjaxResult(result.message, 'error');
            }
        } catch (error) {
            twoFaSwitch.checked = !isEnabled;
            twoFaLabel.textContent = isEnabled ? 'Disabled' : 'Enabled';
            showAjaxResult('A network error occurred. Please try again.', 'error');
        } finally {
            twoFaSwitch.disabled = false;
        }
    });
}
</script>
</body>
</html>