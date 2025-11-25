<?php
// app/views/components/header.php

$base_url = '/iCensus-ent/public'; 

// 1. Check if a session is active and get user role
$isUserLoggedIn = isset($_SESSION['user']);
$isAdmin = $isUserLoggedIn && ($_SESSION['user']['role_name'] === 'System Admin');
$isEncoder = $isUserLoggedIn && ($_SESSION['user']['role_name'] === 'Encoder');

// 2. Determine the correct dashboard link based on role
$dashboardLink = $isAdmin ? $base_url . '/sysadmin/dashboard' : ($isEncoder ? $base_url . '/encoder-dashboard' : $base_url . '/dashboard');

// 3. Get current page context to decide button visibility
$requestUri = $_SERVER['REQUEST_URI'];
$isDashboardPage = (strpos($requestUri, 'dashboard') !== false);

// 4. Determine "Parent URL" for the Back button (Fallback for guests)
$parentUrl = $dashboardLink; 
if (strpos($requestUri, '/sysadmin/') !== false && !$isDashboardPage) {
    $parentUrl = $base_url . '/sysadmin/dashboard';
}

// 5. Check "Pinned Sidebar" preference
$isSidebarPinned = isset($_SESSION['user']['sidebar_pinned']) && $_SESSION['user']['sidebar_pinned'] == 1;
?>

<head>
    <link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
    
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/header2.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/sidebar.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <?php if ($isUserLoggedIn): ?>
    <script>
        const SESSION_TIMEOUT_MS = 1801 * 1000; // 30 minutes + 1 second buffer
        setTimeout(() => {
            window.location.reload(); // Reload triggers server-side redirect to login
        }, SESSION_TIMEOUT_MS);
    </script>
    <?php endif; ?>

    <script src="<?= $base_url ?>/assets/js/sidebar.js" defer></script>
</head>

<?php if ($isSidebarPinned): ?>
<script>
    document.body.classList.add('sidebar-pinned');
</script>
<?php endif; ?>

<header class="header">
    <?php if ($isUserLoggedIn && !$isDashboardPage): ?>
        <button id="sidebarToggleBtn" class="back-button" title="Open Menu">
            <span class="material-icons">menu</span>
        </button>

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

    <?php if ($isUserLoggedIn): ?>
        <button id="logoutBtn" class="logout-icon" title="Logout">
            <span class="material-icons">logout</span>
        </button>
    <?php else: ?>
        <div class="header-slot"></div>
    <?php endif; ?>
</header>

<?php 
// Include Sidebar Component (Only if logged in and NOT on dashboard)
if ($isUserLoggedIn && !$isDashboardPage) {
    include __DIR__ . "/sidebar.php"; 
}

// Include Logout Modal (Always included so it's ready to use)
include __DIR__ . "/LogOutModal.php"; 
?>