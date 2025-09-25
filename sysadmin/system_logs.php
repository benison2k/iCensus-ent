<?php 
require_once __DIR__ . '/auth_check.php';
if (file_exists(__DIR__ . '/../core/functions.php')) {
    require_once __DIR__ . '/../core/functions.php';
}

// --- Filtering Logic ---
$filter = $_GET['filter'] ?? 'all'; // Default to 'all' if no filter is set
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
    default:
        $page_title = "All System Logs";
        // No WHERE clause needed if we are showing all logs
        break;
}

if (!empty($log_actions)) {
    $placeholders = implode(',', array_fill(0, count($log_actions), '?'));
    $where_clause = "WHERE action IN ($placeholders)";
    $params = $log_actions;
}

// Fetch the logs based on the filter
$stmt = $db->getPdo()->prepare("SELECT * FROM system_logs $where_clause ORDER BY timestamp DESC LIMIT 500");
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

<div class="welcome"><h2><?= htmlspecialchars($page_title) ?></h2></div>

<main class="dashboard">
<div class="user-management-container">

    <div class="filter-bar">
        <a href="system_logs.php?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
        <a href="system_logs.php?filter=auth" class="filter-btn <?= $filter === 'auth' ? 'active' : '' ?>">Authentication</a>
        <a href="system_logs.php?filter=data" class="filter-btn <?= $filter === 'data' ? 'active' : '' ?>">Data Audit</a>
        <a href="system_logs.php?filter=user_management" class="filter-btn <?= $filter === 'user_management' ? 'active' : '' ?>">User Management</a>
        <a href="system_logs.php?filter=system" class="filter-btn <?= $filter === 'system' ? 'active' : '' ?>">System</a>
    </div>

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
/* Styles for the filter bar */
.filter-bar {
    margin-bottom: 1.5rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.filter-btn {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background-color: #e0e0e0;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}
.filter-btn:hover {
    background-color: #ccc;
}
.filter-btn.active {
    background-color: #2e7d32;
    color: white;
}
body.dark-mode .filter-btn {
    background-color: #3b4a59;
    color: #fff;
}
body.dark-mode .filter-btn:hover {
    background-color: #4a5c6e;
}
body.dark-mode .filter-btn.active {
    background-color: #1e88e5;
}

/* Styles for log levels */
.log-level-info { color: #0277bd; font-weight: bold; }
.log-level-warning { color: #f57c00; font-weight: bold; }
.log-level-error { color: #c62828; font-weight: bold; }
</style>
</body>
</html>