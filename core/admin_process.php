<?php
session_start();
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

// --- Bouncer: Only Barangay Admins are allowed ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'Barangay Admin') {
    $_SESSION['modal'] = ['message' => 'You do not have permission for this action.', 'type' => 'error'];
    header("Location: ../pages/login.php");
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();
$action = $_POST['action'] ?? 'save'; // This form only uses POST

// Get the logged-in admin's barangay ID to scope all actions
$admin_barangay_id = $_SESSION['user']['barangay_id'];

try {
    switch($action) {
        case 'delete':
            $user_id = $_POST['user_id'] ?? 0;
            // Security Check: Ensure they are deleting an encoder from their own barangay
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND barangay_id = ? AND role_id = 3");
            $stmt->execute([$user_id, $admin_barangay_id]);
            $_SESSION['modal'] = ['message'=>'Encoder deleted successfully', 'type'=>'success'];
            break;

        case 'save':
        default:
            $user_id = $_POST['user_id'] ?? '';
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']);
            $password = $_POST['password'];
            $role_id = $_POST['role_id'];

            // Security Check: Enforce that Barangay Admins can ONLY create/edit Encoders (role_id 3)
            if ($role_id != 3) {
                throw new Exception("Invalid role specified. You can only manage Encoder accounts.");
            }

            if (empty($user_id)) { // Add New Encoder
                if (empty($password)) throw new Exception("A password is required for new users.");
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, full_name, role_id, password, barangay_id) VALUES (?, ?, ?, ?, ?)"
                );
                // Assign the new user to the admin's barangay
                $stmt->execute([$username, $full_name, $role_id, $hashed_password, $admin_barangay_id]);
                $_SESSION['modal'] = ['message'=>'Encoder added successfully', 'type'=>'success'];

            } else { // Update Existing Encoder
                // Security Check: Ensure they are editing an encoder from their own barangay
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, password=? WHERE id=? AND barangay_id=?");
                    $stmt->execute([$username, $full_name, $hashed_password, $user_id, $admin_barangay_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=? WHERE id=? AND barangay_id=?");
                    $stmt->execute([$username, $full_name, $user_id, $admin_barangay_id]);
                }
                $_SESSION['modal'] = ['message'=>'Encoder updated successfully', 'type'=>'success'];
            }
            break;
    }
} catch (Exception $e) {
    $_SESSION['modal'] = ['message' => 'An error occurred: ' . $e->getMessage(), 'type' => 'error'];
}

header("Location: ../admin/manage_users.php");
exit;