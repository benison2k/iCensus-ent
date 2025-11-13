<div id="tab-account" class="tab-pane active">
    <h3>Account Information</h3>
    
    <form id="usernameForm" method="POST">
        <?= Csrf::getField(); ?>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
        </div>
        <input type="hidden" name="update_username" value="1">
        <button type="submit"><span class="material-icons">save</span> Save Username</button>
    </form>
</div>