<div id="userModal" class="modal modal-modern">
    <div class="modal-modern-content">
        <div class="modal-modern-header">
            <h3 id="userModalTitle">Add New User</h3>
            <span class="close"><span class="material-icons">close</span></span>
        </div>
        
        <form id="userForm" method="POST" action="<?= htmlspecialchars($form_action ?? '/iCensus-ent/public/sysadmin/users/process') ?>">
            <?= Csrf::getField(); ?>

            <div class="modal-modern-body">
                <input type="hidden" name="user_id" id="user_id">
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="form-group">
                    <label for="role_id">Role</label>
                    <select name="role_id" id="role_id" required>
                        <option value="">Select a Role</option>
                        <?php foreach ($assignable_roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current">
                    <small id="passwordHelp">A password is required for new users.</small>
                </div>
                
                <div class="form-group" id="confirmPasswordGroup">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password">
                    <small id="passwordMatchMessage" style="color: red;"></small>
                </div>
            </div>

            <div class="modal-modern-footer">
                <button type="submit" name="action" value="save" id="saveUserBtn" class="modal-footer-btn btn-save">
                    <span class="material-icons">save</span> Save
                </button>
            </div>
        </form>
    </div>
</div>