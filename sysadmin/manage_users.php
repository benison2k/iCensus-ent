<?php 
require_once __DIR__ . '/auth_check.php'; 

// Modal setup for feedback messages
$modalMessage = $_SESSION['modal']['message'] ?? '';
$modalType = $_SESSION['modal']['type'] ?? '';
unset($_SESSION['modal']);

// Fetch all users *except* System Admins.
$users_stmt = $db->getPdo()->query("
    SELECT users.id, users.username, users.full_name, roles.role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.id 
    WHERE roles.role_name != 'System Admin' 
    ORDER BY users.id
");
$all_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch only the assignable roles (Barangay Admin and Encoder)
$roles_stmt = $db->getPdo()->query("SELECT id, role_name FROM roles WHERE role_name != 'System Admin'");
$assignable_roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale-1.0">
<title>iCensus - Manage Users</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/users.css"> 
<link rel="stylesheet" href="../assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Manage Barangay Users</h2></div>

<main class="dashboard">

<div class="user-management-container">
    <button id="addUserBtn" class="settings-card add-user-btn">
        <span class="material-icons">person_add</span> Add New User
    </button>

    <div class="table-responsive">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td>
                        <span class="role-label role-<?= strtolower(str_replace(' ', '', $u['role_name'])) ?>">
                            <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="action-btn editBtn" data-id="<?= $u['id'] ?>" title="Edit User"><span class="material-icons">edit</span></button>
                        <button class="action-btn deleteBtn" data-id="<?= $u['id'] ?>" title="Delete User"><span class="material-icons">delete</span></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>

<?php include __DIR__ . '/../components/user_modal.php'; ?>
<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="../assets/js/users.js"></script>

</body>
</html>