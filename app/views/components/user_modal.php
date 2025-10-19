<div id="userModal" class="modal" style="display:none;">
    <div class="modal-content" style="text-align: left;">
        <span class="close">&times;</span>
        <h3 id="userModalTitle" style="text-align: center;">Add New User</h3>
        <form id="userForm" method="POST" action="<?= htmlspecialchars($form_action ?? '../core/users_process.php') ?>">
            <input type="hidden" name="user_id" id="user_id">

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="full_name" style="font-weight: 500;">Full Name</label>
                <input type="text" name="full_name" id="full_name" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="username" style="font-weight: 500;">Username</label>
                <input type="text" name="username" id="username" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="email" style="font-weight: 500;">Email</label>
                <input type="email" name="email" id="email" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="role_id" style="font-weight: 500;">Role</label>
                <select name="role_id" id="role_id" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
                    <option value="">Select a Role</option>
                    <?php foreach ($assignable_roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="password" style="font-weight: 500;">Password</label>
                <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
                <small id="passwordHelp">For new users, a password is required.</small>
            </div>

            <div class="form-group" id="confirmPasswordGroup" style="margin-bottom: 1rem;">
                <label for="confirm_password" style="font-weight: 500;">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
                <small id="passwordMatchMessage" style="color: red;"></small>
            </div>

            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem;">
                <button type="submit" name="saveUser" id="saveUserBtn" style="padding: 0.6rem 1.2rem; border: none; border-radius: 8px; background-color: #2e7d32; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons">save</span> Save
                </button>
            </div>
        </form>
    </div>
</div>