<?php
// app/controllers/SettingsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/functions.php';

class SettingsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: /iCensus-ent/public/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);

        view('settings/index', $data);
    }
    
    public function process() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);
        $userId = $_SESSION['user']['id'];
        $oldUsername = $_SESSION['user']['username'];
        $oldEmail = $_SESSION['user']['email'] ?? '';
        
        try {
            $message = 'Settings saved.';
            
            // --- Update Email Logic ---
            if (isset($_POST['update_email'])) {
                $newEmail = trim($_POST['email']);
                if ($oldEmail !== $newEmail) {
                    $stmt = $db->getPdo()->prepare("UPDATE users SET email=? WHERE id=?");
                    $stmt->execute([$newEmail, $userId]);
                    $auth->refreshUserSession($userId);
                    log_action('INFO', 'SETTINGS_UPDATE', "User updated their email address from '{$oldEmail}' to '{$newEmail}'.");
                    $message = 'Email updated successfully.';
                } else {
                    $message = 'Email is the same, no changes made.';
                }
            }
            // --- End Update Email Logic ---

            if (isset($_POST['update_username'])) {
                $newUsername = $_POST['username'];
                if ($oldUsername !== $newUsername) {
                    $auth->updateUsername($userId, $newUsername);
                    log_action('INFO', 'SETTINGS_UPDATE', "User updated their username from '{$oldUsername}' to '{$newUsername}'.");
                    $message = 'Username updated successfully';
                } else {
                    $message = 'Username is the same, no changes made.';
                }
            }
            
            // --- MODIFIED Password Update Logic ---
            if (isset($_POST['update_password'])) {
                $role = $_SESSION['user']['role_name'];
                $newPassword = $_POST['password'];
                $submittedOtp = $_POST['otp'] ?? '';
                
                // 1. Check if OTP is required and validate it for System Admin
                if ($role === 'System Admin') {
                    if (!isset($_SESSION['password_change_otp_required']) || !$_SESSION['password_change_otp_required']) {
                        http_response_code(403);
                        echo json_encode(['status' => 'error', 'message' => 'OTP session expired. Please verify current password again.']);
                        exit;
                    }
                    if (empty($submittedOtp)) {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => 'OTP is required to change password.']);
                        exit;
                    }

                    // 2. Validate OTP
                    // NOTE: We reuse Auth::verifyOtp, which also clears the OTP columns upon success.
                    $otp_result = $auth->verifyOtp($userId, $submittedOtp);
                    
                    if (!$otp_result['success']) {
                        http_response_code(403);
                        echo json_encode(['status' => 'error', 'message' => $otp_result['message']]);
                        exit;
                    }
                    
                    // 3. Clear session flags on success
                    unset($_SESSION['password_change_otp_required']);
                    unset($_SESSION['password_change_otp_last_sent']);
                }
                
                // 4. Update the password
                $auth->updatePassword($userId, $newPassword);
                log_action('INFO', 'SETTINGS_UPDATE', "User changed their password." . ($role === 'System Admin' ? " (OTP verified)" : ""));
                $message = 'Password updated successfully';
            }
            // --- END MODIFIED Password Update Logic ---
            
            echo json_encode(['status' => 'success', 'message' => $message]);

        } catch (Exception $e) {
            log_action('ERROR', 'SETTINGS_ERROR', $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }

        exit;
    }

    /**
     * MODIFIED: This now acts as STEP 1 for password change.
     * Checks password, sends OTP for System Admin, or grants direct access for others.
     */
    public function verifyPassword() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'];
        $currentPassword = $_POST['current_password'] ?? '';
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);

        // 1. Verify the current password regardless of role
        if (!$auth->verifyPassword($userId, $currentPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }

        // 2. Check for System Admin and OTP requirement
        if ($role === 'System Admin') {
            $email = $_SESSION['user']['email'] ?? '';
            
            if (empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'System Admin requires an email address set in settings for password changes.']);
                exit;
            }
            
            // Send the OTP
            $otp_sent = $auth->generateAndSendOtp($userId, $email);
            
            if (!$otp_sent) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP for password change. Check system logs.']);
                exit;
            }
            
            // Set session flag to indicate OTP is pending for password change
            $_SESSION['password_change_otp_required'] = true;
            $_SESSION['password_change_otp_last_sent'] = time(); // Start cooldown timer
            
            // Return status to client to open OTP input modal
            echo json_encode(['status' => 'otp_sent', 'message' => 'Current password verified. OTP sent to email.']);
            exit;
        }

        // 3. For non-Admin users: proceed directly
        echo json_encode(['status' => 'success', 'message' => 'Password verified.']);
        exit;
    }
    
    /**
     * NEW: Handles OTP resend for System Admin password change with cooldown.
     */
    public function resendPasswordChangeOtp() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if (!isset($_SESSION['password_change_otp_required']) || !$_SESSION['password_change_otp_required']) {
            echo json_encode(['status' => 'error', 'message' => 'Password change session expired. Please verify current password again.']);
            exit;
        }

        $cooldown_duration = 60; // 60 seconds (1 minute)
        $last_sent_key = 'password_change_otp_last_sent';
        $last_sent_time = $_SESSION[$last_sent_key] ?? 0;
        $time_since_last_sent = time() - $last_sent_time;

        if ($time_since_last_sent < $cooldown_duration) {
            $remaining_time = $cooldown_duration - $time_since_last_sent;
            echo json_encode([
                'status' => 'cooldown',
                'message' => "Please wait {$remaining_time} seconds before requesting a new code.",
                'cooldown_remaining' => $remaining_time
            ]);
            exit;
        }
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);
        
        $userId = $_SESSION['user']['id'];
        $email = $_SESSION['user']['email'] ?? '';

        if (!empty($email)) {
            $otp_sent = $auth->generateAndSendOtp($userId, $email);
            
            if ($otp_sent) {
                $_SESSION[$last_sent_key] = time();
                $message = 'A new OTP for password change has been sent.'; 
                $status = 'success';
            } else {
                $message = 'Failed to resend OTP. Check system logs.';
                $status = 'error';
            }
        } else {
            $message = 'Error: Email missing.';
            $status = 'error';
        }
        
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    }
    
    /**
     * MODIFIED: Handles the request to toggle 2FA on or off.
     * Initiates OTP flow if DISABLING 2FA.
     */
    public function toggleTwoFA() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);

        $userId = $_SESSION['user']['id'];
        $currentTwoFA = $_SESSION['user']['two_fa'] ?? 0;
        
        // Determine the target action
        $targetTwoFA = (int)($_POST['target_two_fa'] ?? 0); 
        
        // SCENARIO 1: ENABLING 2FA (No OTP needed)
        if ($targetTwoFA == 1 && $currentTwoFA == 0) {
            $email = $_SESSION['user']['email'] ?? '';
            if (empty($email)) {
                 echo json_encode(['status' => 'error', 'message' => 'Cannot enable 2FA: Your account must have a registered email address.']);
                 exit;
            }
            
            $auth->updateTwoFA($userId, 1); 
            log_action('INFO', '2FA_ENABLED', "User #{$userId} successfully enabled 2FA.");
            echo json_encode([
                'status' => 'success', 
                'message' => 'Two-Factor Authentication has been successfully enabled.'
            ]);
            exit;
        } 
        
        // SCENARIO 2: DISABLING 2FA (OTP Required)
        if ($targetTwoFA == 0 && $currentTwoFA == 1) {
            $email = $_SESSION['user']['email'] ?? '';

            // Check Cooldown
            $cooldown_duration = 60; 
            $last_sent_time = $_SESSION['otp_last_sent'] ?? 0;
            $time_since_last_sent = time() - $last_sent_time;

            // Check if user is in a pending state and still in cooldown
            if ($time_since_last_sent < $cooldown_duration && isset($_SESSION['2fa_toggle_pending'])) {
                $remaining_time = $cooldown_duration - $time_since_last_sent;
                echo json_encode([
                    'status' => 'cooldown',
                    'message' => "Please wait {$remaining_time} seconds before requesting a new code.",
                    'cooldown_remaining' => $remaining_time
                ]);
                exit;
            }

            // Generate and send OTP
            $otp_sent = $auth->generateAndSendOtp($userId, $email);

            if ($otp_sent) {
                $_SESSION['2fa_toggle_pending'] = true; // Set flag to allow verification later
                $_SESSION['otp_last_sent'] = time(); // Set cooldown time
                log_action('INFO', '2FA_DISABLE_OTP_SENT', "OTP sent to user #{$userId} to confirm 2FA disablement.");
                
                echo json_encode([
                    'status' => 'otp_required', 
                    'message' => 'A One-Time Password has been sent to your email to confirm disabling 2FA.'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Failed to send OTP email. Check system configuration.'
                ]);
                exit;
            }
        }
        
        // SCENARIO 3: NO CHANGE OR INVALID REQUEST
        $action = $targetTwoFA ? '2FA_ENABLED' : '2FA_DISABLED';
        log_action('INFO', $action, "User toggled 2FA status to: " . ($targetTwoFA ? 'Enabled' : 'Disabled'));
        echo json_encode(['status' => 'success', 'message' => 'Two-Factor Authentication preference updated.']);
        exit;
    }
    
    /**
     * NEW: Verifies the OTP provided to complete the 2FA disabling process.
     */
    public function verifyTwoFAToggleOtp() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
             echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
            exit;
        }

        if (!isset($_SESSION['2fa_toggle_pending']) || !$_SESSION['2fa_toggle_pending']) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session for OTP verification. Please try toggling 2FA again.']);
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);

        $userId = $_SESSION['user']['id'];
        $submittedOtp = trim($_POST['otp'] ?? '');

        $result = $auth->verifyOtpForToggle($userId, $submittedOtp);

        if ($result['success']) {
            // OTP is correct. Proceed to disable 2FA.
            $auth->updateTwoFA($userId, 0); // Disable 2FA (set to 0)
            log_action('INFO', '2FA_DISABLED_OTP', "User #{$userId} successfully disabled 2FA using OTP.");
            
            unset($_SESSION['2fa_toggle_pending']); // Clear flag
            unset($_SESSION['otp_last_sent']); // Clear cooldown
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Two-Factor Authentication has been successfully disabled.'
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => $result['message']
            ]);
            exit;
        }
    }

    public function updateTheme() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $theme = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $userId = $_SESSION['user']['id'];
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->updateTheme($userId, $theme);
        echo json_encode(['status' => 'success', 'theme' => $theme]);
        exit;
    }
}