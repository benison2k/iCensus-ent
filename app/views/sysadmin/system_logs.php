<?php
// /app/views/sysadmin/system_logs.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - System Logs</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/users.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_table.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/system_logs.css"> 
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= htmlspecialchars($theme) === 'dark' ? 'dark-mode' : 'light-mode'; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<main class="dashboard">
<div class="user-management-container">
    <div class="welcome">
        <h2>System Logs</h2>
    </div>
    
    <div class="filter-container">
        <form method="GET" action="<?= $base_url ?>/sysadmin/logs" id="filterForm">
            <div class="main-filter-controls">
                <input type="hidden" name="sort_by" value="<?= htmlspecialchars($currentSortBy) ?>">
                <input type="hidden" name="sort_order" value="<?= htmlspecialchars($currentSortOrder) ?>">

                <div class="filter-group search-filter">
                    <label for="search">Search Details</label>
                    <input type="search" name="search" id="search" value="<?= htmlspecialchars($currentSearch) ?>" placeholder="e.g., resident ID, username...">
                </div>

                <div class="filter-group">
                    <label for="level">Level</label>
                    <select name="level" id="level" class="auto-submit-filter">
                        <option value="">All Levels</option>
                        <option value="INFO" <?= $currentLevel == 'INFO' ? 'selected' : '' ?>>Info</option>
                        <option value="WARNING" <?= $currentLevel == 'WARNING' ? 'selected' : '' ?>>Warning</option>
                        <option value="ERROR" <?= $currentLevel == 'ERROR' ? 'selected' : '' ?>>Error</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter">Category</label>
                    <select name="filter" id="filter" class="auto-submit-filter">
                        <option value="all" <?= $currentFilter === 'all' ? 'selected' : '' ?>>All Actions</option>
                        <option value="auth" <?= $currentFilter === 'auth' ? 'selected' : '' ?>>Authentication</option>
                        <option value="data" <?= $currentFilter === 'data' ? 'selected' : '' ?>>Data Changes</option>
                        <option value="user_management" <?= $currentFilter === 'user_management' ? 'selected' : '' ?>>User Management</option>
                        <option value="system" <?= $currentFilter === 'system' ? 'selected' : '' ?>>System</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="user_id">User</label>
                    <select name="user_id" id="user_id" class="auto-submit-filter">
                        <option value="">All Users</option>
                        <?php foreach($all_users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $currentUserId == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                </div>
                
                <div class="filter-group">
                    <div class="button-group">
                        <button type="submit" class="clear-btn" style="background-color:#0d6efd; height: 42px;">Filter</button>
                        <a href="<?= $base_url ?>/sysadmin/logs" class="clear-btn" style="text-decoration:none;">Clear Filters</a>
                        <button id="markAllSeenBtn" type="button" class="clear-btn" style="background-color:#f57c00;">Mark all as seen</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="table-container" style="margin-top: 1.5rem;">
        <div class="table-responsive">
            <table class="resident-table log-table"> 
                <thead>
                    <tr class="unified-header">
                        <th>
                            <?php
                            $newSortOrder = ($currentSortBy === 'timestamp' && $currentSortOrder === 'DESC') ? 'ASC' : 'DESC';
                            $queryParams = $_GET;
                            $queryParams['sort_by'] = 'timestamp';
                            $queryParams['sort_order'] = $newSortOrder;
                            ?>
                            <a href="?<?= http_build_query($queryParams) ?>" class="sort-link pagination-control">
                                Timestamp
                                <?php if ($currentSortBy === 'timestamp'): ?>
                                    <span class="material-icons">
                                        <?= $currentSortOrder === 'DESC' ? 'arrow_downward' : 'arrow_upward' ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>Level</th>
                        <th>User</th>
                        <th>Action</th>
                        <th colspan="2">
                            <div class="header-controls-container">
                                <form method="GET" action="<?= $base_url ?>/sysadmin/logs" id="pageSizeForm" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="hidden" name="filter" value="<?= htmlspecialchars($currentFilter) ?>">
                                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                                    <input type="hidden" name="sort_by" value="<?= htmlspecialchars($currentSortBy) ?>">
                                    <input type="hidden" name="sort_order" value="<?= htmlspecialchars($currentSortOrder) ?>">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($currentUserId) ?>">
                                    <input type="hidden" name="level" value="<?= htmlspecialchars($currentLevel) ?>">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($currentSearch) ?>">
                                    <label for="pageSizeSelect">Show</label>
                                    <select name="pageSize" id="pageSizeSelect" class="auto-submit-filter">
                                        <option value="10" <?= $currentPageSize == 10 ? 'selected' : '' ?>>10</option>
                                        <option value="25" <?= $currentPageSize == 25 ? 'selected' : '' ?>>25</option>
                                        <option value="50" <?= $currentPageSize == 50 ? 'selected' : '' ?>>50</option>
                                        <option value="100" <?= $currentPageSize == 100 ? 'selected' : '' ?>>100</option>
                                    </select>
                                    <label>entries</label>
                                </form>

                                <div class="page-nav-group">
                                    <?php
                                    $navParams = $_GET;
                                    unset($navParams['page']);
                                    $navQueryString = http_build_query($navParams);
                                    ?>
                                    <a href="?page=<?= $currentPage - 1 ?>&<?= $navQueryString ?>" class="pagination-link pagination-control" <?= $currentPage <= 1 ? 'style="pointer-events:none;"' : '' ?>><button <?= $currentPage <= 1 ? 'disabled' : '' ?>>Prev</button></a>
                                    <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
                                    <a href="?page=<?= $currentPage + 1 ?>&<?= $navQueryString ?>" class="pagination-link pagination-control" <?= $currentPage >= $totalPages ? 'style="pointer-events:none;"' : '' ?>><button <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>Next</button></a>
                                    <input type="number" id="gotoPageInput" min="1" max="<?= $totalPages ?>" placeholder="Page #">
                                    <button id="gotoPageBtn" class="pagination-control">Go</button>
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" style="text-align: center;">No logs found for the selected filters.</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $log): ?>
                        <?php
                            $role_class = 'role-' . strtolower(str_replace(' ', '', $log['role_name'] ?? 'system'));
                            $level_class = 'log-level-' . strtolower($log['level']);
                            $is_new = ($log['is_seen'] == 0) ? 'new-log' : '';
                        ?>
                        <tr class="<?= $is_new ?>" data-id="<?= $log['id'] ?>">
                            <td><?= htmlspecialchars($log['timestamp']) ?></td>
                            <td><span class="log-badge <?= $level_class ?>"><?= htmlspecialchars($log['level']) ?></span></td>
                            <td>
                                <span class="log-badge <?= $role_class ?>"><?= htmlspecialchars($log['username'] ?? 'SYSTEM') ?></span>
                            </td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td colspan="2" style="white-space: pre-wrap; word-break: break-all;"><?= htmlspecialchars($log['details']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="<?= $base_url ?>/assets/js/system_logs.js"></script>

</body>
</html>