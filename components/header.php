<!-- core/components/header.php -->

<!-- If you can, prefer absolute paths like /iCensus/assets/css/header.css -->
<link rel="stylesheet" href="../assets/css/header.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">


<header class="header">
    <!-- Back Button -->
    <button class="back-button" id="backButton" title="Go Back">
        <span class="material-icons">arrow_back</span>
    </button>

    <div class="header-center">
        <a href="../pages/dashboard.php" class="brand">
            <img src="../assets/barangay-logo.png" alt="Barangay Logo" class="logo">
            <h1>iCensus</h1>
        </a>
    </div>

    <!-- Logout Icon (consider making this absolute: /iCensus/core/logout.php) -->
    <a href="logout.php" class="logout-icon" title="Logout">
        <span class="material-icons">logout</span>
    </a>
</header>

<script>
// Force a fresh load when navigating back via this header button
(function() {
  const backButton = document.getElementById('backButton');
  if (backButton) {
    backButton.addEventListener('click', function(e) {
      e.preventDefault();

      // Try to navigate to referrer if same-origin; otherwise fallback
      const ref = document.referrer ? new URL(document.referrer, window.location.href) : null;

      // Helper: append a cache-busting param
      const bust = (urlObj) => {
        const u = new URL(urlObj.href);
        u.searchParams.set('_r', Date.now().toString());
        return u.toString();
      };

      if (ref && ref.origin === window.location.origin) {
        // Replace to avoid adding another history entry, and bust cache
        window.location.replace(bust(ref));
      } else {
        // Fallback to dashboard; adjust to your absolute path if needed
        const fallback = new URL('/iCensus/pages/dashboard.php', window.location.origin);
        fallback.searchParams.set('_r', Date.now().toString());
        window.location.replace(fallback.toString());
      }
    });
  }

  // Also handle true browser back/forward restores from the bfcache:
  // if the page is shown from bfcache (persisted), reload to reflect latest theme.
  window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
      // Full reload to get the current theme/body class from server or JS
      window.location.reload();
    }
  });
})();
</script>
