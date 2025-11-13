<div id="tab-security" class="tab-pane">
    <h3>Security Settings</h3>
    
    <form id="passwordForm" method="POST">
        <?= Csrf::getField(); ?>
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
        </div>
        <button type="button" id="verifyCurrentBtn">Verify Password</button>
        <span id="verifyMessage" style="margin-left:1rem;color:red;"></span>
        <div class="error-text" id="passwordOtpError" style="margin-bottom: 0;"></div>

        <div id="newPasswordFields" style="display:none; margin-top:1.5rem;">
            <div id="otpRequirement" style="display:none;" class="password-otp-group">
                <h4>OTP Required</h4>
                <p style="font-size:0.9rem; margin-bottom: 5px;">OTP sent to your email for security.</p>
                <input type="text" name="otp" id="otpPasswordField" placeholder="Enter OTP" maxlength="6" pattern="\d{6}" inputmode="numeric">
                <a href="#" id="resendOtpBtnPass" style="display:none;">Resend Code</a>
                <span id="passCooldownTimer" class="small text-muted" style="margin-top: 5px; display: none;"></span>
            </div>
            <div class="form-group" style="margin-top:1.5rem;">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password" required>
                <div class="strength-meter">
                    <div id="strength-bar"></div>
                </div>
                <span id="strength-text" class="strength-text"></span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    <span id="passwordMatchIcon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);"></span>
                </div>
            </div>
            <input type="hidden" name="update_password" value="1">
            <button type="submit" id="passwordSubmit" disabled>
                <span class="material-icons">save</span> Save Password
            </button>
        </div>
    </form>

    <hr style="margin: 2rem 0;">
    <h4 style="font-size: 1.3rem; font-weight: 600; margin-bottom: 1rem;">Email & Recovery</h4>
    <form id="emailForm" method="POST">
        <?= Csrf::getField(); ?>
        <div class="form-group">
            <label for="email">Email Address (for 2FA & Password Reset)</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email address" required>
            <small style="margin-top: 5px; color: #6c757d;">Note: 2FA must be disabled to change or unbind your email.</small>
        </div>
        <input type="hidden" name="update_email" value="1">
        <div class="button-group">
            <button type="submit" data-original-text="Save Email">
                <span class="btn-icon material-icons">save</span>
                <span class="btn-text">Save Email</span>
                <span class="btn-spinner material-icons spinner" style="display: none;">loop</span>
            </button>
            <button type="button" id="unbindEmailBtn" data-original-text="Unbind Email">
                <span class="btn-icon material-icons">link_off</span>
                <span class="btn-text">Unbind Email</span>
                <span class="btn-spinner material-icons spinner" style="display: none;">loop</span>
            </button>
        </div>
    </form>

    <?php if (!empty($user['email'])): ?>
        <hr style="margin: 2rem 0;">
        <div class="form-group">
            <label for="twoFaSwitch">Two-Factor Authentication</label>
            <p style="font-size:0.9rem; color:#555; margin-bottom: 1rem;">
                Enable 2FA via email for an extra layer of security on login.
            </p>
            <form id="twoFaForm">
                <?= Csrf::getField(); ?>
                <div style="display:flex; align-items:center; gap:1rem;">
                    <label class="switch">
                        <input type="checkbox" id="twoFaSwitch" <?= $user['two_fa'] == 1 ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>
                    <span id="twoFaLabel"><?= $user['two_fa'] == 1 ? 'Enabled' : 'Disabled'; ?></span>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<div id="otpUnbindModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; padding: 30px;">
        <span class="close" id="closeOtpUnbindModal">&times;</span>
        <h3 style="margin-top: 0; text-align: center;">Confirm Email Removal</h3>
        <p class="text-muted" style="text-align: center;">Enter the 6-digit code sent to your email to confirm you want to unbind your email.</p>
        <form id="otpUnbindForm" action="<?= $base_url ?>/settings/confirm-unbind-email" method="POST" style="margin-top: 1.5rem;">
            <?= Csrf::getField(); ?>
            <div class="input-wrapper mb-3" style="display: flex; justify-content: center;">
                <input type="text" name="otp" id="otpUnbindInput" class="form-control" placeholder="______" required autofocus maxlength="6" pattern="\d{6}" inputmode="numeric">
            </div>
            <div class="error-text" id="otpUnbindError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpUnbindVerifyBtn" style="background-color: #dc3545;">Confirm Unbind</button>
        </form>
        <a href="#" id="resendUnbindOtpBtn" class="small text-decoration-none" style="display: block; text-align: center;">Resend Code</a>
        <span id="cooldownUnbindTimer" class="small text-muted" style="margin-top: 5px; display: none; text-align: center;"></span>
    </div>
</div>

<div id="otpToggleModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; padding: 30px;">
        <span class="close" id="closeOtpToggleModal">&times;</span>
        <h3 style="margin-top: 0; text-align: center;">Confirm Disable 2FA</h3>
        <p class="text-muted" style="text-align: center;">Enter the 6-digit code sent to your email to confirm you want to disable Two-Factor Authentication.</p>
        <form id="otpToggleForm" action="<?= $base_url ?>/settings/verify-2fa-toggle-otp" method="POST" style="margin-top: 1.5rem;">
            <?= Csrf::getField(); ?>
            <div class="input-wrapper mb-3" style="display: flex; justify-content: center;">
                <input type="text" name="otp" id="otpToggleInput" class="form-control" placeholder="______" required autofocus maxlength="6" pattern="\d{6}" inputmode="numeric">
            </div>
            <div class="error-text" id="otpToggleError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpToggleVerifyBtn">Confirm Disable</button>
        </form>
        <a href="#" id="resendToggleOtpBtn" class="small text-decoration-none" style="display: block; text-align: center;">Resend Code</a>
        <span id="cooldownToggleTimer" class="small text-muted" style="margin-top: 5px; display: none; text-align: center;"></span>
    </div>
</div>

<div id="otpBindModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; padding: 30px;">
        <span class="close" id="closeOtpBindModal">&times;</span>
        <h3 style="margin-top: 0; text-align: center;">Confirm New Email</h3>
        <p class="text-muted" style="text-align: center;">Enter the 6-digit code sent to your <strong>new</strong> email address to confirm the change.</p>
        <form id="otpBindForm" action="<?= $base_url ?>/settings/confirm-bind-email" method="POST" style="margin-top: 1.5rem; margin-bottom: 1rem;">
            <?= Csrf::getField(); ?>
            <div class="otp-container" id="otpBindContainer">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>
            <input type="hidden" name="otp" id="otpBindInput" required>
            <div class="error-text" id="otpBindError" style="margin-bottom: 1rem;"></div>
            <button type="submit" class="btn btn-primary w-100 mb-3" id="otpBindVerifyBtn">Confirm Email</button>
        </form>
        <a href="#" id="resendBindOtpBtn" class="small text-decoration-none" style="display: block; text-align: center;">Resend Code</a>
        <span id="cooldownBindTimer" class="small text-muted" style="margin-top: 5px; display: none; text-align: center;"></span>
    </div>
</div>