<?php
class Auth {
    private $pdo;

    // Constructor receives Database instance
    public function __construct($db) {
        $this->pdo = $db->getPdo();
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            die("❌ Username not found: " . htmlspecialchars($username));
        }

        if (!password_verify($password, $user['password'])) {
            die("❌ Password mismatch for user: " . htmlspecialchars($username));
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role_id' => $user['role_id'],
            'full_name' => $user['full_name']
        ];
        return true;
    }
}
