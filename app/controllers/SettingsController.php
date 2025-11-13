<?php
// app/controllers/SettingsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/Email.php';

class SettingsController {

    private $db;
    private $auth;

    private function checkAuthAndInit() {
        if (!isset($_SESSION['user'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            } else {
                header('Location: /iCensus-ent/public/login');
            }
            exit;
        }
        
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new Database($config);
        $GLOBALS['db'] = $this->db; // For global log_action
        $this->auth = new Auth($this->db);
    }

    public function index() {
        $this->checkAuthAndInit();
        $this->auth->refreshUserSession($_SESSION['user']['id']);

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);

        view('settings/index', $data);
    }

    public function updateUsername() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');
        
        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------
        
        $userId = $_SESSION['user']['id'];
        $oldUsername = $_SESSION['user']['username'];
        $newUsername = $_POST['username'];

        if ($oldUsername !== $newUsername) {
            $this->auth->updateUsername($userId, $newUsername);
            log_action('INFO', 'SETTINGS_UPDATE', "User updated their username from '{$oldUsername}' to '{$newUsername}'.");
            echo json_encode(['status' => 'success', 'message' => 'Username updated successfully']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Username is the same, no changes made.']);
        }
        exit;
    }

    public function requestBindEmailOtp() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------

        $userId = $_SESSION['user']['id'];
        $oldEmail = $_SESSION['user']['email'] ?? '';
        $newEmail = trim($_POST['email']);
        
        if ($oldEmail === $newEmail) {
            echo json_encode(['status' => 'success', 'message' => 'Email is the same, no changes made.']);
            exit;
        }

