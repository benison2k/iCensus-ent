<?php
class Auth {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db->getPdo();
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) die("❌ Username not found.");
        if (!password_verify($password, $user['password'])) die("❌ Password mismatch.");

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role_id' => $user['role_id'],
            'full_name' => $user['full_name'],
            'theme' => $user['theme'] ?? 'light',
            'language' => $user['language'] ?? 'en',
            'two_fa' => $user['two_fa'] ?? 0
        ];
        return true;
    }

    public function refreshUserSession($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user']['username'] = $user['username'];
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
}
