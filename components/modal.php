<?php
$id = $id ?? 'modal';
$message = $message ?? '';
$type = $type ?? 'success';
$show = !empty($message) ? 'true' : 'false';
?>
<div id="<?= $id ?>" class="modal" data-show="<?= $show ?>">
    <div class="modal-content <?= $type ?>">
        <span class="close">&times;</span>
        <p><?= htmlspecialchars($message) ?></p>
    </div>
</div>
<script>initModal('<?= $id ?>');</script>
