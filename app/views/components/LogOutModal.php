<?php
// Define the base URL for asset paths
$base_url = '/iCensus-ent/public'; 
?>

<link rel="stylesheet" href="<?= $base_url ?>/assets/css/LogOutModal.css">
<script src="<?= $base_url ?>/assets/js/LogOutModal.js" defer></script>

<div id="logoutModal" class="modal">
  <div class="modal-content">
    <h2>Confirm Logout</h2>
    <p>Are you sure you want to log out?</p>
    <div class="modal-actions">
      <button id="confirmLogout" class="btn confirm">Yes, Logout</button>
      <button id="cancelLogout" class="btn cancel">Cancel</button>
    </div>
  </div>
</div>