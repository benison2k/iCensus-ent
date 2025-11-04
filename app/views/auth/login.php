<?php
// app/views/auth/login.php
$base_url = '/iCensus-ent/public';
$error = $data['error'] ?? '';
$usernameValue = $data['usernameValue'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus Login</title>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/login.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">

<style>
/* --- OTP MODAL STYLES (IMPROVED LAYOUT) --- */
#otpModal .modal-content {
    max-width: 400px;
    padding: 30px;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

#otpModal h3 {
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--text-dark);
}

#otpModal p.text-muted {
    font-size: 0.95rem;
    margin-bottom: 25px;
}

/* --- START OTP 6-BOX INPUT STYLES (REPLACED) --- */
.otp-container {
    display: flex;
    justify-content: center;
    gap: 6px; /* Reduced gap */
    margin: 20px 0;
}
.otp-input {
    width: 38px;  /* Reduced width */
    height: 48px; /* Reduced height */
    font-size: 1.3rem; /* Reduced font size */
    font-weight: 600;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #fff;
    color: #333;
    -moz-appearance: textfield; 
}
.otp-input:focus {
    border-color: var(--gradient-start);
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
    outline: none;
}
.otp-input::-webkit-outer-spin-button,
.otp-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
/* --- END OTP 6-BOX INPUT STYLES --- */


#otpVerifyBtn {
    background: linear-gradient(135deg, #0d6efd, #6c63ff);
    color: #fff;
    font-weight: 600;
    margin-top: 15px !important;
}

#resendOtpBtn {
    display: block;
    margin-top: 15px;
    font-size: 0.85rem;
    color: #6c757d !important;
    transition: color 0.2s;
    text-decoration: underline !important;
}

#resendOtpBtn:hover {
    color: #0d6efd !important;
}

#cooldownTimer {
    font-size: 0.85rem;
    font-weight: 500;
    color: #0d6efd !important; /* Blue for the active countdown */
}

/* Dark Mode Overrides */
body.dark-mode #otpModal .modal-content {
    background: #2C3E50;
}

body.dark-mode #otpModal h3 {
    color: #ffffff;
}

body.dark-mode #otpModal p.text-muted {
    color: #bbbbbb;
}

body.dark-mode .otp-input {
    background: #1e1e2f;
    color: #ffffff;
    border-color: #555;
}

body.dark-mode #resendOtpBtn {
    color: #9fa8da !important;
}
</style>

</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="<?= $base_url ?>/home" class="home-link" title="Back to Home">
            <span class="material-icons">home</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="<?= $base_url ?>/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">Accurate. Fast. Reliable.</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Sign in</h1>
                <p class="text-muted">Please enter your credentials</p>
            </div>

            <?php
                if (isset($_SESSION['timeout_message'])) {
                    echo '<div class="error-text" id="timeoutMessage" style="color: #0d6efd; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['timeout_message']) . '</div>';
                    unset($_SESSION['timeout_message']);
                }
            ?>

            <form id="loginForm" method="POST" action="<?= $base_url ?>/login">
                <div class="input-wrapper mb-3">
                    <input type="text" name="username" id="usernameInput" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Username" value="<?= $usernameValue ?>" autofocus>
                    <span class="material-icons input-icon">person</span>
                </div>
                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="passwordField" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Password">
                    <span class="material-icons password-toggle" id="togglePassword">visibility_off</span>
                </div>
                <div class="error-text" id="loginError" style="margin-bottom: 1rem;"><?= $error ?></div>
                <button type="submit" class="btn btn-primary w-100 mb-2" id="loginBtn">Login</button>
                <div class="text-center">
                    <a href="<?= $base_url ?>/password/forgot" class="small text-decoration-none">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="otpModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeOtpModal">&times;</span>
        <h3 style="margin-top: 0;">Two-Factor Authentication</h3>
        <p class="text-muted">A 6-digit code has been sent to your registered email.</p>
        
        <form id="otpForm" action="<?= $base_url ?>/verify-otp" method="POST" style="margin-top: 1.5rem; margin-bottom: 1rem;">
            
            <div class="otp-container" id="otpLoginContainer">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>
            <input type="hidden" name="otp" id="otpInput" required>
            <div class="error-text" id="otpError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpVerifyBtn">Verify Code</button>
        </form>
        
        <a href="#" id="resendOtpBtn" class="small text-decoration-none" style="display: none;">Resend Code</a>
        <span id="cooldownTimer" class="small text-muted" style="margin-top: 5px; display: none;"></span>
    </div>
