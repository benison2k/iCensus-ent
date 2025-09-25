<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

// --- Bouncer: Only System Admins are allowed ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'System Admin') {
    $_SESSION['modal'] = ['message' => 'You do not have permission for this action.', 'type' => 'error'];
    header("Location: ../pages/login.php");
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();
$action = $_GET['action'] ?? $_POST['action'] ?? 'save';

try {
    switch($action) {
        case 'get':
            header('Content-Type: application/json');
            $user_id = $_GET['user_id'] ?? 0;
            $stmt = $pdo->prepare("SELECT id, username, full_name, role_id FROM users WHERE id=?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user ? ['status'=>'success', 'user'=>$user] : ['status'=>'error', 'message'=>'User not found']);
            break;

        case 'delete':
            $user_id = $_POST['user_id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$user_id]);
            $_SESSION['modal'] = ['message'=>'User deleted successfully', 'type'=>'success'];
            header("Location: ../sysadmin/manage_users.php");
            break;

        case 'save':
        default:
            $user_id = $_POST['user_id'] ?? '';
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']);
            $role_id = $_POST['role_id'];
            $password = $_POST['password'];

            if (empty($username) || empty($full_name) || empty($role_id)) {
                throw new Exception("All fields except password are required.");
            }

            if (empty($user_id)) { // Add New User
                if (empty($password)) throw new Exception("A password is required for new users.");
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, full_name, role_id, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $full_name, $role_id, $hashed_password]);
                $_SESSION['modal'] = ['message'=>'User added successfully', 'type'=>'success'];
            } else { // Update Existing User
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=?, password=? WHERE id=?");
                    $stmt->execute([$username, $full_name, $role_id, $hashed_password, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, role_id=? WHERE id=?");
                    $stmt->execute([$username, $full_name, $role_id, $user_id]);
                }
                $_SESSION['modal'] = ['message'=>'User updated successfully', 'type'=>'success'];
            }
            header("Location: ../sysadmin/manage_users.php");
            break;
    }
} catch (Exception $e) {
    $_SESSION['modal'] = ['message' => 'An error occurred: ' . $e->getMessage(), 'type' => 'error'];
    header("Location: ../sysadmin/manage_users.php");
}
exit;