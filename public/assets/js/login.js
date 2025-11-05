// public/assets/js/login.js

document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENT SELECTORS ---
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginError = document.getElementById('loginError');
    const otpModal = document.getElementById('otpModal');
    const otpForm = document.getElementById('otpForm');
    const otpInput = document.getElementById('otpInput'); // Hidden input
    const otpError = document.getElementById('otpError');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const cooldownTimer = document.getElementById('cooldownTimer');
    const closeOtpModal = document.getElementById('closeOtpModal');
    const passwordField = document.getElementById('passwordField');
    const togglePassword = document.getElementById('togglePassword');
    const otpContainer = document.getElementById('otpLoginContainer');

    let timerInterval = null;
    const COOLDOWN_DURATION = 60;

    // --- OTP 6-BOX INPUT LOGIC ---
    if (otpContainer) {
        const otpInputs = otpContainer.querySelectorAll('.otp-input');
        const hiddenOtpInput = document.getElementById('otpInput');

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

    // --- COUNTDOWN LOGIC ---
    function startCountdown(duration) {
        clearInterval(timerInterval);
        let timeRemaining = duration;
        
        resendOtpBtn.style.display = 'none';
        cooldownTimer.style.display = 'block';
        cooldownTimer.style.color = '#0d6efd';

        timerInterval = setInterval(() => {
            let seconds = timeRemaining % 60;
            let display = seconds < 10 ? "0" + seconds : seconds;
            
            cooldownTimer.textContent = `Resend available in ${display}s`;
            
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                resendOtpBtn.style.display = 'block';
                cooldownTimer.style.display = 'none';
                cooldownTimer.textContent = '';
                otpError.textContent = 'The cooldown has expired. You may resend the code.';
                otpError.style.color = '#0d6efd';
            }
            timeRemaining--;
        }, 1000);
    }
    
    // --- MODAL & UTILITY FUNCTIONS ---
    function showModal(error = '') {
        otpError.textContent = error;
        // Clear all 6 boxes
        if (otpContainer) {
            const otpInputs = otpContainer.querySelectorAll('.otp-input');
            otpInputs.forEach(input => input.value = '');
            if(otpInputs.length > 0) otpInputs[0].focus();
        }
        otpInput.value = ''; // Clear hidden input
        otpModal.style.display = 'flex';
        
        startCountdown(COOLDOWN_DURATION); 
    }

    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePassword.textContent = type === 'password' ? 'visibility_off' : 'visibility';
        });
    }
    
    // --- EVENT LISTENERS ---
    closeOtpModal.onclick = () => {
        clearInterval(timerInterval);
        otpModal.style.display = 'none';
    };
    window.onclick = (event) => {
        if (event.target === otpModal) {
            clearInterval(timerInterval);
            otpModal.style.display = 'none';
        }
    };

    resendOtpBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        resendOtpBtn.style.display = 'none';
        otpError.textContent = 'Sending...';
        otpError.style.color = '#0d6efd';
        
        try {
            // BASE_URL is defined in the <script> tag in login.php
            const response = await fetch(BASE_URL + '/resend-otp');
            const result = await response.json();
            
            if (result.status === 'success') {
                otpError.textContent = result.message;
                otpError.style.color = 'green';
                startCountdown(COOLDOWN_DURATION); 
            } else if (result.status === 'cooldown') {
                otpError.textContent = result.message;
                otpError.style.color = 'red';
                startCountdown(result.cooldown_remaining); 
            } else {
                otpError.textContent = result.message;
                otpError.style.color = 'red';
                resendOtpBtn.style.display = 'block';
            }
        } catch (error) {
            otpError.textContent = 'Network error while trying to resend.';
            otpError.style.color = 'red';
            resendOtpBtn.style.display = 'block';
            console.error(error);
        }
    });

    // Main Login Form Submission (AJAX)
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        loginBtn.disabled = true;
        loginBtn.textContent = 'Logging in...';
        loginError.textContent = '';

        try {
            const formData = new URLSearchParams(new FormData(loginForm));
            
            const response = await fetch(loginForm.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            const result = await response.json();
            
            if (result.status === 'success') {
                window.location.href = result.redirect;
            } else if (result.status === '2fa_required') {
                showModal(); 
            } else {
                loginError.textContent = result.message;
                loginError.style.color = 'red';
            }
        } catch (error) {
            loginError.textContent = 'Network error. Could not connect to the server.';
            loginError.style.color = 'red';
            console.error(error);
        } finally {
            loginBtn.disabled = false;
            loginBtn.textContent = 'Login';
        }
    });

    // OTP Modal Form Submission (AJAX)
    otpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const verifyBtn = document.getElementById('otpVerifyBtn');
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Verifying...';
        otpError.textContent = '';
        
        try {
            const formData = new URLSearchParams(new FormData(otpForm));
            
            const response = await fetch(otpForm.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                clearInterval(timerInterval);
                window.location.href = result.redirect;
            } else {
                otpError.textContent = result.message;
                otpError.style.color = 'red';
            }
        } catch (error) {
            otpError.textContent = 'Network error. Could not complete verification.';
            otpError.style.color = 'red';
            console.error(error);
        } finally {
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify Code';
        }
    });
});