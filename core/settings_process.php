<?php
session_start();
require __DIR__ . '/Auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Load database config
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

// Initialize Database
$db = new Database($config);
$conn = $db->getPdo();

$userId = $_SESSION['user']['id'];

// ---------------------------
// Update Username Only
// ---------------------------
if (isset($_POST['update_username'])) {
    $username = trim($_POST['username']);

    if (!empty($username)) {
        $stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
        $stmt->execute([$username, $userId]);

        $_SESSION['user']['username'] = $username;
        $_SESSION['modal'] = ['message' => 'Username updated successfully!', 'type' => 'success'];
        header("Location: ../pages/settings.php");
        exit;
    } else {
        $_SESSION['modal'] = ['message' => 'Username cannot be empty!', 'type' => 'error'];
        header("Location: ../pages/settings.php");
        exit;
    }
}

// ---------------------------
// Update Password Only
// ---------------------------
if (isset($_POST['update_password'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($password) || empty($confirmPassword)) {
        $_SESSION['modal'] = ['message' => 'Password fields cannot be empty!', 'type' => 'error'];
        header("Location: ../pages/settings.php");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['modal'] = ['message' => 'Passwords do not match!', 'type' => 'error'];
        header("Location: ../pages/settings.php");
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->execute([$passwordHash, $userId]);

    $_SESSION['modal'] = ['message' => 'Password updated successfully!', 'type' => 'success'];
    header("Location: ../pages/settings.php");
    exit;
}

// ---------------------------
// Update Preferences
// ---------------------------
if (isset($_POST['update_preferences'])) {
    $theme = $_POST['theme'];
    $language = $_POST['language'];

    $stmt = $conn->prepare("UPDATE users SET theme=?, language=? WHERE id=?");
    $stmt->execute([$theme, $language, $userId]);

    $_SESSION['user']['theme'] = $theme;
    $_SESSION['user']['language'] = $language;

    $_SESSION['modal'] = ['message' => 'Preferences saved successfully!', 'type' => 'success'];
    header("Location: ../pages/settings.php");
    exit;
}

// ---------------------------
// Update Security Settings
// ---------------------------
if (isset($_POST['update_security'])) {
    $twoFA = isset($_POST['2fa']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET two_fa=? WHERE id=?");
    $stmt->execute([$twoFA, $userId]);

    $_SESSION['user']['two_fa'] = $twoFA;

    $_SESSION['modal'] = ['message' => 'Security settings updated!', 'type' => 'success'];
    header("Location: ../pages/settings.php");
    exit;
}

// ---------------------------
// Fallback redirect
// ---------------------------
header("Location: ../pages/settings.php");
exit;
?>
