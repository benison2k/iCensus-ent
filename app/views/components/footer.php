<?php
// Define the base URL for asset paths, ensuring it's consistent.
$base_url = '/iCensus-ent/public';
?>

<style>
/* Back to Top Button Styles */
#backToTopBtn {
    display: none;
    position: fixed;
    bottom: 20px;
    right: 30px;
    z-index: 99;
    border: none;
    outline: none;
    background-color: #0d6efd;
    color: white;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    transition: opacity 0.3s, visibility 0.3s;
}

#backToTopBtn:hover {
    background-color: #0a58ca;
}

body.dark-mode #backToTopBtn {
    background-color: #4da3ff;
}

body.dark-mode #backToTopBtn:hover {
    background-color: #64b5f6;
}
</style>

<footer class="footer">
    <p>&copy; <?= date("Y") ?> iCensus System. All rights reserved.</p>
</footer>

<button id="backToTopBtn" title="Go to top">
    <span class="material-icons">arrow_upward</span>
</button>

<script src="<?= $base_url ?>/assets/js/global.js"></script>