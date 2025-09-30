<?php
$base_url = '/iCensus-ent/public'; // The correct base URL for all links

// Check if a session is active and if the user is a System Admin
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role_name'] === 'System Admin';

// Determine the correct dashboard link
$dashboardLink = $isAdmin ? $base_url . '/sysadmin/dashboard' : $base_url . '/dashboard';

// Get the current request URI to determine if we are on a dashboard page
$requestUri = $_SERVER['REQUEST_URI'];
$isDashboardPage = (strpos($requestUri, 'dashboard') !== false);
?>

<head>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/header2.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<header class="header">
    <?php if (!$isDashboardPage): ?>
        <button class="back-button" id="backButton" title="Go Back">
            <span class="material-icons">arrow_back</span>
        </button>
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

<script>
(function() {
  const backButton = document.getElementById('backButton');
  if (backButton) {
    backButton.addEventListener('click', function() {
      // A simpler, more reliable way to go back
      window.history.back();
    });
  }
})();
</script>