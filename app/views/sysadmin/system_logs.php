<?php
// /app/views/sysadmin/system_logs.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - System Logs</title>
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/style.css">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/users.css">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/residents.css">
<style>
    .log-table th, .log-table td { vertical-align: middle; }
    .log-badge {
        padding: 0.2rem 0.6rem; border-radius: 5px; font-weight: 500; color: #fff;
        text-align: center; font-size: 0.8rem; text-transform: uppercase;
        display: inline-block; min-width: 70px;
    }
    .log-level-info { background-color: #1e88e5; }
    .log-level-warning { background-color: #f57c00; }
    .log-level-error { background-color: #d32f2f; }
    .role-systemadmin { background-color: #c62828; }
    .role-barangayadmin { background-color: #2e7d32; }
    .role-encoder { background-color: #0277bd; }
    .role-system { background-color: #616161; }
    .user-management-container { max-width: 1400px; }
    
    .sort-link { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.3rem;}
    .sort-link .material-icons { font-size: 1.1rem; vertical-align: middle; }
    body.dark-mode .sort-link { color: #fff; }

    .log-table th:first-child,
    .log-table td:first-child { width: 200px; }
    .log-table th:first-child .sort-link { white-space: nowrap; }
    
    .new-log { background-color: #f1f8e9 !important; cursor: pointer; }
    body.dark-mode .new-log { background-color: #2c3b2d !important; }
    .button-group { display: flex; gap: 0.5rem; }

    .table-container {
        margin-top: 1.5rem;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .unified-header th {
        vertical-align: middle;
    }
    .unified-header .header-controls-container {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1.5rem;
    }
    .page-nav-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-nav-group button, .page-nav-group a > button {
        padding: 0.5rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        background: #fff;
        cursor: pointer;
    }
    .page-nav-group button:disabled { opacity: 0.5; cursor: not-allowed; }
    .page-nav-group input {
        width: 70px;
        padding: 0.5rem;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    #pageSizeSelect { padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; }
    
    body.dark-mode .page-nav-group button, 
    body.dark-mode .page-nav-group a > button,
    body.dark-mode .page-nav-group input, 
    body.dark-mode #pageSizeSelect {
        background-color: #2C3E50;
        border-color: #4a5a6a;
        color: #fff;
    }
    body.dark-mode .page-nav-group button:disabled { background-color: #2C3E50; opacity: 0.4; }
</style>
</head>
<body class="<?= htmlspecialchars($theme) === 'dark' ? 'dark-mode' : ''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<main class="dashboard">
<div class="user-management-container">
    <div class="welcome">
        <h2>System Logs</h2>
    </div>
    
    <div class="filter-container">
        <form method="GET" action="/iCensus-ent/public/sysadmin/logs" id="filterForm">
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
                        <a href="/iCensus-ent/public/sysadmin/logs" class="clear-btn" style="text-decoration:none;">Clear Filters</a>
                        <button id="markAllSeenBtn" type="button" class="clear-btn" style="background-color:#f57c00;">Mark all as seen</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="user-table log-table">
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
                                <form method="GET" action="/iCensus-ent/public/sysadmin/logs" id="pageSizeForm" style="display: flex; align-items: center; gap: 0.5rem;">
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

<script>
    document.getElementById('gotoPageBtn').addEventListener('click', function() {
        const pageNum = document.getElementById('gotoPageInput').value;
        if (pageNum) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', pageNum);
            window.location.href = currentUrl.href;
        }
    });

    document.querySelectorAll('.auto-submit-filter').forEach(function(element) {
        element.addEventListener('change', function() {
            if (this.id === 'pageSizeSelect') {
                document.getElementById('pageSizeForm').submit();
            } else {
                document.getElementById('filterForm').submit();
            }
        });
    });

    document.getElementById('logTableBody').addEventListener('click', function(e) {
        const row = e.target.closest('tr.new-log');
        if (row) {
            const logId = row.dataset.id;
            row.classList.remove('new-log');
            
            const formData = new FormData();
            formData.append('id', logId);

            fetch('/iCensus-ent/public/sysadmin/logs/mark-as-seen', {
                method: 'POST',
                body: formData
            }).catch(error => console.error('Error:', error));
        }
    });

    document.getElementById('markAllSeenBtn').addEventListener('click', function() {
        fetch('/iCensus-ent/public/sysadmin/logs/mark-all-as-seen', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.querySelectorAll('tr.new-log').forEach(row => {
                    row.classList.remove('new-log');
                });
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // --- SCRIPT TO MAINTAIN SCROLL POSITION ---
    
    function saveScrollPosition() {
        sessionStorage.setItem('logScrollPosition', window.scrollY);
    }

    document.querySelectorAll('.pagination-control').forEach(el => {
        el.addEventListener('click', saveScrollPosition);
    });

    document.querySelectorAll('#filterForm, #pageSizeForm').forEach(form => {
        form.addEventListener('submit', saveScrollPosition);
    });

    window.addEventListener('load', () => {
        const scrollPosition = sessionStorage.getItem('logScrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition, 10));
            sessionStorage.removeItem('logScrollPosition');
        }
    });
</script>

</body>
</html>