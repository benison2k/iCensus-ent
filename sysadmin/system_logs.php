<?php 
require_once __DIR__ . '/auth_check.php';
if (file_exists(__DIR__ . '/../core/functions.php')) {
    require_once __DIR__ . '/../core/functions.php';
}

// --- Filtering Logic ---
$filter = $_GET['filter'] ?? 'all'; 
$page_title = "System Logs";
$where_clause = "";
$params = [];

// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = 25;
$offset = ($page - 1) * $pageSize;

// Date filtering variables
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$log_actions = [];
$conditions = [];

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
        break;
}

if (!empty($log_actions)) {
    $placeholders = implode(',', array_fill(0, count($log_actions), '?'));
    $conditions[] = "action IN ($placeholders)";
    $params = array_merge($params, $log_actions);
}

if (!empty($start_date)) {
    $conditions[] = "timestamp >= ?";
    $params[] = $start_date . ' 00:00:00';
}
if (!empty($end_date)) {
    $conditions[] = "timestamp <= ?";
    $params[] = $end_date . ' 23:59:59';
}

if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Get total count for pagination
$count_stmt = $db->getPdo()->prepare("SELECT COUNT(*) FROM system_logs $where_clause");
$count_stmt->execute($params);
$totalLogs = $count_stmt->fetchColumn();
$totalPages = ceil($totalLogs / $pageSize);

// Fetch the logs based on the filter and pagination
$stmt = $db->getPdo()->prepare("SELECT * FROM system_logs $where_clause ORDER BY timestamp DESC LIMIT $pageSize OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the current page URL without page, start_date, end_date params
$currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
$baseQuery = http_build_query(['filter' => $filter]);
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

    <div class="date-filter-bar" style="margin-bottom: 1.5rem;">
        <form method="GET" action="system_logs.php" style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            
            <label for="start_date" style="font-weight: 500;">From:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">

            <label for="end_date" style="font-weight: 500;">To:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">
            
            <button type="submit" style="padding: 0.5rem 1rem; background-color: #2e7d32; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Filter</button>
            <a href="system_logs.php?<?= htmlspecialchars($baseQuery) ?>" style="padding: 0.5rem 1rem; background-color: #f44336; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">Clear</a>
        </form>
    </div>
    <div class="pagination-controls" style="margin-top: 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 1rem;">
        <span>Page <?= $page ?> of <?= $totalPages ?> (Total: <?= $totalLogs ?>)</span>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>" class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" style="text-decoration: none;">Previous</a>
        <form method="GET" action="system_logs.php" style="display:flex; align-items:center; gap:0.25rem;">
            <?php foreach($_GET as $key => $value): ?>
                <?php if($key !== 'page'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="number" name="page" min="1" max="<?= $totalPages ?>" value="<?= htmlspecialchars($page) ?>" style="width: 60px; text-align: center; padding:0.25rem; border-radius:4px; border:1px solid #ccc;">
            <button type="submit" style="padding: 0.25rem 0.5rem; background-color: #1e88e5; color:white; border:none; border-radius:4px; cursor:pointer;">Go</button>
        </form>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])) ?>" class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>" style="text-decoration: none;">Next</a>
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
                        <td><?= htmlspecialchars($log['username'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-controls" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 1rem;">
        <span>Page <?= $page ?> of <?= $totalPages ?> (Total: <?= $totalLogs ?>)</span>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>" class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" style="text-decoration: none;">Previous</a>
        <form method="GET" action="system_logs.php" style="display:flex; align-items:center; gap:0.25rem;">
            <?php foreach($_GET as $key => $value): ?>
                <?php if($key !== 'page'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="number" name="page" min="1" max="<?= $totalPages ?>" value="<?= htmlspecialchars($page) ?>" style="width: 60px; text-align: center; padding:0.25rem; border-radius:4px; border:1px solid #ccc;">
            <button type="submit" style="padding: 0.25rem 0.5rem; background-color: #1e88e5; color:white; border:none; border-radius:4px; cursor:pointer;">Go</button>
        </form>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])) ?>" class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>" style="text-decoration: none;">Next</a>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<style>
/* Styles for the filter bar */
.filter-bar {
    margin-bottom: 1rem;
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

/* Date filter bar */
.date-filter-bar {
    margin-bottom: 1.5rem;
}

/* Pagination styles */
.pagination-controls {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    font-size: 0.9rem;
}

.page-link {
    padding: 0.5rem 1rem;
    background-color: #eee;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: background-color 0.2s;
}

.page-link:hover:not(.disabled) {
    background-color: #ccc;
}

.page-link.disabled {
    opacity: 0.5;
    pointer-events: none;
    cursor: not-allowed;
}

body.dark-mode .page-link {
    background-color: #3b4a59;
    color: #fff;
}

body.dark-mode .page-link:hover:not(.disabled) {
    background-color: #4a5c6e;
}

/* Styles for log levels */
.log-level-info { color: #0277bd; font-weight: bold; }
.log-level-warning { color: #f57c00; font-weight: bold; }
.log-level-error { color: #c62828; font-weight: bold; }
</style>
</body>
</html>