<?php
session_start();

// Include database configuration and core classes
$config = require __DIR__ . '/../config/database.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

// Initialize Database and Auth
$db   = new Database($config);
$auth = new Auth($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        // Redirect to dashboard after successful login
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - iCensus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
<div class="card p-4 shadow" style="width: 350px;">
    <h4 class="text-center mb-3">iCensus Login</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
