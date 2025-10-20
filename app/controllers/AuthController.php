<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../core/Auth.php';

class AuthController {

    public function showLoginForm() {
        $data = [
            'error' => '',
            'usernameValue' => ''
        ];
        view('auth/login', $data);
    }

    public function login() {
        header('Content-Type: application/json');

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
             echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
             exit;
        }

        $last_logout = $_SESSION['last_logout'] ?? null;

        $result = $auth->login($username, $password);
        $base_url = '/iCensus-ent/public';

        if ($result['success']) {
            if ($last_logout) {
                $_SESSION['user']['last_log_view'] = $last_logout;
            }

            $role = $_SESSION['user']['role_name'];
            
            if ($role == 'System Admin') $redirect_to = $base_url . '/sysadmin/dashboard';
            elseif ($role == 'Barangay Admin') $redirect_to = $base_url . '/dashboard';
            elseif ($role == 'Encoder') $redirect_to = $base_url . '/encoder-dashboard';
            else $redirect_to = $base_url . '/login';

            echo json_encode(['status' => 'success', 'redirect' => $redirect_to]);
            exit;
        } elseif (isset($result['message']) && $result['message'] === '2FA_REQUIRED') {
             // Return 2FA required status
             echo json_encode(['status' => '2fa_required']);
             exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Invalid credentials']);
            exit;
        }
    }
    
    public function verifyOtp() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['2fa_required']) || !$_SESSION['2fa_required'] || !isset($_SESSION['2fa_user_id'])) {
            echo json_encode(['status' => 'error', 'message' => '2FA session expired. Please log in again.']);
            exit;
        }
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);
        
        $userId = $_SESSION['2fa_user_id'];
        $submittedOtp = trim($_POST['otp'] ?? '');
        
        $result = $auth->verifyOtp($userId, $submittedOtp);
        $base_url = '/iCensus-ent/public';

        if ($result['success']) {
            unset($_SESSION['2fa_required']);
            unset($_SESSION['2fa_user_id']);
            // NEW: Clean up cooldown session variables on successful login
            unset($_SESSION['otp_last_sent']); 
            
            $role = $_SESSION['user']['role_name'];
            
            if ($role == 'System Admin') $redirect_to = $base_url . '/sysadmin/dashboard';
            elseif ($role == 'Barangay Admin') $redirect_to = $base_url . '/dashboard';
            elseif ($role == 'Encoder') $redirect_to = $base_url . '/encoder-dashboard';
            else $redirect_to = $base_url . '/login';

            echo json_encode(['status' => 'success', 'redirect' => $redirect_to]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Invalid OTP.']);
            exit;
        }
    }
    
    /**
     * Handles OTP resend via an AJAX call with cooldown enforcement.
     */
    public function resendOtp() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['2fa_required']) || !$_SESSION['2fa_required'] || !isset($_SESSION['2fa_user_id'])) {
            echo json_encode(['status' => 'error', 'message' => '2FA session expired. Please log in again.']);
            exit;
        }

        // --- NEW: Cooldown Check (60 seconds) ---
        $cooldown_duration = 60; 
        $last_sent_time = $_SESSION['otp_last_sent'] ?? 0;
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
        // --- END Cooldown Check ---
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);
        
        $userId = $_SESSION['2fa_user_id'];
        
        $user_data = $auth->refreshUserSession($userId);

        if (!empty($user_data['email'])) {
            $otp_sent = $auth->generateAndSendOtp($userId, $user_data['email']);
            
            if ($otp_sent) {
                // SUCCESS: Update the session variable after successful sending
                $_SESSION['otp_last_sent'] = time();
                $message = 'A new OTP has been sent. Check your email.'; 
                $status = 'success';
            } else {
                $message = 'Failed to resend OTP. Check system logs.';
                $status = 'error';
            }
        } else {
            $message = 'Cannot resend: No email address registered for this account.';
            $status = 'error';
        }
        
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    }

    public function showOtpVerificationForm() {
        header("Location: /iCensus-ent/public/login");
        exit;
    }

    public function logout() {
        $config = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../../core/Database.php';
        require_once __DIR__ . '/../../core/functions.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;

        if (isset($_SESSION['user'])) {
            log_action('INFO', 'USER_LOGOUT', "User '" . $_SESSION['user']['username'] . "' logged out.");
        }
        
        // NEW: Clean up OTP session variables on logout
        unset($_SESSION['2fa_required']);
        unset($_SESSION['2fa_user_id']);
        unset($_SESSION['otp_last_sent']);
        
        $last_logout_time = date('Y-m-d H:i:s');
        
        session_unset();
        session_destroy();

        session_start();
        $_SESSION['last_logout'] = $last_logout_time;

        header("Location: /iCensus-ent/public/login");
        exit;
    }
}