        if ($_SESSION['user']['two_fa'] == 1) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You must disable Two-Factor Authentication before changing your email.']);
            exit;
        }

        $cooldown_duration = 60;
        $last_sent_key = 'bind_otp_last_sent';
        $last_sent_time = $_SESSION[$last_sent_key] ?? 0;
        $time_since_last_sent = time() - $last_sent_time;

        if ($time_since_last_sent < $cooldown_duration) {
            $remaining_time = $cooldown_duration - $time_since_last_sent;
            echo json_encode(['status' => 'cooldown', 'message' => "Please wait {$remaining_time} seconds.", 'cooldown_remaining' => $remaining_time]);
            exit;
        }

        $otp = random_int(100000, 999999);
        $emailService = new Email();
        $sent = $emailService->sendOtp($newEmail, $otp);

        if ($sent) {
            $_SESSION['bind_otp_hash'] = password_hash((string)$otp, PASSWORD_DEFAULT);
            $_SESSION['bind_otp_expires'] = time() + 300;
            $_SESSION['bind_otp_new_email'] = $newEmail;
            $_SESSION[$last_sent_key] = time();

            log_action('INFO', 'EMAIL_BIND_OTP_SENT', "OTP sent to new email '{$newEmail}' for user ID #{$userId}.");
            echo json_encode(['status' => 'otp_required', 'message' => 'An OTP has been sent to your new email address.']);
        } else {
            log_action('ERROR', 'EMAIL_BIND_OTP_FAIL', "Failed to send OTP to '{$newEmail}' for user ID #{$userId}.");
            echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP to the new email. Check system logs.']);
        }
        exit;
    }

    public function confirmBindEmail() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------

        $userId = $_SESSION['user']['id'];
        $submittedOtp = trim($_POST['otp'] ?? '');

        if (!isset($_SESSION['bind_otp_hash'], $_SESSION['bind_otp_expires'], $_SESSION['bind_otp_new_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please try saving your email again.']);
            exit;
        }

        if (time() > $_SESSION['bind_otp_expires']) {
            echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.']);
            exit;
        }

        if (password_verify($submittedOtp, $_SESSION['bind_otp_hash'])) {
            $newEmail = $_SESSION['bind_otp_new_email'];
            $oldEmail = $_SESSION['user']['email'] ?? 'none';
            
            $stmt = $this->db->getPdo()->prepare("UPDATE users SET email=? WHERE id=?");
            $stmt->execute([$newEmail, $userId]);
            
            $this->auth->refreshUserSession($userId);
            log_action('INFO', 'EMAIL_BIND_SUCCESS', "User #{$userId} confirmed new email '{$newEmail}' (was '{$oldEmail}').");

            unset($_SESSION['bind_otp_hash'], $_SESSION['bind_otp_expires'], $_SESSION['bind_otp_new_email'], $_SESSION['bind_otp_last_sent']);

            echo json_encode(['status' => 'success', 'message' => 'Email updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code.']);
        }
        exit;
    }

    public function resendBindEmailOtp() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        if (!isset($_SESSION['bind_otp_new_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please try saving your email again.']);
            exit;
        }

        $cooldown_duration = 60;
        $last_sent_key = 'bind_otp_last_sent';
        $last_sent_time = $_SESSION[$last_sent_key] ?? 0;
        $time_since_last_sent = time() - $last_sent_time;

        if ($time_since_last_sent < $cooldown_duration) {
            $remaining_time = $cooldown_duration - $time_since_last_sent;
            echo json_encode(['status' => 'cooldown', 'message' => "Please wait {$remaining_time} seconds.", 'cooldown_remaining' => $remaining_time]);
            exit;
        }

        $newEmail = $_SESSION['bind_otp_new_email'];
        $otp = random_int(100000, 999999);
        $emailService = new Email();
        $sent = $emailService->sendOtp($newEmail, $otp);

        if ($sent) {
            $_SESSION['bind_otp_hash'] = password_hash((string)$otp, PASSWORD_DEFAULT);
            $_SESSION['bind_otp_expires'] = time() + 300;
            $_SESSION[$last_sent_key] = time();
            echo json_encode(['status' => 'success', 'message' => 'A new code has been sent.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to resend OTP.']);
        }
        exit;
    }

    public function updatePassword() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------

        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'];
        $newPassword = $_POST['password'];
        $submittedOtp = $_POST['otp'] ?? '';

        try {
            if ($role === 'System Admin') {
                if (!isset($_SESSION['password_change_otp_required']) || !$_SESSION['password_change_otp_required']) {
                    throw new Exception('OTP session expired. Please verify current password again.', 403);
                }
                if (empty($submittedOtp)) {
                    throw new Exception('OTP is required to change password.', 400);
                }

                $otp_result = $this->auth->verifyOtp($userId, $submittedOtp);
                if (!$otp_result['success']) {
                    throw new Exception($otp_result['message'], 403);
                }

                unset($_SESSION['password_change_otp_required']);
                unset($_SESSION['password_change_otp_last_sent']);
            }

            $this->auth->updatePassword($userId, $newPassword);
            log_action('INFO', 'SETTINGS_UPDATE', "User changed their password." . ($role === 'System Admin' ? " (OTP verified)" : ""));
            
            echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);

        } catch (Exception $e) {
            log_action('ERROR', 'SETTINGS_ERROR', $e->getMessage());
            http_response_code($e->getCode() > 0 ? $e->getCode() : 500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function verifyPassword() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'];
        $currentPassword = $_POST['current_password'] ?? '';

        if (!$this->auth->verifyPassword($userId, $currentPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }

        if ($role === 'System Admin') {
            $email = $_SESSION['user']['email'] ?? '';
            if (empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'System Admin requires an email address set in settings for password changes.']);
                exit;
            }
            
            $otp_sent = $this->auth->generateAndSendOtp($userId, $email);
            
            if (!$otp_sent) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP for password change. Check system logs.']);
                exit;
            }
            
            $_SESSION['password_change_otp_required'] = true;
            $_SESSION['password_change_otp_last_sent'] = time();
            
            echo json_encode(['status' => 'otp_sent', 'message' => 'Current password verified. OTP sent to email.']);
            exit;
        }

        echo json_encode(['status' => 'success', 'message' => 'Password verified.']);
        exit;
    }
    
    public function resendPasswordChangeOtp() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        if (!isset($_SESSION['password_change_otp_required']) || !$_SESSION['password_change_otp_required']) {
            echo json_encode(['status' => 'error', 'message' => 'Password change session expired. Please verify current password again.']);
            exit;
        }

        $cooldown_duration = 60; 
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
        
        $userId = $_SESSION['user']['id'];
        $email = $_SESSION['user']['email'] ?? '';

        if (!empty($email)) {
            $otp_sent = $this->auth->generateAndSendOtp($userId, $email);
            
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
    
    public function toggleTwoFA() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['id'];
        $currentTwoFA = $_SESSION['user']['two_fa'] ?? 0;
        $targetTwoFA = (int)($_POST['target_two_fa'] ?? 0); 
        
        if ($targetTwoFA == 1 && $currentTwoFA == 0) {
            $email = $_SESSION['user']['email'] ?? '';
            if (empty($email)) {
                 echo json_encode(['status' => 'error', 'message' => 'Cannot enable 2FA: Your account must have a registered email address.']);
                 exit;
            }
            
            $this->auth->updateTwoFA($userId, 1); 
            log_action('INFO', '2FA_ENABLED', "User #{$userId} successfully enabled 2FA.");
            echo json_encode(['status' => 'success', 'message' => 'Two-Factor Authentication has been successfully enabled.']);
            exit;
        } 
        
        if ($targetTwoFA == 0 && $currentTwoFA == 1) {
            $email = $_SESSION['user']['email'] ?? '';
            $cooldown_duration = 60; 
            $last_sent_time = $_SESSION['otp_last_sent'] ?? 0;
            $time_since_last_sent = time() - $last_sent_time;

            if ($time_since_last_sent < $cooldown_duration && isset($_SESSION['2fa_toggle_pending'])) {
                $remaining_time = $cooldown_duration - $time_since_last_sent;
                echo json_encode(['status' => 'cooldown', 'message' => "Please wait {$remaining_time} seconds.", 'cooldown_remaining' => $remaining_time]);
                exit;
            }

            $otp_sent = $this->auth->generateAndSendOtp($userId, $email);

            if ($otp_sent) {
                $_SESSION['2fa_toggle_pending'] = true;
                $_SESSION['otp_last_sent'] = time();
                log_action('INFO', '2FA_DISABLE_OTP_SENT', "OTP sent to user #{$userId} to confirm 2FA disablement.");
                echo json_encode(['status' => 'otp_required', 'message' => 'An OTP has been sent to your email to confirm disabling 2FA.']);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP email.']);
                exit;
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'No change in 2FA status.']);
        exit;
    }
    
    public function verifyTwoFAToggleOtp() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------

        if (!isset($_SESSION['2fa_toggle_pending']) || !$_SESSION['2fa_toggle_pending']) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session for OTP verification.']);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $submittedOtp = trim($_POST['otp'] ?? '');
        $result = $this->auth->verifyOtpForToggle($userId, $submittedOtp);

        if ($result['success']) {
            $this->auth->updateTwoFA($userId, 0); // Disable 2FA
            log_action('INFO', '2FA_DISABLED_OTP', "User #{$userId} successfully disabled 2FA using OTP.");
            
            unset($_SESSION['2fa_toggle_pending']);
            unset($_SESSION['otp_last_sent']);
            
            echo json_encode(['status' => 'success', 'message' => 'Two-Factor Authentication has been successfully disabled.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            exit;
        }
    }

    public function requestUnbindEmailOtp() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');
        
        if ($_SESSION['user']['two_fa'] == 1) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You must disable Two-Factor Authentication before removing your email.']);
            exit;
        }

        $cooldown_duration = 60;
        $last_sent_key = 'unbind_otp_last_sent';
        $last_sent_time = $_SESSION[$last_sent_key] ?? 0;
        $time_since_last_sent = time() - $last_sent_time;

        if ($time_since_last_sent < $cooldown_duration) {
            $remaining_time = $cooldown_duration - $time_since_last_sent;
            echo json_encode(['status' => 'cooldown', 'message' => "Please wait {$remaining_time} seconds.", 'cooldown_remaining' => $remaining_time]);
            exit;
        }
        
        $userId = $_SESSION['user']['id'];
        $email = $_SESSION['user']['email'] ?? '';

        if (!empty($email)) {
            $otp_sent = $this->auth->generateAndSendOtp($userId, $email);
            
            if ($otp_sent) {
                $_SESSION[$last_sent_key] = time();
                $_SESSION['unbind_otp_pending'] = true;
                $message = 'An OTP has been sent to your email to confirm removal.'; 
                $status = 'success';
            } else {
                $message = 'Failed to send OTP. Check system logs.';
                $status = 'error';
            }
        } else {
            $message = 'Error: No email to unbind.';
            $status = 'error';
        }
        
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    }

    public function confirmUnbindEmail() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');

        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Error. Reload page.']);
            exit;
        }
        // -----------------------

        if (!isset($_SESSION['unbind_otp_pending']) || !$_SESSION['unbind_otp_pending']) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please request a new OTP.']);
            exit;
        }
        
        $userId = $_SESSION['user']['id'];
        $submittedOtp = trim($_POST['otp'] ?? '');
        $result = $this->auth->verifyOtpForToggle($userId, $submittedOtp);

        if ($result['success']) {
            $currentEmail = $_SESSION['user']['email'] ?? 'none';
            $stmt = $this->db->getPdo()->prepare("UPDATE users SET email = NULL WHERE id = ?");
            $stmt->execute([$userId]);
            $this->auth->refreshUserSession($userId);
            log_action('INFO', 'SETTINGS_UPDATE', "User removed their email address via OTP. (Was: '{$currentEmail}')");
            
            unset($_SESSION['unbind_otp_pending']);
            unset($_SESSION['unbind_otp_last_sent']);
            
            echo json_encode(['status' => 'success', 'message' => 'Email address removed successfully.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            exit;
        }
    }

    public function updateTheme() {
        $this->checkAuthAndInit();
        header('Content-Type: application/json');
        $theme = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $userId = $_SESSION['user']['id'];
        $this->auth->updateTheme($userId, $theme);
        echo json_encode(['status' => 'success', 'theme' => $theme]);
        exit;
    }
}