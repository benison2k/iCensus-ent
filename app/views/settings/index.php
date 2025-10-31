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
<body class="<?= $theme === 'dark' ? 'dark-mode' : ''; ?>">

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
        </div>

        <div class="settings-content">
            <div id="tab-account" class="tab-pane active">
                <h3>Account Information</h3>
                <form id="emailForm" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address (for 2FA)</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email address" required>
                    </div>
                    <input type="hidden" name="update_email" value="1">
                    <button type="submit"><span class="material-icons">save</span> Save Email</button>
                </form>
                <hr style="margin: 2rem 0;">
                <form id="usernameForm" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>
                    <input type="hidden" name="update_username" value="1">
                    <button type="submit"><span class="material-icons">save</span> Save Username</button>
                </form>
            </div>

            <div id="tab-security" class="tab-pane">
                <h3>Security Settings</h3>
                <form id="passwordForm" method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
                    </div>
                    <button type="button" id="verifyCurrentBtn">Verify Password</button>
                    <span id="verifyMessage" style="margin-left:1rem;color:red;"></span>
                    <div class="error-text" id="passwordOtpError" style="margin-bottom: 0;"></div>

                    <div id="newPasswordFields" style="display:none; margin-top:1.5rem;">
                        <div id="otpRequirement" style="display:none;" class="password-otp-group">
                             <h4>OTP Required</h4>
                            <p style="font-size:0.9rem; margin-bottom: 5px;">OTP sent to your email for security.</p>
                            <input type="text" name="otp" id="otpPasswordField" placeholder="Enter OTP" maxlength="6" pattern="\d{6}" inputmode="numeric">
                            <a href="#" id="resendOtpBtnPass" style="display:none;">Resend Code</a>
                            <span id="passCooldownTimer" class="small text-muted" style="margin-top: 5px; display: none;"></span>
                        </div>
                        <div class="form-group" style="margin-top:1.5rem;">
                            <label for="password">New Password</label>
                            <input type="password" name="password" id="password" placeholder="Enter new password" required>
                            <div class="strength-meter">
                                <div id="strength-bar"></div>
                            </div>
                            <span id="strength-text" class="strength-text"></span>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                             <div style="position:relative;">
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                                <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);"></span>
                            </div>
                        </div>
                        <input type="hidden" name="update_password" value="1">
                        <button type="submit" id="passwordSubmit" disabled>
                            <span class="material-icons">save</span> Save Password
                        </button>
                    </div>
                </form>
                <hr style="margin: 2rem 0;">
                 <div class="form-group">
                    <label for="twoFaSwitch">Two-Factor Authentication</label>
                    <p style="font-size:0.9rem; color:#555; margin-bottom: 1rem;">
                        Enable 2FA via email for an extra layer of security on login.
                    </p>
                    <form id="twoFaForm">
                         <div style="display:flex; align-items:center; gap:1rem;">
                            <label class="switch">
                                <input type="checkbox" id="twoFaSwitch" <?= $user['two_fa'] == 1 ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                            <span id="twoFaLabel"><?= $user['two_fa'] == 1 ? 'Enabled' : 'Disabled'; ?></span>
                        </div>
                    </form>
                </div>
            </div>

            <div id="tab-preferences" class="tab-pane">
                <h3>Preferences</h3>
                <div class="form-group">
                    <label for="themeSwitch">Theme</label>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <label class="switch">
                            <input type="checkbox" id="themeSwitch" <?= $theme === 'dark' ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                        <span id="themeLabel"><?= $theme === 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<div id="otpToggleModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; padding: 30px;">
        <span class="close" id="closeOtpToggleModal">&times;</span>
        <h3 style="margin-top: 0; text-align: center;">Confirm Disable 2FA</h3>
        <p class="text-muted" style="text-align: center;">Enter the 6-digit code sent to your email to confirm you want to disable Two-Factor Authentication.</p>
        
        <form id="otpToggleForm" action="<?= $base_url ?>/settings/verify-2fa-toggle-otp" method="POST" style="margin-top: 1.5rem;">
            <div class="input-wrapper mb-3" style="display: flex; justify-content: center;">
                <input type="text" name="otp" id="otpToggleInput" class="form-control" placeholder="______" required autofocus maxlength="6" pattern="\d{6}" inputmode="numeric">
            </div>
            <div class="error-text" id="otpToggleError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpToggleVerifyBtn">Confirm Disable</button>
        </form>
        
        <a href="#" id="resendToggleOtpBtn" class="small text-decoration-none" style="display: block; text-align: center;">Resend Code</a>
        <span id="cooldownToggleTimer" class="small text-muted" style="margin-top: 5px; display: none; text-align: center;"></span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                tabPanes.forEach(pane => {
                    if (pane.id === `tab-${button.dataset.tab}`) {
                        pane.classList.add('active');
                    } else {
                        pane.classList.remove('active');
                    }
                });
            });
        });
    });

    // --- GLOBAL VARS ---
    const BASE_URL = '<?= $base_url ?>';
    const USER_ROLE = '<?= $_SESSION['user']['role_name'] ?>';
    const IS_ADMIN = USER_ROLE === 'System Admin';
    const COOLDOWN_DURATION = 60;

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
                if (form.id === 'passwordForm') {
                    resetPasswordForm();
                }
                if (form.id === 'emailForm' || form.id === 'usernameForm') {
                     window.location.reload();
                }
            } else {
                showAjaxResult(result.message || 'An error occurred.', 'error');
            }
            return result;
        } catch (error) {
            showAjaxResult('A network error occurred. Please try again.', 'error');
            return {status: 'error', message: 'Network error'};
        }
    }

    document.getElementById('usernameForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this, BASE_URL + '/settings/process');
    });

    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this, BASE_URL + '/settings/process');
    });

    // --- PASSWORD CHANGE LOGIC ---
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
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    let currentPasswordVerified = false;
    let otpCooldownInterval = null;
    
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
        strengthBar.className = '';
        strengthText.textContent = '';
        otpPasswordField.required = false; // **FIX**
    }

    // STEP 1: Verify Current Password / Send OTP
    verifyBtn.addEventListener('click', async () => {
        // ... (rest of the function is unchanged)
        const current = currentPassword.value.trim();
    if(current === '') return;
    
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Checking...';
    verifyMessage.textContent = '';
    
    try {
        const formData = new URLSearchParams({current_password: current});
        const response = await fetch(BASE_URL + '/settings/verify-password', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            currentPasswordVerified = true;
            otpRequirement.style.display = 'none';
            otpPasswordField.required = false; // **FIX**
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = 'Password verified.';
            verifyMessage.style.color = 'green';
            currentPassword.disabled = true;
            
        } else if (result.status === 'otp_sent') {
            currentPasswordVerified = true;
            otpRequirement.style.display = 'block';
            otpPasswordField.required = true; // **FIX**
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = result.message;
            verifyMessage.style.color = 'green';
            currentPassword.disabled = true;
            startPassCooldown(COOLDOWN_DURATION);

        } else {
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

    // STEP 3: Final Password Submission (AJAX)
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Use the browser's built-in validation check
        if (!this.checkValidity()) {
            // If the form is invalid, try to find the first invalid field and focus it.
            const firstInvalid = this.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
            return;
        }
        
        if (!currentPasswordVerified || password.value !== confirmPassword.value) {
            passwordOtpError.textContent = 'Please ensure passwords match and current password is verified.';
            passwordOtpError.style.color = 'red';
            return;
        }

        const form = this;
        passwordSubmit.disabled = true;
        passwordSubmit.textContent = 'Saving...';
        passwordOtpError.textContent = '';
        
        handleFormSubmit(form, BASE_URL + '/settings/process').then(() => {
            passwordSubmit.disabled = false;
            passwordSubmit.innerHTML = '<span class="material-icons">save</span> Save Password';
        });
    });

    // ... (rest of the script is unchanged)
    // Cooldown logic
function startPassCooldown(duration) {
    clearInterval(otpCooldownInterval);
    let timeRemaining = duration;
    
    resendOtpBtnPass.style.display = 'none';
    passCooldownTimer.style.display = 'block';
    passwordOtpError.textContent = '';

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
            resendOtpBtnPass.style.display = 'block'; 
        }
    } catch (error) {
        passwordOtpError.textContent = 'Network error while trying to resend.';
        passwordOtpError.style.color = 'red';
    }
});


