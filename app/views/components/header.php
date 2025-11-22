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
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/sidebar.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php if ($isUserLoggedIn): ?>
    <script>
        const SESSION_TIMEOUT_MS = 1801 * 1000;
        setTimeout(() => {
            window.location.reload();
        }, SESSION_TIMEOUT_MS);
    </script>
    <?php endif; ?>
    
    <script src="<?= $base_url ?>/assets/js/sidebar.js" defer></script>
</head>

<header class="header">
    <?php if (!$isDashboardPage && $isUserLoggedIn): ?>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button id="sidebarToggleBtn" class="back-button" title="Open Menu" style="border:none; background:none; cursor:pointer;">
                <span class="material-icons">menu</span>
            </button>
            
            </div>
    <?php elseif (!$isDashboardPage): ?>
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

<?php 
// Only include the sidebar component if we are NOT on the dashboard
if (!$isDashboardPage && $isUserLoggedIn) {
    include __DIR__ . "/sidebar.php"; 
}

include __DIR__ . "/LogOutModal.php"; 
?>