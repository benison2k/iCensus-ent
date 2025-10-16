<?php
// At the very top of the file, include the new functions file
require_once __DIR__ . '/functions.php';

class Auth {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * Attempt login and return structured result
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string|null]
     */
    public function login($username, $password) {
        // Make the global $db object available for our logging function
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
            // UPDATED: Log the failed login attempt
            log_action('WARNING', 'USER_LOGIN_FAIL', "Failed login attempt for username: '" . htmlspecialchars($username) . "'");
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        // Successful login
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'full_name' => $user['full_name'],
            'theme' => $user['theme'] ?? 'light',
            'language' => $user['language'] ?? 'en',
            'two_fa' => $user['two_fa'] ?? 0
        ];

        // --- BUG FIX: Add this line to initialize the session timer ---
        $_SESSION['LAST_ACTIVITY'] = time();

        // UPDATED: Log the successful login event after setting the session
        log_action('INFO', 'USER_LOGIN_SUCCESS', "User '" . htmlspecialchars($username) . "' logged in successfully.");

        return ['success' => true, 'message' => null];
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
            $_SESSION['user']['username'] = $user['username'];
            $_SESSION['user']['role_name'] = $user['role_name'];
            $_SESSION['user']['theme'] = $user['theme'] ?? 'light';
            $_SESSION['user']['language'] = $user['language'] ?? 'en';
            $_SESSION['user']['two_fa'] = $user['two_fa'] ?? 0;
        }
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