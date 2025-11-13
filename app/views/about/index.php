<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About iCensus</title>
    <?php $base_url = '/iCensus-ent/public'; ?>
    <link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/about.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : 'light-mode' ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome">
        <h2>About the iCensus System</h2>
    </div>

    <main class="about-page">
        <div class="about-card" id="card-intro">
            <div class="about-header">
                <span class="material-icons about-icon">info</span>
                <h2>Welcome to iCensus</h2>
                <span class="material-icons chevron">expand_more</span>
            </div>
            <div class="about-content">
                <p>iCensus is a modern digital platform designed to streamline and enhance the way your barangay manages resident information. Our system provides a secure, centralized, and efficient solution for census data management, empowering your local government to serve the community better.</p>
            </div>
        </div>

        <div class="about-card" id="card-features">
            <div class="about-header">
                <span class="material-icons about-icon">stars</span>
                <h3>Key Features for Barangay Admins</h3>
                <span class="material-icons chevron">expand_more</span>
            </div>
            <div class="about-content">
                <ul>
                    <li><strong>Resident Management:</strong> Add, view, search, and update resident profiles with an intuitive and comprehensive interface.</li>
                    <li><strong>Powerful Analytics:</strong> Instantly generate insightful reports and visualize demographic data with a customizable dashboard to aid in evidence-based governance.</li>
                    <li><strong>Role-Based Access Control:</strong> Ensure data security and integrity by managing user accounts and assigning specific roles (Admin, Encoder) with distinct permissions.</li>
                    <li><strong>System Configuration:</strong> Customize system settings, manage your personal profile, and maintain the application to suit your barangay's needs.</li>
                </ul>
            </div>
        </div>

        <div class="about-card" id="card-mission">
            <div class="about-header">
                <span class="material-icons about-icon">track_changes</span>
                <h2>Our Mission</h2>
                <span class="material-icons chevron">expand_more</span>
            </div>
            <div class="about-content">
                <p>Our mission is to empower local government units with the technology to build better-informed, more responsive, and well-organized communities. By digitizing the census process, we aim to provide barangay officials with the accurate data they need for effective planning, resource allocation, and public service.</p>
            </div>
        </div>

        <div class="about-card developer-card" id="card-dev">
            <div class="about-header">
                <span class="material-icons about-icon">code</span>
                <h3>The Developer</h3>
                <span class="material-icons chevron">expand_more</span>
            </div>
            <div class="about-content">
                <p>iCensus was developed by a passionate individual dedicated to using technology for community development. The system is a product of careful planning, design, and a commitment to creating a tool that is both powerful and user-friendly for barangay officials.</p>
            </div>
        </div>
        
        <div class="about-card" id="card-contact">
            <div class="about-header">
                <span class="material-icons about-icon">help_outline</span>
                <h2>Support & Contact</h2>
                <span class="material-icons chevron">expand_more</span>
            </div>
            <div class="about-content">
                <p>If you encounter any issues, have questions, or need assistance with the iCensus system, please do not hesitate to reach out to the system administrator or the developer for support.</p>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script src="<?= $base_url ?>/assets/js/about.js"></script>
</body>
</html>