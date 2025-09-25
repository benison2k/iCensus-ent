<?php
// Detect current page
$currentFile = basename($_SERVER['PHP_SELF']);

// Check if a session is active and if the user is a System Admin
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role_name'] === 'System Admin';

// Determine the correct dashboard link based on the user's role
$dashboardLink = $isAdmin ? '../sysadmin/dashboard.php' : '../pages/dashboard.php';
?>

<head>
<link rel="stylesheet" href="../assets/css/header2.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<header class="header">
    <?php if ($currentFile !== 'dashboard.php'): ?>
        <button class="back-button" id="backButton" title="Go Back">
            <span class="material-icons">arrow_back</span>
        </button>
    <?php else: ?>
        <div class="header-slot"></div>
    <?php endif; ?>

    <div class="header-logo">
        <a href="<?= $dashboardLink ?>">
            <img src="../assets/img/iCensusLogoSmaller.png" alt="iCensus Logo" class="logo">
        </a>
    </div>

    <button id="logoutBtn" class="logout-icon" title="Logout">
        <span class="material-icons">logout</span>
    </button>
</header>

<?php include __DIR__ . "/../components/LogOutModal.php"; ?>

<script>
(function() {
  const backButton = document.getElementById('backButton');
  if (backButton) {
    backButton.addEventListener('click', function(e) {
      e.preventDefault();
      const ref = document.referrer ? new URL(document.referrer, window.location.href) : null;
      const bust = (urlObj) => {
        const u = new URL(urlObj.href);
        u.searchParams.set('_r', Date.now().toString());
        return u.toString();
      };
      if (ref && ref.origin === window.location.origin) {
        window.location.replace(bust(ref));
      } else {
        const fallback = new URL('<?= $dashboardLink ?>', window.location.origin);
        fallback.searchParams.set('_r', Date.now().toString());
        window.location.replace(fallback.toString());
      }
    });
  }

  window.addEventListener('pageshow', function(e) {
    if (e.persisted) window.location.reload();
  });
})();
</script>