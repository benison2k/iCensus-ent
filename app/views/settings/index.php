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

/* Styles for 2FA Toggle Modal */
#otpToggleModal .modal-content {
    max-width: 400px;
}

#otpToggleInput {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 5px;
}
#resendToggleOtpBtn {
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: underline !important;
    color: #0d6efd !important;
    margin-top: 10px;
}
#otpToggleError {
    margin-top: 10px;
}

/* Styles for Web App Info Tab */
.info-group {
    margin-bottom: 1.5rem;
}

.info-group h4 {
    font-size: 1.2rem;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

body.dark-mode .info-group h4 {
    color: #fff;
    border-bottom-color: #4a5a6a;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.info-item strong {
    font-weight: 600;
}

/* --- NEW: Button Group for Email Form --- */
.button-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

#unbindEmailBtn {
    background-color: #dc3545;
}
#unbindEmailBtn:hover {
    background-color: #c82333;
}
body.dark-mode #unbindEmailBtn {
    background-color: #c82333;
}
body.dark-mode #unbindEmailBtn:hover {
    background-color: #a71d2a;
}

/* --- NEW: Confirmation Modal Styles --- */
#unbindEmailModal .modal-content {
    max-width: 450px;
    text-align: left; /* Aligns text left */
}
#unbindEmailModal h3 {
    color: #dc3545; /* Red text for warning */
    text-align: left;
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
}
#unbindEmailModal p {
    text-align: left;
    margin-top: 0.5rem;
    font-size: 0.95rem;
    color: #333;
}
.modal-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.modal-actions .btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.9rem;
}
.modal-actions .btn.confirm {
    background-color: #dc3545;
    color: white;
}
.modal-actions .btn.cancel {
    background-color: #f1f1f1;
    color: #333;
    border: 1px solid #ddd;
}

/* Dark Mode for new modal */
body.dark-mode #unbindEmailModal .modal-content {
    background: #2C3E50;
    border-left: none; /* Remove the side bar */
}
body.dark-mode #unbindEmailModal h3 {
    color: #f44336;
}
body.dark-mode #unbindEmailModal p {
    color: #eee;
}
body.dark-mode .modal-actions .btn.cancel {
    background-color: #4a5a6a;
    color: #fff;
    border: 1px solid #555;
}
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
        <div id="tab-account" class="tab-pane active">
            <h3>Account Information</h3>
            <form id="emailForm" method="POST">
                <div class="form-group">
                    <label for="email">Email Address (for 2FA & Password Reset)</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email address" required>
                    <small style="margin-top: 5px; color: #6c757d;">Note: 2FA must be disabled to change or unbind your email.</small>
                </div>
                <input type="hidden" name="update_email" value="1">
                <div class="button-group">
                    <button type="submit"><span class="material-icons">save</span> Save Email</button>
                    <button type="button" id="unbindEmailBtn"><span class="material-icons">link_off</span> Unbind Email</button>
                </div>
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
        
        <div id="tab-info" class="tab-pane">
            <h3>Web App Information</h3>
            <div class="info-group">
                <h4>Application Details</h4>
                <div class="info-item">
                    <strong>Application Name:</strong>
                    <span>iCensus System</span>
                </div>
                <div class="info-item">
                    <strong>Version:</strong>
                    <span>1.0.0</span>
                </div>
                <div class="info-item">
                    <strong>Developer:</strong>
                    <span>iCensus Development Team</span>
                </div>
            </div>
            <div class="info-group">
                <h4>Key Technologies</h4>
                <div class="info-item">
                    <strong>Backend:</strong>
                    <span>PHP</span>
                </div>
                 <div class="info-item">
                    <strong>Frontend:</strong>
                    <span>JavaScript, HTML5, CSS3</span>
                </div>
                <div class="info-item">
                    <strong>Database:</strong>
                    <span>MySQL</span>
                </div>
                 <div class="info-item">
                    <strong>Email Service:</strong>
                    <span>PHPMailer v7.0.0</span>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<div id="unbindEmailModal" class="modal" style="display: none; align-items: center; justify-content: center; z-index: 2001;">
    <div class="modal-content">
        <span class="close" id="closeUnbindModal">&times;</span>
        <h3>Are you sure?</h3>
        <p>This action will permanently remove your email address from the account. You will lose the ability to use Two-Factor Authentication (2FA) and password reset via email.</p>
        <div class="modal-actions">
            <button id="cancelUnbindBtn" class="btn cancel">Cancel</button>
            <button id="confirmUnbindBtn" class="btn confirm">Confirm Unbind</button>
        </div>
    </div>
</div>


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

<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
// --- GLOBAL VARS ---
const BASE_URL = '<?= $base_url ?>';
const USER_ROLE = '<?= $_SESSION['user']['role_name'] ?>';
const IS_ADMIN = USER_ROLE === 'System Admin';
const COOLDOWN_DURATION = 60; // Must match PHP

