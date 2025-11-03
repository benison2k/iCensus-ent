// Handles all logic for the Security tab
export function initSecurityTab(helpers) {
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;

    // --- Email Form ---
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const twoFaSwitch = document.getElementById('twoFaSwitch');
            // This check remains valid, as 2FA could be enabled
            if (twoFaSwitch && twoFaSwitch.checked) { 
                showAjaxResult('You must disable Two-Factor Authentication before changing your email.', 'error');
                return;
            }
            
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;

            await handleGenericSubmit(this, BASE_URL + '/settings/email', showAjaxResult);
            
            btn.disabled = false;
            // Reload the page to show/hide the 2FA section if email was added/removed
            setTimeout(() => window.location.reload(), 1500); 
        });
    }

    // --- Password Form ---
    initPasswordForm(helpers);

    // --- 2FA Toggle ---
    init2FAToggle(helpers);

    // --- Unbind Email ---
    initUnbindEmail(helpers);
}

// --- Password Sub-Module ---
function initPasswordForm(helpers) {
    // (This entire function remains unchanged from the previous version)
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;

    const passwordForm = document.getElementById('passwordForm');
    if (!passwordForm) return;

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
        if (otpPasswordField) otpPasswordField.required = false; // Check if it exists
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
                if (otpPasswordField) otpPasswordField.required = false; // Check if it exists
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
    
    if (resendOtpBtnPass) { // Check if it exists
        resendOtpBtnPass.addEventListener('click', async (e) => {
            e.preventDefault();
            passwordOtpError.textContent = 'Sending...';
            passwordOtpError.style.color = '#0d6efd';
            
            try {
                const response = await fetch(BASE_URL + '/settings/resendPasswordChangeOtp', { method: 'POST' });
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
    }

    function checkPasswordStrength() {
        const pass = password.value;
        let score = 0;
        if (pass.length > 8) score++;
        if (pass.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) score++;
        if (pass.match(/([0-9])/)) score++;
        if (pass.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) score++;
        if (pass.length === 0) { strengthBar.className = ''; strengthText.textContent = ''; return; }
        if (score < 2) { strengthBar.className = 'weak'; strengthText.textContent = 'Weak'; }
        else if (score < 4) { strengthBar.className = 'medium'; strengthText.textContent = 'Medium'; }
        else { strengthBar.className = 'strong'; strengthText.textContent = 'Strong'; }
    }

    function checkPasswordMatch() {
        const passwordValid = password.value.length >= 6 && password.value === confirmPassword.value;
        if (password.value === '' || confirmPassword.value === '') {
            matchIcon.innerHTML = ''; passwordSubmit.disabled = true; return;
        }
        if(passwordValid) {
            matchIcon.innerHTML = '<span class="material-icons" style="color:green;">check_circle</span>';
            passwordSubmit.disabled = false;
        } else {
            matchIcon.innerHTML = '<span class="material-icons" style="color:red;">cancel</span>';
            passwordSubmit.disabled = true;
        }
    }

    password.addEventListener('input', () => { checkPasswordStrength(); checkPasswordMatch(); });
    confirmPassword.addEventListener('input', checkPasswordMatch);

    passwordForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            this.querySelector(':invalid')?.focus();
            return;
        }
        
        if (!currentPasswordVerified || password.value !== confirmPassword.value) {
            passwordOtpError.textContent = 'Please verify current password and ensure new passwords match.';
            passwordOtpError.style.color = 'red';
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        const result = await handleGenericSubmit(this, BASE_URL + '/settings/password', showAjaxResult);
        btn.disabled = false;

        if (result.status === 'success') {
            resetPasswordForm();
        }
    });
}

// --- 2FA Toggle Sub-Module ---
function init2FAToggle(helpers) {
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;
    
    const twoFaSwitch = document.getElementById('twoFaSwitch');
    const twoFaLabel = document.getElementById('twoFaLabel');
    const emailInput = document.getElementById('email');

    // This section is conditional in PHP, so it might not exist.
    if (!twoFaSwitch) return; 

    // **REMOVED** The check to disable the switch is no longer needed
    // as the PHP view handles this rendering logic.
    // if (emailInput.value.trim().length === 0) {
    //     ...
    // }

    twoFaSwitch.addEventListener('change', async function() {
        const isChecked = this.checked;
        const targetTwoFA = isChecked ? 1 : 0;
        
        // This check is still good as a client-side safeguard
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
                this.disabled = false;
            } else if (result.status === 'otp_required') {
                this.checked = true; 
                initOtpModal('otpToggleModal', {
                    ...helpers, 
                    resendUrl: BASE_URL + '/settings/toggleTwoFA',
                    resendBody: new URLSearchParams({ target_two_fa: 0 }),
                    onCloseReload: true
                }, result.message, COOLDOWN_DURATION);
            } else if (result.status === 'cooldown') {
                this.checked = true; 
                initOtpModal('otpToggleModal', {
                    ...helpers, 
                    resendUrl: BASE_URL + '/settings/toggleTwoFA',
                    resendBody: new URLSearchParams({ target_two_fa: 0 }),
                    onCloseReload: true
                }, result.message, result.cooldown_remaining);
            } else {
                this.checked = !isChecked;
                showAjaxResult(result.message, 'error');
                this.disabled = false;
            }
        } catch (error) {
            this.checked = !isChecked;
            showAjaxResult('A network error occurred. Please try again.', 'error');
            this.disabled = false;
        }
    });
}

