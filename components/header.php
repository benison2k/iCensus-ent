<!-- core/components/header.php -->

<link rel="stylesheet" href="../assets/css/header.css">

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

    <!-- Logout Icon -->
    <a href="logout.php" class="logout-icon" title="Logout">
        <span class="material-icons">logout</span>
    </a>
</header>

<!-- JS to handle back button properly -->
<script>
const backButton = document.getElementById('backButton');
backButton.addEventListener('click', (e) => {
    e.preventDefault();
    if (document.referrer) {
        // Navigate back in history
        window.history.back();
    } else {
        // Fallback to dashboard if no referrer
        window.location.href = '/iCensus/pages/dashboard.php';
    }
});
</script>
