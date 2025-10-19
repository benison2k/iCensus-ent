<?php
// app/views/auth/verify_otp.php
$base_url = '/iCensus-ent/public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus - Verify OTP</title>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/login.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="<?= $base_url ?>/login" class="home-link" title="Back to Login">
            <span class="material-icons">arrow_back</span>
        </a>
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <canvas id="particleCanvas"></canvas>
        <img src="<?= $base_url ?>/assets/img/iCensusLogo.png" alt="iCensus Logo" class="hero-logo mb-3">
        <p class="hero-subtitle">Two-Factor Authentication</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Enter OTP Code</h1>
                <p class="text-muted">A code has been sent to your registered email address.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-text" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= $base_url ?>/verify-otp" novalidate>
                <div class="input-wrapper mb-3">
                    <input type="text" name="otp" class="form-control" placeholder="6-digit OTP" required autofocus maxlength="6" pattern="\d{6}" inputmode="numeric">
                    <span class="material-icons input-icon">lock_open</span>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-4">Verify Code</button>
            </form>
            
            <div class="text-center">
                <a href="<?= $base_url ?>/resend-otp" class="small text-decoration-none text-muted">Resend OTP</a>
            </div>
        </div>
    </div>
</div>

<script>
// Particle animation (from login.php)
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