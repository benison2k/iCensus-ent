// --- NEW Helper function to toggle button loading state ---
/**
 * Toggles the loading state of a button.
 * @param {HTMLButtonElement} button The button element
 * @param {boolean} isLoading Whether to show the loading state
 * @param {string} [loadingText="Sending..."] Optional text to show while loading
 */
function setButtonLoading(button, isLoading, loadingText = "Sending...") {
    if (!button) return;
    
    const btnIcon = button.querySelector('.btn-icon');
    const btnText = button.querySelector('.btn-text');
    const btnSpinner = button.querySelector('.btn-spinner');

    // Store original text if it's not already stored
    if (isLoading && !button.dataset.originalText) {
         if (btnText) button.dataset.originalText = btnText.textContent;
    }

    button.disabled = isLoading;

    if (isLoading) {
        if (btnIcon) btnIcon.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline-block';
        if (btnText) btnText.textContent = loadingText;
    } else {
        if (btnIcon) btnIcon.style.display = 'inline-block';
        if (btnSpinner) btnSpinner.style.display = 'none';
        if (btnText && button.dataset.originalText) {
            btnText.textContent = button.dataset.originalText;
        }
    }
}


// Handles all logic for the Security tab
export function initSecurityTab(helpers) {
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;

    // --- Email Form ---
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const twoFaSwitch = document.getElementById('twoFaSwitch');
            if (twoFaSwitch && twoFaSwitch.checked) { 
                showAjaxResult('You must disable Two-Factor Authentication before changing your email.', 'error');
                return;
            }
            
            const btn = this.querySelector('button[type="submit"]');
            setButtonLoading(btn, true, 'Sending OTP...');

            let otpModalOpened = false; // Flag
            try {
                const formData = new URLSearchParams(new FormData(this));
                const response = await fetch(BASE_URL + '/settings/email', { // This now points to requestBindEmailOtp
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (response.ok) {
                    if (result.status === 'success') {
                        // This means the email was the same, no change
                        showAjaxResult(result.message, 'success');
                    } else if (result.status === 'otp_required') {
                        otpModalOpened = true; // Set flag
                        // Trigger the NEW bind modal
                        initOtpModal('otpBindModal', {
                            ...helpers,
                            resendUrl: BASE_URL + '/settings/resend-bind-otp',
                            resendBody: new URLSearchParams(), // No body needed, email is in session
                            onSuccessReload: true, // Reload on success to update email value
                            onCloseCallback: () => setButtonLoading(btn, false)
                        }, result.message, COOLDOWN_DURATION);
                    }
                } else {
                    showAjaxResult(result.message || 'An error occurred.', 'error');
                }
            } catch (error) {
                showAjaxResult('A network error occurred. Please try again.', 'error');
            } finally {
                if (!otpModalOpened) {
                    setButtonLoading(btn, false);
                }
            }
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
        if (resendOtpBtnPass) resendOtpBtnPass.style.display = 'none'; 
        if (passCooldownTimer) passCooldownTimer.style.display = 'none'; 
        if (passCooldownTimer) passCooldownTimer.textContent = ''; 
        strengthBar.className = '';
        strengthText.textContent = '';
        if (otpPasswordField) otpPasswordField.required = false; 
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
                if (otpPasswordField) otpPasswordField.required = false; 
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
    
    if (resendOtpBtnPass) { 
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

    if (!twoFaSwitch) return; 

    twoFaSwitch.addEventListener('change', async function() {
        const isChecked = this.checked;
        const targetTwoFA = isChecked ? 1 : 0;
        
        if (targetTwoFA === 1 && emailInput.value.trim().length === 0) {
            this.checked = false;
            showAjaxResult('Cannot enable 2FA: Please save a valid email address first.', 'error');
            return;
        }

        // --- NEW: Retrieve CSRF token from the form ---
        const form = document.getElementById('twoFaForm');
        let csrfToken = '';
        if (form) {
            const tokenInput = form.querySelector('input[name="csrf_token"]');
            if (tokenInput) {
                csrfToken = tokenInput.value;
            }
        }

        this.disabled = true;
        
        // --- NEW: Include token in params ---
        const data = new URLSearchParams({ 
            target_two_fa: targetTwoFA,
            csrf_token: csrfToken
        });
        
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
                    resendBody: new URLSearchParams({ target_two_fa: 0, csrf_token: csrfToken }), // Re-send token on retry
                    onCloseReload: true
                }, result.message, COOLDOWN_DURATION);
            } else if (result.status === 'cooldown') {
                this.checked = true; 
                initOtpModal('otpToggleModal', {
                    ...helpers, 
                    resendUrl: BASE_URL + '/settings/toggleTwoFA',
                    resendBody: new URLSearchParams({ target_two_fa: 0, csrf_token: csrfToken }), // Re-send token on retry
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
    const { showAjaxResult, BASE_URL, COOLDOWN_DURATION } = helpers;

    const unbindEmailBtn = document.getElementById('unbindEmailBtn');
    if (!unbindEmailBtn) return;

    unbindEmailBtn.addEventListener('click', async function() {
        const btn = this;
        setButtonLoading(btn, true, 'Sending OTP...');

        const twoFaSwitch = document.getElementById('twoFaSwitch');
        if (twoFaSwitch && twoFaSwitch.checked) { 
            showAjaxResult('You must disable Two-Factor Authentication before removing your email.', 'error');
            setButtonLoading(btn, false);
            return;
        }
        
        let otpModalOpened = false; // Flag
        try {
            const response = await fetch(BASE_URL + '/settings/request-unbind-otp', { method: 'POST' });
            const result = await response.json();

            if (result.status === 'success' || result.status === 'cooldown') {
                otpModalOpened = true; // Set flag
                initOtpModal('otpUnbindModal', {
                    ...helpers,
                    resendUrl: BASE_URL + '/settings/request-unbind-otp',
                    resendBody: new URLSearchParams(),
                    onSuccessReload: true,
                    onCloseCallback: () => setButtonLoading(btn, false)
                }, result.message, result.cooldown_remaining || COOLDOWN_DURATION);
            } else {
                showAjaxResult(result.message || 'An error occurred.', 'error');
            }
        } catch (error) {
             showAjaxResult('A network error occurred.', 'error');
        } finally {
            if (!otpModalOpened) {
                setButtonLoading(btn, false);
            }
        }
    });
}

// --- Generic OTP Modal Handler ---
let otpCooldownInterval = null;
function initOtpModal(modalId, helpers, message, cooldown) {
    const { 
        showAjaxResult, BASE_URL, COOLDOWN_DURATION, 
        resendUrl, resendBody, 
        onSuccessReload, onCloseReload,
        onCloseCallback
    } = helpers;
    
    const modal = document.getElementById(modalId);
    if (!modal) return;
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

    // --- START OTP 6-BOX LOGIC ---
    // Check if this modal is the one we are fixing (otpBindModal)
    if (modalId === 'otpBindModal') {
        const otpContainer = document.getElementById('otpBindContainer');
        const otpInputs = otpContainer.querySelectorAll('.otp-input');
        const hiddenOtpInput = document.getElementById('otpBindInput'); // This is our hidden input

        // Focus the first box
        if(otpInputs.length > 0) otpInputs[0].focus();

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Handle paste
                if (value.length > 1) {
                    value.split('').forEach((char, i) => {
                        if (index + i < otpInputs.length) {
                            otpInputs[index + i].value = char;
                        }
                    });
                    const lastPastedIndex = Math.min(index + value.length - 1, otpInputs.length - 1);
                    otpInputs[lastPastedIndex].focus();
                } 
                // Handle single digit
                else if (value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                
                // Combine all values into the hidden input for form submission
                hiddenOtpInput.value = Array.from(otpInputs).map(inp => inp.value).join('');
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === "Backspace" && input.value === "" && index > 0) {
                    otpInputs[index - 1].focus();
                }
                // Also update hidden input on backspace
                setTimeout(() => {
                        hiddenOtpInput.value = Array.from(otpInputs).map(inp => inp.value).join('');
                }, 0);
            });
        });
    }
    // --- END OTP 6-BOX LOGIC ---

    if (!modal.dataset.initialized) {
        modal.dataset.initialized = 'true';
        
        closeBtn.onclick = () => {
            clearInterval(otpCooldownInterval);
            modal.style.display = 'none';
            if (onCloseCallback) onCloseCallback();
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