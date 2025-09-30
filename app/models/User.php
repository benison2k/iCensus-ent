<?php
// /app/models/User.php

class User {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    // Gets all users EXCEPT System Admins
    public function getManageableUsers() {
        $stmt = $this->pdo->query("
            SELECT users.id, users.username, users.full_name, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE roles.role_name != 'System Admin' 
            ORDER BY users.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Gets all roles that can be assigned
    public function getAssignableRoles() {
        $stmt = $this->pdo->query("SELECT id, role_name FROM roles WHERE role_name != 'System Admin'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT id, username, full_name, role_id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save($data) {
        if (empty($data['user_id'])) { // Create new user
            if (empty($data['password'])) throw new Exception("Password is required for new users.");
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, full_name, role_id, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $hashed_password]);
        } else { // Update existing user
            if (!empty($data['password'])) {
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=?, password=? WHERE id=?");
                $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $hashed_password, $data['user_id']]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=? WHERE id=?");
                $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $data['user_id']]);
            }
        }
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}