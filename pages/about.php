<?php
session_start();
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - About</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/about.css">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>

    <main class="about-page">

        <!-- Intro -->
        <div class="about-card">
            <span class="material-icons about-icon">info</span>
            <h2>About iCensus</h2>
            <div class="about-content">
                <p>
                    iCensus is a digital census management system designed to simplify population data collection, reporting, and analytics. 
                    It provides tools for clerks and administrators to securely manage user accounts, analyze demographic data, and generate reports.
                </p>
            </div>
        </div>

        <!-- System Info -->
        <div class="about-card">
            <span class="material-icons about-icon">computer</span>
            <h3>System Information</h3>
            <div class="about-content">
                <ul>
                    <li>Version: 1.0.0</li>
                    <li>Release Date: August 2025</li>
                    <li>Developed by: Bulacan State University – BSIT</li>
                </ul>
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="about-card">
            <span class="material-icons about-icon">flag</span>
            <h3>Mission & Vision</h3>
            <div class="about-content">
                <p><strong>Mission:</strong> To provide a secure, reliable, and efficient digital platform for census data management.</p>
                <p><strong>Vision:</strong> To create a future where population data is accessible, accurate, and drives informed decision-making.</p>
            </div>
        </div>

        <!-- Core Features -->
        <div class="about-card">
            <span class="material-icons about-icon">star</span>
            <h3>Core Features</h3>
            <div class="about-content">
                <ul class="feature-list">
                    <li><span class="material-icons">check_circle</span> Secure Login & Role-based Access</li>
                    <li><span class="material-icons">check_circle</span> Automated Report Generation</li>
                    <li><span class="material-icons">check_circle</span> Demographic Data Analytics</li>
                    <li><span class="material-icons">check_circle</span> User Account Management</li>
                    <li><span class="material-icons">check_circle</span> Responsive & Easy-to-Use Interface</li>
                </ul>
            </div>
        </div>

        <!-- Technologies Used -->
        <div class="about-card">
            <span class="material-icons about-icon">build</span>
            <h3>Technologies Used</h3>
            <div class="about-content">
                <ul>
                    <li>PHP (Backend)</li>
                    <li>MySQL (Database)</li>
                    <li>HTML, CSS, JavaScript</li>
                    <li>Material Icons</li>
                </ul>
            </div>
        </div>

        <!-- FAQ -->
        <div class="about-card">
            <span class="material-icons about-icon">help_outline</span>
            <h3>Frequently Asked Questions</h3>
            <div class="about-content">
                <ul class="faq-list">
                    <li><strong>Who can access iCensus?</strong><br>Clerks and administrators with valid accounts.</li>
                    <li><strong>Is the data secure?</strong><br>Yes, we use authentication and role-based access control.</li>
                    <li><strong>How do I reset my password?</strong><br>Contact the system administrator to request a password reset.</li>
                </ul>
            </div>
        </div>

        <!-- Roadmap -->
        <div class="about-card">
            <span class="material-icons about-icon">timeline</span>
            <h3>System Roadmap</h3>
            <div class="about-content">
                <ul>
                    <li>📱 Mobile App Integration (Coming Soon)</li>
                    <li>📊 Advanced Analytics & Charts</li>
                    <li>☁ Cloud Hosting Support</li>
                </ul>
            </div>
        </div>

        <!-- Contact -->
        <div class="about-card">
            <span class="material-icons about-icon">contact_mail</span>
            <h3>Contact</h3>
            <div class="about-content">
                <p>Email: support@icensus.com</p>
                <p>Phone: +63 912 345 6789</p>
            </div>
        </div>

        <!-- Developer -->
        <div class="about-card developer-card">
            <span class="material-icons about-icon">person</span>
            <h3>Developed By</h3>
            <div class="about-content">
                <p><strong>Benson Lastrollo</strong></p>
                <p>BS Information Technology</p>
                <p>Bulacan State University</p>
                <p><em>Capstone Project 2025</em></p>
            </div>
        </div>

    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Script for Toggle -->
    <script>
        document.querySelectorAll('.about-card').forEach(card => {
            card.addEventListener('click', () => {
                card.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
