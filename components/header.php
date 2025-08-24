<!-- Absolute paths preferred -->
<link rel="stylesheet" href="../assets/css/header.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<header class="header">
    <!-- Back Button -->
    <button class="back-button" id="backButton" title="Go Back">
        <span class="material-icons">arrow_back</span>
    </button>

    <!-- Centered Header Logo -->
    <div class="header-logo">
        <a href="../pages/dashboard.php">
            <img src="../assets/img/iCensusLogoSmaller.png" alt="iCensus Logo" class="logo">
        </a>
    </div>

    <!-- Logout Icon -->
    <a href="logout.php" class="logout-icon" title="Logout">
        <span class="material-icons">logout</span>
    </a>
</header>

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
        const fallback = new URL('/iCensus/pages/dashboard.php', window.location.origin);
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
