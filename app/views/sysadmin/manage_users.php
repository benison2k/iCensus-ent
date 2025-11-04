<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Manage Users</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/page_actions.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_table.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_filters.css"> 
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/users.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
    /* Styles for new icon buttons, copied from residents page */
    .actions-column { display: flex; gap: 0.5rem; align-items: center; }
    .action-btn {
        padding: 0.5rem; border-radius: 50%; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border: none; cursor: pointer;
        transition: background-color 0.2s;
    }
    .action-btn .material-icons { font-size: 20px; vertical-align: middle; }
    
    /* Light Mode Colors */
    .btn-edit { background-color: #e3f2fd; color: #0d6efd; }
    .btn-edit:hover { background-color: #bbdefb; }
    .btn-delete { background-color: #ffebee; color: #c62828; }
    .btn-delete:hover { background-color: #ffcdd2; }

    /* Dark Mode Colors */
    body.dark-mode .btn-edit { background-color: #1a3a5b; color: #90caf9; }
    body.dark-mode .btn-edit:hover { background-color: #0d6efd; }
    body.dark-mode .btn-delete { background-color: #3e2723; color: #ef9a9a; }
    body.dark-mode .btn-delete:hover { background-color: #c62828; }
</style>

</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Manage Barangay Users</h2></div>

<main class="dashboard dashboard-management">
<div class="user-management-container">

    <div class="page-actions-container">
        <button id="addUserBtn" class="action-button-link">
            <span class="material-icons">person_add</span> Add New User
        </button>
    </div>

    <div class="filter-wrapper">
        <div class="filter-container">
            <div class="main-filter-controls">
                <div class="filter-group search-filter">
                    <label for="searchInput">Search by Name or Username</label>
                    <input type="text" id="searchInput" placeholder="Enter name or username...">
                </div>
                <div class="filter-group">
                    <label for="roleFilterSelect">Filter by Role</label>
                    <select id="roleFilterSelect">
                        <option value="">All Roles</option>
                        <?php foreach ($assignable_roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['role_name']) ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button id="clearFiltersBtn" class="clear-btn">
                        Clear Filters
                    </button>
                </div>
            </div>
            <div id="activeFiltersContainer" class="active-filters-container" style="display: none;">
                <span class="active-filters-label">Active Filters:</span>
            </div>
        </div>
    </div>

    <div class="table-area-wrapper">
        <div class="table-container">
            <table class="resident-table" id="usersTable"> <thead>
                    <tr class="table-controls-header">
                        <th colspan="5"> <div id="pagination-controls" style="margin: 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                                    <div>
                                        <label>Show
                                            <select id="pageSizeSelect" style="padding:0.3rem;">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                        entries</label>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                        <span>Showing <span id="shownCount">0–0</span> of <span id="totalCountEl">0</span></span>
                                    </div>
                                    <div style="margin: 0; font-weight: 500; display:none;" id="filteredResults">
                                        <span style="font-weight: 500;">(Filtered: <span id="filteredCount">0</span>)</span>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <button id="prevPageBtn" style="padding:0.3rem 0.5rem;">Prev</button>
                                    <span id="pageInfo">Page 1 of 1</span>
                                    <button id="nextPageBtn" style="padding:0.3rem 0.5rem;">Next</button>
                                    <input type="number" id="gotoPage" min="1" max="1" style="width:70px; padding:0.3rem;" placeholder="Page">
                                    <button id="gotoPageBtn" style="padding:0.3rem 0.5rem;">Go</button>
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th class="sortable" data-sort="id">
                            <div class="sort-header-content">
                                <div class="sort-header-top-line"><span>ID</span></div>
                                <span class="sort-icon"></span>
                            </div>
                        </th>
                        <th class="sortable" data-sort="username">
                            <div class="sort-header-content">
                                <div class="sort-header-top-line"><span>Username</span></div>
                                <span class="sort-icon"></span>
                            </div>
                        </th>
                        <th class="sortable" data-sort="full_name">
                             <div class="sort-header-content">
                                <div class="sort-header-top-line"><span>Full Name</span></div>
                                <span class="sort-icon"></span>
                            </div>
                        </th>
                        <th class="sortable" data-sort="role_name">
                             <div class="sort-header-content">
                                <div class="sort-header-top-line"><span>Role</span></div>
                                <span class="sort-icon"></span>
                            </div>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<?php $form_action = '/iCensus-ent/public/sysadmin/users/process'; ?>
<?php include __DIR__ . '/../components/user_modal.php'; ?>
<?php include __DIR__ . '/../components/footer.php'; ?>

<div id="ajaxToast" class="toast-notification">
    <span class="material-icons" id="toastIcon">check_circle</span>
    <p id="toastMessage"></p>
</div>
<style>
    /* Toast Notification Styles */
    .toast-notification {
        position: fixed; top: 20px; right: 20px; background-color: #28a745; color: white;
        padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 2000; display: flex; align-items: center; gap: 1rem; opacity: 0;
        transform: translateX(100%); transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .toast-notification.show { opacity: 1; transform: translateX(0); }
    .toast-notification.error { background-color: #dc3545; }
    .toast-notification.info { background-color: #0d6efd; }
</style>

<script>
    // Pass all user data to the new JavaScript file
    const allUsersData = <?= json_encode($all_users); ?>;
    const userRole = '<?= htmlspecialchars($user['role_name']) ?>';
    const assignableRoles = <?= json_encode($assignable_roles); ?>;
</script>
<script src="<?= $base_url ?>/assets/js/users.js"></script>

</body>
</html>