// Password Match Validation
function checkPasswordMatch() {
    const passwordValid = password.value.length >= 6 && password.value === confirmPassword.value;
    
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

// Password Strength
function checkPasswordStrength() {
    const pass = password.value;
    let score = 0;
    if (pass.length > 8) score++;
    if (pass.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) score++;
    if (pass.match(/([0-9])/)) score++;
    if (pass.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) score++;

    if (pass.length === 0) {
        strengthBar.className = '';
        strengthText.textContent = '';
        return;
    }

    if (score < 2) {
        strengthBar.className = 'weak';
        strengthText.textContent = 'Weak';
    } else if (score < 4) {
        strengthBar.className = 'medium';
        strengthText.textContent = 'Medium';
    } else {
        strengthBar.className = 'strong';
        strengthText.textContent = 'Strong';
    }
}

password.addEventListener('input', () => {
    checkPasswordStrength();
    checkPasswordMatch();
});
confirmPassword.addEventListener('input', checkPasswordMatch);

// 2FA Toggle Logic
const twoFaSwitch = document.getElementById('twoFaSwitch');
const twoFaLabel = document.getElementById('twoFaLabel');
const emailInput = document.querySelector('#emailForm input[name="email"]').value;

// 2FA Toggle Modal Constants
const otpToggleModal = document.getElementById('otpToggleModal');
const closeOtpToggleModal = document.getElementById('closeOtpToggleModal');
const otpToggleForm = document.getElementById('otpToggleForm');
const otpToggleInput = document.getElementById('otpToggleInput');
const otpToggleError = document.getElementById('otpToggleError');
const resendToggleOtpBtn = document.getElementById('resendToggleOtpBtn');
const cooldownToggleTimer = document.getElementById('cooldownToggleTimer');
let toggleTimerInterval = null; 

function startToggleCountdown(duration) {
    clearInterval(toggleTimerInterval);
    let timeRemaining = duration;
    
    resendToggleOtpBtn.style.display = 'none';
    cooldownToggleTimer.style.display = 'block';

    toggleTimerInterval = setInterval(() => {
        let seconds = timeRemaining % 60;
        let display = seconds < 10 ? "0" + seconds : seconds;
        
        cooldownToggleTimer.textContent = `Resend available in ${display}s`;
        
        if (timeRemaining <= 0) {
            clearInterval(toggleTimerInterval);
            resendToggleOtpBtn.style.display = 'block';
            cooldownToggleTimer.style.display = 'none';
            otpToggleError.textContent = 'The cooldown has expired. You may resend the code.';
            otpToggleError.style.color = '#0d6efd';
        }
        timeRemaining--;
    }, 1000);
}

function showOtpToggleModal(message, cooldown = 60) {
    otpToggleError.textContent = message;
    otpToggleError.style.color = '#0d6efd';
    otpToggleInput.value = '';
    otpToggleModal.style.display = 'flex';
    otpToggleInput.focus();
    startToggleCountdown(cooldown); 
}

closeOtpToggleModal.onclick = () => {
    clearInterval(toggleTimerInterval);
    otpToggleModal.style.display = 'none';
    window.location.reload();
};
window.addEventListener('click', (event) => {
    if (event.target === otpToggleModal) {
        clearInterval(toggleTimerInterval);
        otpToggleModal.style.display = 'none';
        window.location.reload(); 
    }
});

resendToggleOtpBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    
    otpToggleError.textContent = 'Sending...';
    otpToggleError.style.color = 'orange';
    resendToggleOtpBtn.style.display = 'none';
    
    try {
        const response = await fetch(BASE_URL + '/settings/toggleTwoFA', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ target_two_fa: 0 }) 
        });
        const result = await response.json();
        
        if (result.status === 'otp_required' || result.status === 'success') {
            otpToggleError.textContent = 'A new code has been sent. Check your email.';
            otpToggleError.style.color = 'green';
            startToggleCountdown(60); 
        } else if (result.status === 'cooldown') {
            otpToggleError.textContent = result.message;
            otpToggleError.style.color = 'red';
            startToggleCountdown(result.cooldown_remaining); 
        } else {
            otpToggleError.textContent = result.message;
            otpToggleError.style.color = 'red';
            resendToggleOtpBtn.style.display = 'block';
        }
    } catch (error) {
        otpToggleError.textContent = 'Network error while trying to resend.';
        otpToggleError.style.color = 'red';
        resendToggleOtpBtn.style.display = 'block';
        console.error(error);
    }
});

otpToggleForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const verifyBtn = document.getElementById('otpToggleVerifyBtn');
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';
    otpToggleError.textContent = '';
    
    try {
        const formData = new URLSearchParams(new FormData(otpToggleForm));
        
        const response = await fetch(otpToggleForm.action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            clearInterval(toggleTimerInterval);
            showAjaxResult(result.message, 'success');
            otpToggleModal.style.display = 'none';
            setTimeout(() => window.location.reload(), 500);
        } else {
            otpToggleError.textContent = result.message;
            otpToggleError.style.color = 'red';
        }
    } catch (error) {
        otpToggleError.textContent = 'Network error during verification.';
        otpToggleError.style.color = 'red';
        console.error(error);
    } finally {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Confirm Disable';
    }
});

if (twoFaSwitch) {
    twoFaSwitch.addEventListener('change', async function() {
        const isChecked = this.checked;
        const targetTwoFA = isChecked ? 1 : 0;
        
        if (targetTwoFA === 1 && emailInput.length === 0) {
            this.checked = false;
            showAjaxResult('Cannot enable 2FA: Please save a valid email address first.', 'error');
            return;
        }

        this.disabled = true;

        const data = new URLSearchParams({ target_two_fa: targetTwoFA });
        
        try {
            const response = await fetch(BASE_URL + '/settings/toggleTwoFA', {
                method: 'POST',
                body: data,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            const result = await response.json();

            if (result.status === 'success') {
                showAjaxResult(result.message, 'success');
                twoFaLabel.textContent = isChecked ? 'Enabled' : 'Disabled';
            } else if (result.status === 'otp_required') {
                this.checked = true; 
                showOtpToggleModal(result.message, COOLDOWN_DURATION);
            } else if (result.status === 'cooldown') {
                this.checked = true; 
                showOtpToggleModal(result.message, result.cooldown_remaining);
            } else {
                this.checked = !isChecked;
                showAjaxResult(result.message, 'error');
            }
        } catch (error) {
            this.checked = !isChecked;
            showAjaxResult('A network error occurred. Please try again.', 'error');
            console.error('Error toggling 2FA:', error);
        } finally {
            if (result.status !== 'otp_required' && result.status !== 'cooldown') {
                this.disabled = false;
            }
        }
    });
}

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
</script>
</body>
</html>