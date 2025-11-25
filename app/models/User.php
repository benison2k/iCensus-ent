<?php
// app/models/User.php

class User {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, username FROM users ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function getPaginatedManageableUsers($page = 1, $pageSize = 10) {
        $offset = ($page - 1) * $pageSize;

        // Get total count
        $countStmt = $this->pdo->query("
            SELECT COUNT(users.id) 
            FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE roles.role_name != 'System Admin'
        ");
        $totalUsers = $countStmt->fetchColumn();

        // Get paginated results
        $stmt = $this->pdo->prepare("
            SELECT users.id, users.username, users.full_name, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE roles.role_name != 'System Admin' 
            ORDER BY users.id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int) $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'users' => $users,
            'total' => $totalUsers,
            'totalPages' => ceil($totalUsers / $pageSize)
        ];
    }

    public function getAssignableRoles() {
        $stmt = $this->pdo->query("SELECT id, role_name FROM roles WHERE role_name != 'System Admin'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        // UPDATED: Added sidebar_pinned to the selection
        $stmt = $this->pdo->prepare("SELECT id, username, full_name, role_id, sidebar_pinned FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save($data) {
        if (empty($data['user_id'])) { // Create new user
            if (empty($data['password'])) throw new Exception("Password is required for new users.");
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, full_name, role_id, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $hashed_password]);
            return $this->pdo->lastInsertId();
        } else { // Update existing user
            if (!empty($data['password'])) {
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=?, password=? WHERE id=?");
                $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $hashed_password, $data['user_id']]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=? WHERE id=?");
                $stmt->execute([$data['username'], $data['full_name'], $data['role_id'], $data['user_id']]);
            }
            return $data['user_id'];
        }
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}