// --- Unbind Email Sub-Module ---
function initUnbindEmail(helpers) {
    // (This entire function remains unchanged from the previous version)
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;

    const unbindEmailBtn = document.getElementById('unbindEmailBtn');
    if (!unbindEmailBtn) return;

    unbindEmailBtn.addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;

        const twoFaSwitch = document.getElementById('twoFaSwitch');
        if (twoFaSwitch && twoFaSwitch.checked) { // Check if switch exists and is checked
            showAjaxResult('You must disable Two-Factor Authentication before removing your email.', 'error');
            btn.disabled = false;
            return;
        }
        
        try {
            const response = await fetch(BASE_URL + '/settings/request-unbind-otp', { method: 'POST' });
            const result = await response.json();

            if (result.status === 'success') {
                initOtpModal('otpUnbindModal', {
                    ...helpers,
                    resendUrl: BASE_URL + '/settings/request-unbind-otp',
                    resendBody: new URLSearchParams(),
                    onSuccessReload: true
                }, result.message, COOLDOWN_DURATION);
            } else if (result.status === 'cooldown') {
                initOtpModal('otpUnbindModal', {
                    ...helpers,
                    resendUrl: BASE_URL + '/settings/request-unbind-otp',
                    resendBody: new URLSearchParams(),
                    onSuccessReload: true
                }, result.message, result.cooldown_remaining);
            } else {
                showAjaxResult(result.message || 'An error occurred.', 'error');
            }
        } catch (error) {
             showAjaxResult('A network error occurred.', 'error');
        } finally {
            btn.disabled = false;
        }
    });
}

// --- Generic OTP Modal Handler ---
let otpCooldownInterval = null;
function initOtpModal(modalId, helpers, message, cooldown) {
    // (This entire function remains unchanged from the previous version)
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION, resendUrl, resendBody, onSuccessReload, onCloseReload } = helpers;
    
    const modal = document.getElementById(modalId);
    if (!modal) return; // Add check in case modal doesn't exist
    const form = modal.querySelector('form');
    const input = modal.querySelector('input[name="otp"]');
    const errorEl = modal.querySelector('.error-text');
    const resendBtn = modal.querySelector('a[id^="resend"]');
    const cooldownTimer = modal.querySelector('span[id^="cooldown"]');
    const closeBtn = modal.querySelector('.close');

    function startCountdown(duration) {
        clearInterval(otpCooldownInterval);
        let timeRemaining = duration;
        
        resendBtn.style.display = 'none';
        cooldownTimer.style.display = 'block';

        otpCooldownInterval = setInterval(() => {
            let seconds = timeRemaining % 60;
            let display = seconds < 10 ? "0" + seconds : seconds;
            
            cooldownTimer.textContent = `Resend available in ${display}s`;
            
            if (timeRemaining <= 0) {
                clearInterval(otpCooldownInterval);
                resendBtn.style.display = 'block';
                cooldownTimer.style.display = 'none';
                errorEl.textContent = 'The cooldown has expired. You may resend the code.';
                errorEl.style.color = '#0d6efd';
            }
            timeRemaining--;
        }, 1000);
    }

    errorEl.textContent = message;
    errorEl.style.color = '#0d6efd';
    input.value = '';
    modal.style.display = 'flex';
    input.focus();
    startCountdown(cooldown);

    if (!modal.dataset.initialized) {
        modal.dataset.initialized = 'true';
        
        closeBtn.onclick = () => {
            clearInterval(otpCooldownInterval);
            modal.style.display = 'none';
            if (onCloseReload) window.location.reload();
        };
        
        resendBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            errorEl.textContent = 'Sending...';
            errorEl.style.color = 'orange';
            resendBtn.style.display = 'none';
            
            try {
                const response = await fetch(resendUrl, {
                    method: 'POST',
                    body: resendBody,
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });
                const result = await response.json();
                
                if (result.status === 'otp_required' || result.status === 'success') {
                    errorEl.textContent = 'A new code has been sent. Check your email.';
                    errorEl.style.color = 'green';
                    startCountdown(COOLDOWN_DURATION); 
                } else if (result.status === 'cooldown') {
                    errorEl.textContent = result.message;
                    errorEl.style.color = 'red';
                    startCountdown(result.cooldown_remaining); 
                } else {
                    errorEl.textContent = result.message;
                    errorEl.style.color = 'red';
                    resendBtn.style.display = 'block';
                }
            } catch (error) {
                errorEl.textContent = 'Network error while trying to resend.';
                errorEl.style.color = 'red';
                resendBtn.style.display = 'block';
            }
        });
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const verifyBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = verifyBtn.textContent;
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Verifying...';
            errorEl.textContent = '';
            
            try {
                const formData = new URLSearchParams(new FormData(form));
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    clearInterval(otpCooldownInterval);
                    showAjaxResult(result.message, 'success');
                    modal.style.display = 'none';
                    if (onSuccessReload) {
                        setTimeout(() => window.location.reload(), 500);
                    }
                } else {
                    errorEl.textContent = result.message;
                    errorEl.style.color = 'red';
                }
            } catch (error) {
                errorEl.textContent = 'Network error during verification.';
                errorEl.style.color = 'red';
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.textContent = originalBtnText;
            }
        });
    }
}

// --- Generic Form Handler ---
async function handleGenericSubmit(form, url, callback) {
    try {
        const formData = new URLSearchParams(new FormData(form));
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (response.ok) {
            callback(result.message, 'success');
        } else {
            callback(result.message || 'An error occurred.', 'error');
        }
        return result;
    } catch (error) {
        callback('A network error occurred. Please try again.', 'error');
        return {status: 'error', message: 'Network error'};
    }
}