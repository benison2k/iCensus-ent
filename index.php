<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to iCensus</title>
    <link rel="stylesheet" href="assets/css/landing-page.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <canvas id="particleCanvas"></canvas>

    <header class="header" id="header">
        <div class="container header-container">
            <img src="assets/img/iCensusLogo.png" alt="iCensus Logo" class="logo">
            <a href="pages/login.php" class="btn-login btn-icon" title="Member Login">
                <span class="material-icons">account_circle</span>
            </a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-text-content">
                        <h1 class="hero-title">Empowering Your Barangay with Digital Census Management</h1>
                        <p class="hero-subtitle">
                            Welcome to iCensus. Streamline resident profiling, generate instant reports, and build a better-informed community here in Balagtas.
                        </p>
                        <a href="pages/login.php" class="btn-cta">Access the Portal</a>
                    </div>
                    <div class="hero-visual-content">
                        <div class="visual-placeholder">
                            <span class="material-icons">dashboard_customize</span>
                            <p>System Visual</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2 class="section-title">Everything You Need in One Platform</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><span class="material-icons">groups</span></div>
                        <h3 class="feature-title">Centralized Resident Data</h3>
                        <p class="feature-description">Securely manage, view, and update all resident information in one organized and accessible database.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><span class="material-icons">analytics</span></div>
                        <h3 class="feature-title">Insightful Analytics</h3>
                        <p class="feature-description">Generate real-time demographic reports and statistics with a powerful analytics dashboard.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><span class="material-icons">admin_panel_settings</span></div>
                        <h3 class="feature-title">Role-Based Access</h3>
                        <p class="feature-description">Ensure data security with distinct permission levels for Admins and Encoders.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="how-it-works">
            <div class="container">
                <h2 class="section-title">A Simple, Streamlined Process</h2>
                <div class="process-wrapper">
                    <div class="process-step">
                        <div class="step-icon"><span class="material-icons">lock_open</span></div>
                        <h3 class="step-title">1. Secure Login</h3>
                        <p>Access the system using your officially provided credentials with role-based permissions.</p>
                    </div>
                    <div class="step-arrow">&rarr;</div>
                    <div class="process-step">
                        <div class="step-icon"><span class="material-icons">edit_document</span></div>
                        <h3 class="step-title">2. Manage Data</h3>
                        <p>Easily add new residents, update existing information, and search the entire database in seconds.</p>
                    </div>
                    <div class="step-arrow">&rarr;</div>
                    <div class="process-step">
                        <div class="step-icon"><span class="material-icons">assessment</span></div>
                        <h3 class="step-title">3. Generate Insights</h3>
                        <p>Instantly create official reports and visualize demographic data through the analytics dashboard.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="role-section">
            <div class="container">
                 <h2 class="section-title">Designed For Your Role</h2>
                 <div class="role-row">
                     <div class="role-text">
                         <h3>For Barangay Admins</h3>
                         <p>Oversee all census operations with a comprehensive dashboard. Manage user accounts for encoders, view system-wide analytics for better community planning, and ensure the integrity and security of all resident data.</p>
                     </div>
                     <div class="role-img-container">
                        <span class="material-icons role-icon">supervisor_account</span>
                     </div>
                 </div>
                 <div class="role-row reverse">
                      <div class="role-text">
                         <h3>For Data Encoders</h3>
                         <p>Focus on what you do best: accurate and efficient data entry. With a clean, straightforward interface, you can add and update resident profiles quickly, minimizing errors and maximizing productivity.</p>
                     </div>
                     <div class="role-img-container">
                        <span class="material-icons role-icon">edit</span>
                     </div>
                 </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to Get Started?</h2>
                <p>Access the secure portal to begin managing your community's census data.</p>
                <a href="pages/login.php" class="btn-cta">Access the Portal</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date("Y") ?> iCensus System. All Rights Reserved.</p>
    </footer>

    <script>
// Header scroll effect
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Particle animation script
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particlesArray = [];

function resizeCanvas() {
    canvas.width = window.innerWidth;
    // The key fix is here: set height to window.innerHeight
    canvas.height = window.innerHeight; 
}

window.addEventListener('resize', () => {
    resizeCanvas();
    initParticles();
});

class Particle {
    constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 2.5 + 1;
        this.speedX = Math.random() * 0.8 - 0.4;
        this.speedY = Math.random() * 0.8 - 0.4;
    }
    update() {
        this.x += this.speedX;
        this.y += this.speedY;
        if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
    }
    draw() {
        ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
}

function initParticles() {
    particlesArray = [];
    let numberOfParticles = (canvas.width * canvas.height) / 9000;
    for (let i = 0; i < numberOfParticles; i++) {
        particlesArray.push(new Particle());
    }
}

function animateParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particlesArray.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animateParticles);
}

// Initial setup
resizeCanvas();
initParticles();
animateParticles();
    </script>

</body>
</html>