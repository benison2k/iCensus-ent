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
            'email' => $user['email'] ?? null,
            // UPDATED: Persist sidebar preference
            'sidebar_pinned' => $user['sidebar_pinned'] ?? 0 
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
    
    /**
     * Verifies the submitted OTP against the stored hash and expiration for a non-login action (like 2FA toggle).
     */
    public function verifyOtpForToggle($userId, $submittedOtp) {
        $stmt = $this->pdo->prepare("SELECT otp, otp_expires_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Check if OTP exists and is not expired
        if (empty($user['otp']) || time() > strtotime($user['otp_expires_at'])) {
            // Clear expired token fields
            $this->pdo->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE id = ?")->execute([$userId]);
            return ['success' => false, 'message' => 'OTP expired or not set.'];
        }

        // Verify the code
        if (password_verify($submittedOtp, $user['otp'])) {
            // Success: Clear the used OTP immediately, but DO NOT log the user in.
            $clearStmt = $this->pdo->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE id = ?");
            $clearStmt->execute([$userId]);
            
            log_action('INFO', 'OTP_VERIFY_SUCCESS', "OTP verified for 2FA toggle action for user ID #{$userId}.");
            
            return ['success' => true, 'message' => 'OTP verified successfully.'];
        }

        return ['success' => false, 'message' => 'Invalid OTP code.'];
    }


    /**
     * Finds user by email, generates token, saves to DB, and sends reset email.
     */
    public function sendPasswordResetLink($email) {
        global $db; 
        
        $stmt = $this->pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['email'])) {
            log_action('WARNING', 'PASSWORD_RESET_FAIL', "Attempted password reset for non-existent or no-email user: '" . htmlspecialchars($email) . "'");
            return true; // Return true for security to prevent email enumeration
        }

        // Generate token (hashed version stored in DB, raw version used for link)
        $rawToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

        // Save token and expiration to DB
        $updateStmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $updateStmt->execute([$hashedToken, $expiresAt, $user['id']]);

        // Send email with the link
        $resetLink = BASE_URL . '/password/reset?token=' . $rawToken . '&email=' . urlencode($user['email']);
        $emailService = new Email();
        $sent = $emailService->sendPasswordReset($user['email'], $user['username'], $resetLink);

        if ($sent) {
            log_action('INFO', 'PASSWORD_RESET_SENT', "Password reset link sent to user: " . htmlspecialchars($user['email']) . ".");
        } else {
            log_action('ERROR', 'PASSWORD_RESET_FAIL', "Failed to send password reset email to: " . htmlspecialchars($user['email']) . ".");
        }

        return $sent;
    }
    
    /**
     * Verifies the token and updates the user's password.
     */
    public function resetPassword($email, $token, $newPassword) {
        $hashedToken = hash('sha256', $token);
        
        $stmt = $this->pdo->prepare("
            SELECT id, username, reset_token_expires_at 
            FROM users 
            WHERE email = ? AND reset_token = ?
        ");
        $stmt->execute([$email, $hashedToken]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired link.'];
        }

        if (time() > strtotime($user['reset_token_expires_at'])) {
            // Clear the expired token to prevent reuse
            $this->pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?")->execute([$user['id']]);
            return ['success' => false, 'message' => 'The reset link has expired. Please request a new one.'];
        }

        // Update password and clear token fields
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $this->pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $user['id']]);

        log_action('INFO', 'PASSWORD_RESET_SUCCESS', "User '" . $user['username'] . "' successfully reset their password via token.");
        
        return ['success' => true, 'message' => 'Your password has been reset successfully.'];
    }
    // ... existing refreshUserSession, updateUsername, etc. methods ...

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
            
            // --- NEW: Failsafe to ensure 2FA is off if email is null ---
            if (is_null($user['email']) && $user['two_fa'] == 1) {
                $this->updateTwoFA($userId, 0); // Directly call method to disable it
                $user['two_fa'] = 0; // Update local copy
                log_action('WARNING', '2FA_FAILSAFE', "2FA disabled for user ID #{$userId} because email was NULL.");
            }
            // --- END NEW ---

            // Update session if user is currently logged in, otherwise just return data
            if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $userId) {
                $_SESSION['user']['username'] = $user['username'];
                $_SESSION['user']['role_name'] = $user['role_name'];
                $_SESSION['user']['theme'] = $user['theme'] ?? 'light';
                $_SESSION['user']['language'] = $user['language'] ?? 'en';
                $_SESSION['user']['two_fa'] = $user['two_fa'] ?? 0;
                $_SESSION['user']['email'] = $user['email'] ?? null;
                // UPDATED: Persist sidebar preference
                $_SESSION['user']['sidebar_pinned'] = $user['sidebar_pinned'] ?? 0;
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