<?php
// At the very top of the file, include the new functions file
require_once __DIR__ . '/functions.php';
// NEW: Include Email class
require_once __DIR__ . '/Email.php';

class Auth {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * Attempt login and return structured result
     */
    public function login($username, $password) {
        global $db; 

        $stmt = $this->pdo->prepare("
            SELECT users.*, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE users.username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If login fails, log the event and then return the error
        if (!$user || !password_verify($password, $user['password'])) {
            log_action('WARNING', 'USER_LOGIN_FAIL', "Failed login attempt for username: '" . htmlspecialchars($username) . "'");
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // --- NEW: Check for 2FA ---
        if ($user['two_fa'] == 1 && !empty($user['email'])) {
            $otp_sent = $this->generateAndSendOtp($user['id'], $user['email']);
            
            // Store user ID for the next verification step
            $_SESSION['2fa_user_id'] = $user['id'];
            $_SESSION['2fa_required'] = true;
            
            if (!$otp_sent) {
                return ['success' => false, 'message' => 'OTP required, but failed to send email. Check system logs for details.'];
            }

            // --- CRITICAL ADDITION: Set last sent time on successful initial send ---
            $_SESSION['otp_last_sent'] = time();

            return ['success' => false, 'message' => '2FA_REQUIRED'];
        }

        // Successful login (no 2FA or 2FA disabled)
        $this->setUserSession($user);
        log_action('INFO', 'USER_LOGIN_SUCCESS', "User '" . htmlspecialchars($username) . "' logged in successfully.");

        return ['success' => true, 'message' => null];
    }
    
    // --- Helper to set complete session data ---
    private function setUserSession($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'full_name' => $user['full_name'],
            'theme' => $user['theme'] ?? 'light',
            'language' => $user['language'] ?? 'en',
            'two_fa' => $user['two_fa'] ?? 0,
            'email' => $user['email'] ?? null
        ];
        // Ensure LAST_ACTIVITY is set for session timeout
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    
    /**
     * Generates, saves, and sends OTP.
     */
    public function generateAndSendOtp($userId, $email) {
        // Generate a 6-digit random code
        $otp = random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes expiration
        
        // Hash the OTP before storing it for security
        $hashedOtp = password_hash((string)$otp, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?");
        $stmt->execute([$hashedOtp, $expiresAt, $userId]);
        
        $emailService = new Email();
        $sent = $emailService->sendOtp($email, $otp);
        
        if ($sent) {
            log_action('INFO', 'OTP_SENT', "OTP successfully sent to user email: " . htmlspecialchars($email) . ".");
        }
        
        return $sent;
    }
    
    /**
     * Verifies the submitted OTP against the stored hash and expiration.
     */
    public function verifyOtp($userId, $submittedOtp) {
        $stmt = $this->pdo->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE users.id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Check if OTP exists and is not expired
        if (empty($user['otp']) || time() > strtotime($user['otp_expires_at'])) {
            return ['success' => false, 'message' => 'OTP expired or not set. Please log in again to receive a new one.'];
        }

        // Verify the code
        if (password_verify($submittedOtp, $user['otp'])) {
            // Clear the used OTP immediately
            $clearStmt = $this->pdo->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE id = ?");
            $clearStmt->execute([$userId]);
            
            // Log in the user
            $this->setUserSession($user);
            log_action('INFO', 'USER_LOGIN_SUCCESS', "User '" . htmlspecialchars($user['username']) . "' logged in successfully via OTP.");
            
            return ['success' => true, 'message' => null];
        }

        return ['success' => false, 'message' => 'Invalid OTP code.'];
    }
    
    public function refreshUserSession($userId) {
        $stmt = $this->pdo->prepare("
            SELECT users.*, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE users.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Update session if user is currently logged in, otherwise just return data
            if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $userId) {
                $_SESSION['user']['username'] = $user['username'];
                $_SESSION['user']['role_name'] = $user['role_name'];
                $_SESSION['user']['theme'] = $user['theme'] ?? 'light';
                $_SESSION['user']['language'] = $user['language'] ?? 'en';
                $_SESSION['user']['two_fa'] = $user['two_fa'] ?? 0;
                $_SESSION['user']['email'] = $user['email'] ?? null;
            }
            return $user;
        }
        return false;
    }

    public function updateUsername($userId, $username) {
        $stmt = $this->pdo->prepare("UPDATE users SET username=? WHERE id=?");
        $stmt->execute([$username, $userId]);
        $this->refreshUserSession($userId);
    }

    public function updatePassword($userId, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hashed, $userId]);
    }

    public function updateTwoFA($userId, $twoFA) {
        $stmt = $this->pdo->prepare("UPDATE users SET two_fa=? WHERE id=?");
        $stmt->execute([$twoFA, $userId]);
        $this->refreshUserSession($userId);
    }

    public function verifyPassword($userId, $password) {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || empty($user['password'])) return false;

        return password_verify((string)$password, (string)$user['password']);
    }

    public function updateTheme($userId, $theme) {
        $stmt = $this->pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $userId]);
        $this->refreshUserSession($userId);
    }
}