<?php
// Define the base URL for asset paths, ensuring it's consistent.
$base_url = '/iCensus-ent/public';
?>

<link rel="stylesheet" href="<?= $base_url ?>/assets/css/footer.css">

<footer class="footer">
    <p>&copy; <?= date("Y") ?> iCensus System. All rights reserved.</p>
</footer>

<button id="backToTopBtn" title="Go to top">
    <span class="material-icons">arrow_upward</span>
</button>

<script src="<?= $base_url ?>/assets/js/global.js"></script>