</div>
<script>
    const BASE_URL = '<?= $base_url ?>';
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginError = document.getElementById('loginError');
    const otpModal = document.getElementById('otpModal');
    const otpForm = document.getElementById('otpForm');
    const otpInput = document.getElementById('otpInput'); // This is now the HIDDEN input
    const otpError = document.getElementById('otpError');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const cooldownTimer = document.getElementById('cooldownTimer');
    const closeOtpModal = document.getElementById('closeOtpModal');
    const passwordField = document.getElementById('passwordField');
    
    let timerInterval = null;
    const COOLDOWN_DURATION = 60; 

    // --- START OTP 6-BOX LOGIC ---
    const otpContainer = document.getElementById('otpLoginContainer'); // <-- Adapted ID
    if (otpContainer) {
        const otpInputs = otpContainer.querySelectorAll('.otp-input');
        const hiddenOtpInput = document.getElementById('otpInput'); // <-- This is our hidden input

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


    // --- Countdown Logic ---
    function startCountdown(duration) {
        clearInterval(timerInterval);
        let timeRemaining = duration;
        
        // Hide button, show timer
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
    
    function showModal(error = '') {
        otpError.textContent = error;
        // Clear all 6 boxes
        const otpInputs = otpContainer.querySelectorAll('.otp-input');
        otpInputs.forEach(input => input.value = '');
        otpInput.value = ''; // Clear hidden input
        otpModal.style.display = 'flex';
        if (otpInputs.length > 0) otpInputs[0].focus(); // Focus first box
        
        // When modal is shown, start the countdown immediately
        startCountdown(COOLDOWN_DURATION); 
    }

    // --- Utility Functions ---
    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePassword.textContent = type === 'password' ? 'visibility_off' : 'visibility';
        });
    }
    
    // --- Event Listeners for OTP Modal ---
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
        
        // Disable and show 'Sending...' state
        resendOtpBtn.style.display = 'none';
        otpError.textContent = 'Sending...';
        otpError.style.color = '#0d6efd';
        
        try {
            const response = await fetch(BASE_URL + '/resend-otp');
            const result = await response.json();
            
            if (result.status === 'success') {
                otpError.textContent = result.message;
                otpError.style.color = 'green';
                // Start the full countdown on success
                startCountdown(COOLDOWN_DURATION); 
                
            } else if (result.status === 'cooldown') { // Handle server-side cooldown response
                otpError.textContent = result.message;
                otpError.style.color = 'red';
                // Start the countdown from the time returned by the server
                startCountdown(result.cooldown_remaining); 
                
            } else {
                otpError.textContent = result.message;
                otpError.style.color = 'red';
                // Re-show button if general error
                resendOtpBtn.style.display = 'block';
            }
        } catch (error) {
            otpError.textContent = 'Network error while trying to resend.';
            otpError.style.color = 'red';
            resendOtpBtn.style.display = 'block';
            console.error(error);
        }
    });

    // --- 1. Main Login Form Submission (AJAX) ---
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
                // If 2FA is required, initialize the countdown (60 seconds)
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

    // --- 2. OTP Modal Form Submission (AJAX) ---
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
                clearInterval(timerInterval); // Stop timer on successful verification
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

    // --- Particle Animation Script (omitted for brevity) ---
    const canvas = document.getElementById('particleCanvas');
    const ctx = canvas.getContext('2d');
    let particlesArray = [];

    function resizeCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }

    window.addEventListener('resize', () => {
        resizeCanvas();
        initParticles();
    });

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 3 + 1;
            this.speedX = Math.random() * 1 - 0.5;
            this.speedY = Math.random() * 1 - 0.5;
        }
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
            if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
        }
        draw() {
            ctx.fillStyle = 'rgba(255,255,255,0.4)';
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function initParticles() {
        particlesArray = [];
        for (let i = 0; i < 60; i++) {
            particlesArray.push(new Particle());
        }
    }

    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particlesArray.forEach(p => { p.update(); p.draw(); });
        requestAnimationFrame(animateParticles);
    }

    resizeCanvas();
    initParticles();
    animateParticles();
</script>

</body>
</html>