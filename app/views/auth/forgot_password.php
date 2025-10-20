<?php
// app/views/auth/forgot_password.php
$base_url = '/iCensus-ent/public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus - Forgot Password</title>
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
        <p class="hero-subtitle">Password Recovery</p>
    </div>
    <div class="divider-shadow"></div>
    <div class="right-side d-flex justify-content-center align-items-center">
        <div class="login-card">
            <div class="card-header text-center mb-3">
                <h1 class="hero-title">Forgot Password</h1>
                <p class="text-muted">Enter your email address to receive a password reset link.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-text" id="errorMessage" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="error-text" style="color: green; margin-bottom: 1rem;"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form id="forgotForm" method="POST" action="<?= $base_url ?>/password/forgot">
                <div class="input-wrapper mb-3">
                    <input type="email" name="email" id="emailInput" class="form-control" placeholder="Email Address" required autofocus>
                    <span class="material-icons input-icon">email</span>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2" id="sendLinkBtn">Send Reset Link</button>
                <div class="text-center">
                    <a href="<?= $base_url ?>/login" class="small text-decoration-none">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Particle animation script here (copy from login.php for visual consistency)
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particlesArray = [];
function resizeCanvas(){ canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; }
window.addEventListener('resize', () => { resizeCanvas(); initParticles(); });
class Particle {
    constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 3 + 1;
        this.speedX = Math.random() * 1 - 0.5;
        this.speedY = Math.random() * 1 - 0.5;
    }
    update() {
        this.x += this.speedX; this.y += this.speedY;
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
function initParticles(){ particlesArray = []; for(let i=0;i<60;i++){ particlesArray.push(new Particle()); } }
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