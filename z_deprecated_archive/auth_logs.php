<?php 
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../core/functions.php';

$filter = $_GET['filter'] ?? 'all';
$page_title = "System Logs";
$where_clause = "";
$params = [];

$log_actions = [];

switch ($filter) {
    case 'auth':
        $page_title = "Authentication Logs";
        $log_actions = ['USER_LOGIN_SUCCESS', 'USER_LOGIN_FAIL', 'USER_LOGOUT'];
        break;
    case 'data':
        $page_title = "Data Audit Logs";
        $log_actions = ['RESIDENT_CREATE', 'RESIDENT_UPDATE', 'RESIDENT_DELETE'];
        break;
    case 'user_management':
        $page_title = "User Management Logs";
        $log_actions = ['USER_CREATE', 'USER_UPDATE', 'USER_DELETE'];
        break;
    case 'system':
        $page_title = "System & Error Logs";
        $log_actions = ['SYSTEM_ERROR', 'DB_ERROR', 'SETTINGS_UPDATE'];
        break;
}

if (!empty($log_actions)) {
    $placeholders = implode(',', array_fill(0, count($log_actions), '?'));
    $where_clause = "WHERE action IN ($placeholders)";
    $params = $log_actions;
}

$stmt = $db->getPdo()->prepare("SELECT * FROM system_logs $where_clause ORDER BY timestamp DESC LIMIT 200");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - <?= htmlspecialchars($page_title) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/users.css">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Viewing: <?= htmlspecialchars($page_title) ?></h2></div>

<main class="dashboard">
<div class="user-management-container">
    <div class="table-responsive">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Level</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align: center;">No logs found for this category.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['timestamp']) ?></td>
                        <td><span class="log-level-<?= strtolower(htmlspecialchars($log['level'])) ?>"><?= htmlspecialchars($log['level']) ?></span></td>
                        <td><?= htmlspecialchars($log['username']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<style>
.log-level-info { color: #0277bd; font-weight: bold; }
.log-level-warning { color: #f57c00; font-weight: bold; }
.log-level-error { color: #c62828; font-weight: bold; }
</style>
</body>
</html>