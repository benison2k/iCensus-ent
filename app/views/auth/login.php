<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iCensus Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/login.css">
</head>
<body>

<div class="split-screen">
    <div class="left-side position-relative d-flex flex-column justify-content-center align-items-center text-center overflow-hidden">
        <a href="/iCensus-ent/public/" class="home-link" title="Back to Home">
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

            <?php
                // Display and then clear the timeout message if it exists
                if (isset($_SESSION['timeout_message'])) {
                    echo '<div class="error-text" style="color: #0d6efd; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['timeout_message']) . '</div>';
                    unset($_SESSION['timeout_message']);
                }
            ?>

            <form method="POST" action="/iCensus-ent/public/login" novalidate>
                <div class="input-wrapper mb-3">
                    <input type="text" name="username" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Username" value="<?= $usernameValue ?>" autofocus>
                    <span class="material-icons input-icon">person</span>
                </div>
                <div class="input-wrapper mb-3">
                    <input type="password" name="password" id="password" class="form-control <?= $error ? 'error' : '' ?>" placeholder="Password">
                    <span class="material-icons password-toggle" id="togglePassword">visibility_off</span>
                </div>
                <?php if ($error): ?>
                    <div class="error-text"><?= $error ?></div>
                <?php endif; ?>
                <button class="btn btn-primary w-100 mb-2">Login</button>
                <div class="text-center">
                    <a href="#" class="small text-decoration-none">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password toggle
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');
togglePassword.addEventListener('click', () => {
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    togglePassword.textContent = type === 'password' ? 'visibility_off' : 'visibility';
});

// Particle animation
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