document.addEventListener('DOMContentLoaded', () => {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            tabPanes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(`tab-${button.dataset.tab}`).classList.add('active');
        });
    });

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
        
        // --- NEW: Check for 2FA before allowing email *change* ---
        const twoFaSwitch = document.getElementById('twoFaSwitch');
        if (twoFaSwitch.checked) {
             showAjaxResult('You must disable Two-Factor Authentication before changing your email.', 'error');
             return;
        }
        // --- END NEW ---

        handleFormSubmit(this, BASE_URL + '/settings/process');
    });

    // --- MODIFIED: Unbind Email Button Logic ---
    const unbindEmailBtn = document.getElementById('unbindEmailBtn');
    const unbindEmailModal = document.getElementById('unbindEmailModal');
    const confirmUnbindBtn = document.getElementById('confirmUnbindBtn');
    const cancelUnbindBtn = document.getElementById('cancelUnbindBtn');
    const closeUnbindModal = document.getElementById('closeUnbindModal');

    if (unbindEmailBtn) {
        unbindEmailBtn.addEventListener('click', function() {
            const twoFaSwitch = document.getElementById('twoFaSwitch');
            if (twoFaSwitch.checked) {
                showAjaxResult('You must disable Two-Factor Authentication before removing your email.', 'error');
                return;
            }
            
            // Show the confirmation modal instead of confirm()
            if (unbindEmailModal) {
                unbindEmailModal.style.display = 'flex'; // Use flex for centering
            }
        });
    }

    // Close modal listeners
    if (cancelUnbindBtn) {
        cancelUnbindBtn.onclick = () => { unbindEmailModal.style.display = 'none'; };
    }
    if (closeUnbindModal) {
        closeUnbindModal.onclick = () => { unbindEmailModal.style.display = 'none'; };
    }
    window.addEventListener('click', (event) => {
        if (event.target === unbindEmailModal) {
            unbindEmailModal.style.display = 'none';
        }
    });

    // Handle the actual confirmation
    if (confirmUnbindBtn) {
        confirmUnbindBtn.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Removing...';
            if (unbindEmailModal) {
                unbindEmailModal.style.display = 'none'; // Hide modal
            }

            fetch(BASE_URL + '/settings/process', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ 'unbind_email': '1' })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showAjaxResult(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAjaxResult(result.message || 'An error occurred.', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Confirm Unbind';
                }
            })
            .catch(error => {
                showAjaxResult('A network error occurred.', 'error');
                btn.disabled = false;
                btn.textContent = 'Confirm Unbind';
            });
        });
    }
    // --- END MODIFIED ---


    // --- PASSWORD CHANGE LOGIC ---
    const passwordForm = document.getElementById('passwordForm');
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

    function resetPasswordForm() {
        passwordForm.reset();
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
        otpPasswordField.required = false;
    }

    verifyBtn.addEventListener('click', async () => {
        const current = currentPassword.value.trim();
        if(current === '') return;
        
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Checking...';
        
        const formData = new URLSearchParams({current_password: current});
        const response = await fetch(BASE_URL + '/settings/verify-password', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success' || result.status === 'otp_sent') {
            currentPasswordVerified = true;
            newPasswordFields.style.display = 'block';
            verifyMessage.textContent = result.message;
            verifyMessage.style.color = 'green';
            currentPassword.disabled = true;
            
            if (result.status === 'otp_sent') {
                otpRequirement.style.display = 'block';
                otpPasswordField.required = true;
                startPassCooldown(COOLDOWN_DURATION);
            } else {
                otpRequirement.style.display = 'none';
                otpPasswordField.required = false;
            }
        } else {
            verifyMessage.textContent = result.message || 'Incorrect password.';
            verifyMessage.style.color = 'red';
        }
        
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify Password';
    });

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

    password.addEventListener('input', () => {
        checkPasswordStrength();
        checkPasswordMatch();
    });
    confirmPassword.addEventListener('input', checkPasswordMatch);

    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            const firstInvalid = this.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
            return;
        }
        
        if (!currentPasswordVerified || password.value !== confirmPassword.value) {
            passwordOtpError.textContent = 'Please verify current password and ensure new passwords match.';
            passwordOtpError.style.color = 'red';
            return;
        }

        handleFormSubmit(this, BASE_URL + '/settings/process');
    });

    // --- 2FA TOGGLE LOGIC ---
    const twoFaSwitch = document.getElementById('twoFaSwitch');
    const twoFaLabel = document.getElementById('twoFaLabel');
    const emailInput = document.querySelector('#emailForm input[name="email"]'); // Get the input element itself

    if (twoFaSwitch) {
        // --- NEW: Disable switch if email is empty ---
        if (emailInput.value.trim().length === 0) {
            twoFaSwitch.disabled = true;
            twoFaSwitch.parentElement.title = 'Please add and save an email address to enable 2FA.';
        }
        // --- END NEW ---

        twoFaSwitch.addEventListener('change', async function() {
            const isChecked = this.checked;
            const targetTwoFA = isChecked ? 1 : 0;
            
            // Check email again, just in case
            if (targetTwoFA === 1 && emailInput.value.trim().length === 0) {
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
                    this.disabled = false; // Re-enable on simple success
                } else if (result.status === 'otp_required') {
                    this.checked = true; 
                    showOtpToggleModal(result.message, COOLDOWN_DURATION);
                    // Do not re-enable switch, wait for modal flow
                } else if (result.status === 'cooldown') {
                    this.checked = true; 
                    showOtpToggleModal(result.message, result.cooldown_remaining);
                    // Do not re-enable switch, wait for modal flow
                } else {
                    this.checked = !isChecked; // Revert change on error
                    showAjaxResult(result.message, 'error');
                    this.disabled = false; // Re-enable on error
                }
            } catch (error) {
                this.checked = !isChecked; // Revert change on network error
                showAjaxResult('A network error occurred. Please try again.', 'error');
                console.error('Error toggling 2FA:', error);
                this.disabled = false; // Re-enable on network error
            }
        });
    }

    // --- 2FA TOGGLE MODAL ---
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
    
    // --- Theme toggle logic ---
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
});
</script>

</body>
</html>