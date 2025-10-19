<?php
$base_url = '/iCensus-ent/public'; // The correct base URL for all links

// Check if a session is active
$isUserLoggedIn = isset($_SESSION['user']);
$isAdmin = $isUserLoggedIn && $_SESSION['user']['role_name'] === 'System Admin';
$isEncoder = $isUserLoggedIn && $_SESSION['user']['role_name'] === 'Encoder';

// Determine the correct dashboard link for the logo
$dashboardLink = $isAdmin ? $base_url . '/sysadmin/dashboard' : ($isEncoder ? $base_url . '/encoder-dashboard' : $base_url . '/dashboard');

// Get the current request URI to determine the page
$requestUri = $_SERVER['REQUEST_URI'];
$isDashboardPage = (strpos($requestUri, 'dashboard') !== false);

// Determine the correct "UP" URL for the back button
$parentUrl = $dashboardLink; 
if (strpos($requestUri, '/sysadmin/') !== false && !$isDashboardPage) {
    $parentUrl = $base_url . '/sysadmin/dashboard';
}

?>

<head>
    <link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/header2.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php if ($isUserLoggedIn): ?>
    <script>
        // The session timeout is set to 1800 seconds (30 minutes) on the server.
        // We'll set a JavaScript timer for the same duration.
        const SESSION_TIMEOUT_MS = 1801 * 1000;

        // When the timer finishes, reload the page. The server-side code in 'init.php'
        // will see that the session has expired and will handle the redirect to the login page.
        setTimeout(() => {
            // You can optionally alert the user before reloading
            // alert("Your session has expired due to inactivity. You will be redirected to the login page.");
            window.location.reload();
        }, SESSION_TIMEOUT_MS);
    </script>
    <?php endif; ?>
</head>

<header class="header">
    <?php if (!$isDashboardPage): ?>
        <a href="<?= $parentUrl ?>" class="back-button" title="Go Back">
            <span class="material-icons">arrow_back</span>
        </a>
    <?php else: ?>
        <div class="header-slot"></div>
    <?php endif; ?>

    <div class="header-logo">
        <a href="<?= $dashboardLink ?>">
            <img src="<?= $base_url ?>/assets/img/iCensusLogoSmaller.png" alt="iCensus Logo" class="logo">
        </a>
    </div>

    <button id="logoutBtn" class="logout-icon" title="Logout">
        <span class="material-icons">logout</span>
    </button>
</header>

<?php include __DIR__ . "/LogOutModal.php"; ?>