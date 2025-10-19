<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus Login</title>
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/login.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="/iCensus-ent/public/home" class="home-link" title="Back to Home">
            <span class="material-icons">home</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="/iCensus-ent/public/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">Accurate. Fast. Reliable.</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Sign in</h1>
                <p class="text-muted">Please enter your credentials</p>
            </div>

            <form method="POST" action="/iCensus-ent/public/login" novalidate>
                <div class="input-wrapper mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" value="" autofocus>
                    <span class="material-icons input-icon">person</span>
                </div>
                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                    <span class="material-icons password-toggle" id="togglePassword">visibility_off</span>
                </div>
                <div class="error-text" style="display: none;"></div>
                <button class="btn btn-primary w-100 mb-2">Login</button>
                <div class="text-center">
                    <a href="#" class="small text-decoration-none">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="otpModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
    <div class="modal-content" style="background: #fff; padding: 25px; border-radius: 12px; width: 350px; text-align: center;">
        <h3 style="margin-top: 0;">Enter OTP</h3>
        <p>An OTP has been sent to your registered email. Please enter it below.</p>
        <form id="otpForm">
            <input type="text" id="otpInput" name="otp" style="width: 100%; padding: 10px; font-size: 1.2rem; text-align: center; border: 1px solid #ccc; border-radius: 8px;" maxlength="6" required>
            <p id="otpError" style="color: red; min-height: 20px;"></p>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>
    </div>
</div>

<div id="messageOverlay" style="display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; color: white; text-align: center;">
    <div>
        <h2 id="messageText"></h2>
    </div>
</div>


<script>
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');
togglePassword.addEventListener('click', () => {
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    togglePassword.textContent = type === 'password' ? 'visibility_off' : 'visibility';
});

const loginForm = document.querySelector('form[action="/iCensus-ent/public/login"]');
const errorText = document.querySelector('.error-text');
const messageOverlay = document.getElementById('messageOverlay');
const messageText = document.getElementById('messageText');
const otpModal = document.getElementById('otpModal');
const otpForm = document.getElementById('otpForm');
const otpError = document.getElementById('otpError');

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorText.style.display = 'none';
    const formData = new FormData(loginForm);
    
    try {
        const response = await fetch(loginForm.action, {
            method: 'POST',
            body: new URLSearchParams(formData)
        });

        const result = await response.json();

        if (response.ok && result.status === 'otp_required') {
            messageText.textContent = 'Sending OTP...';
            messageOverlay.style.display = 'flex';

            setTimeout(() => {
                messageOverlay.style.display = 'none';
                otpModal.style.display = 'flex';
                document.getElementById('otpInput').focus();
            }, 2000);

        } else if (response.ok && result.status === 'success') {
            window.location.href = result.redirect;
        } else {
            errorText.textContent = result.message || 'An unknown error occurred.';
            errorText.style.display = 'block';
        }
    } catch (err) {
        errorText.textContent = 'A network error occurred. Please try again.';
        errorText.style.display = 'block';
    }
});

otpForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const otpData = new FormData(otpForm);
    otpError.textContent = '';

    try {
        const response = await fetch('/iCensus-ent/public/verify-otp', {
            method: 'POST',
            body: new URLSearchParams(otpData)
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            otpModal.style.display = 'none';
            messageText.innerHTML = 'Success! <br> Redirecting to dashboard...';
            messageOverlay.style.display = 'flex';

            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1500);
        } else {
            otpError.textContent = result.message;
        }
    } catch (err) {
        otpError.textContent = 'A network error occurred.';
    }
});

const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particlesArray = [];

function resizeCanvas(){
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
        if(this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if(this.y < 0 || this.y > canvas.height) this.speedY *= -1;
    }
    draw() {
        ctx.fillStyle = 'rgba(255,255,255,0.4)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
}

function initParticles(){
    particlesArray = [];
    for(let i=0;i<60;i++){
        particlesArray.push(new Particle());
    }
}

function animateParticles(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    particlesArray.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animateParticles);
}

resizeCanvas();
initParticles();
animateParticles();
</script>

</body>
</html>