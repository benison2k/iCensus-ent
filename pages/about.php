<?php
session_start();

// Include config and core classes
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Initialize DB and Auth
$db = new Database($config);
$auth = new Auth($db);

// Refresh session to get latest user info (including theme)
$auth->refreshUserSession($_SESSION['user']['id']);
$user = $_SESSION['user'];

// Determine body class based on user theme
$themeClass = ($user['theme'] ?? 'light') === 'dark' ? 'dark-mode' : 'light-mode';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - About</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/about.css">
<link rel="icon" type="image/png" href="../assets/img/iCensusLogoOnly.png">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
/* =========================
   About Card Animations
========================= */
.about-card {
    opacity: 0;
    transform: translateY(30px);
    animation: aboutCardFadeUp 0.7s ease forwards;
}

/* Staggered animation using nth-child */
.about-card:nth-child(1) { animation-delay: 0.1s; }
.about-card:nth-child(2) { animation-delay: 0.2s; }
.about-card:nth-child(3) { animation-delay: 0.3s; }
.about-card:nth-child(4) { animation-delay: 0.4s; }
.about-card:nth-child(5) { animation-delay: 0.5s; }
.about-card:nth-child(6) { animation-delay: 0.6s; }
.about-card:nth-child(7) { animation-delay: 0.7s; }
.about-card:nth-child(8) { animation-delay: 0.8s; }
.about-card:nth-child(9) { animation-delay: 0.9s; }

@keyframes aboutCardFadeUp {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Add spacing from edges */
.about-page {
    padding: 2rem;
    max-width: 900px;
    margin: 2rem auto;
}

/* Optional: prevent scrollbar during animation */
.about-page.animating {
    overflow: hidden;
}
</style>
</head>
<body class="<?= $themeClass; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<main class="about-page animating">

    <!-- About Card Template -->
    <?php
    $aboutCards = [
        [
            'icon' => 'info',
            'title' => 'About iCensus',
            'content' => '<p>iCensus is a digital census management system designed to simplify population data collection, reporting, and analytics. 
            It provides tools for clerks and administrators to securely manage user accounts, analyze demographic data, and generate reports.</p>'
        ],
        [
            'icon' => 'computer',
            'title' => 'System Information',
            'content' => '<ul>
                <li>Version: 1.0.0</li>
                <li>Release Date: August 2025</li>
                <li>Developed by: Bulacan State University – BSIT</li>
            </ul>'
        ],
        [
            'icon' => 'flag',
            'title' => 'Mission & Vision',
            'content' => '<p><strong>Mission:</strong> To provide a secure, reliable, and efficient digital platform for census data management.</p>
                          <p><strong>Vision:</strong> To create a future where population data is accessible, accurate, and drives informed decision-making.</p>'
        ],
        [
            'icon' => 'star',
            'title' => 'Core Features',
            'content' => '<ul class="feature-list">
                <li><span class="material-icons">check_circle</span> Secure Login & Role-based Access</li>
                <li><span class="material-icons">check_circle</span> Automated Report Generation</li>
                <li><span class="material-icons">check_circle</span> Demographic Data Analytics</li>
                <li><span class="material-icons">check_circle</span> User Account Management</li>
                <li><span class="material-icons">check_circle</span> Responsive & Easy-to-Use Interface</li>
            </ul>'
        ],
        [
            'icon' => 'build',
            'title' => 'Technologies Used',
            'content' => '<ul>
                <li>PHP (Backend)</li>
                <li>MySQL (Database)</li>
                <li>HTML, CSS, JavaScript</li>
                <li>Material Icons</li>
            </ul>'
        ],
        [
            'icon' => 'help_outline',
            'title' => 'Frequently Asked Questions',
            'content' => '<ul class="faq-list">
                <li><strong>Who can access iCensus?</strong><br>Clerks and administrators with valid accounts.</li>
                <li><strong>Is the data secure?</strong><br>Yes, we use authentication and role-based access control.</li>
                <li><strong>How do I reset my password?</strong><br>Contact the system administrator to request a password reset.</li>
            </ul>'
        ],
        [
            'icon' => 'timeline',
            'title' => 'System Roadmap',
            'content' => '<ul>
                <li>📱 Mobile App Integration (Coming Soon)</li>
                <li>📊 Advanced Analytics & Charts</li>
                <li>☁ Cloud Hosting Support</li>
            </ul>'
        ],
        [
            'icon' => 'contact_mail',
            'title' => 'Contact',
            'content' => '<p>Email: support@icensus.com</p>
                          <p>Phone: +63 912 345 6789</p>'
        ],
        [
            'icon' => 'person',
            'title' => 'Developed By',
            'content' => '<p><strong>Benson Lastrollo</strong></p>
                          <p>BS Information Technology</p>
                          <p>Bulacan State University</p>
                          <p><em>Capstone Project 2025</em></p>',
            'class' => 'developer-card'
        ]
    ];

    foreach ($aboutCards as $card):
        $cardClass = $card['class'] ?? '';
    ?>
    <div class="about-card <?= $cardClass; ?>">
        <div class="about-header">
            <span class="material-icons about-icon"><?= $card['icon']; ?></span>
            <h3><?= $card['title']; ?></h3>
            <span class="material-icons chevron">expand_more</span>
        </div>
        <div class="about-content">
            <?= $card['content']; ?>
        </div>
    </div>
    <?php endforeach; ?>

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script>
    // Card toggle with chevron rotation
    document.querySelectorAll('.about-card').forEach(card => {
        const chevron = card.querySelector('.chevron');
        card.querySelector('.about-header').addEventListener('click', () => {
            card.classList.toggle('active');
            chevron.classList.toggle('rotated');
        });
    });

    // Remove animating class after all cards finished animation
    const aboutPage = document.querySelector('.about-page');
    const totalDuration = 1000; // total animation time in ms
    window.addEventListener('load', () => {
        setTimeout(() => {
            aboutPage.classList.remove('animating');
        }, totalDuration);
    });
</script>
</body>
</html>
