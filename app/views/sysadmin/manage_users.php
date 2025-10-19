<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Manage Users</title>
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/style.css">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/residents.css">
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/users.css"> 
<link rel="stylesheet" href="/iCensus-ent/public/assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
            <div class="filter-group search-filter">
                <label for="userSearchInput">Search by Name or Username</label>
                <input type="text" id="userSearchInput" placeholder="Enter name or username...">
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
        </div>
    </div>

    <div id="pagination-controls" style="margin: 1rem 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
        <div>
            <label>Show
                <select id="pageSizeSelect" style="padding:0.3rem;">
                    <option value="10" <?= $pageSize == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $pageSize == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $pageSize == 50 ? 'selected' : '' ?>>50</option>
                </select>
            entries</label>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span>Showing <span id="shownCount">0–0</span> of <span id="totalCountEl"><?= $totalUsers ?></span></span>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <button id="prevPageBtn" style="padding:0.3rem 0.5rem;" <?= $currentPage <= 1 ? 'disabled' : '' ?>>Prev</button>
            <span id="pageInfo">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <button id="nextPageBtn" style="padding:0.3rem 0.5rem;" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>Next</button>
            <input type="number" id="gotoPage" min="1" max="<?= $totalPages ?>" style="width:70px; padding:0.3rem;" placeholder="Page">
            <button id="gotoPageBtn" style="padding:0.3rem 0.5rem;">Go</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="user-table resident-table">
            <thead>
                <tr>
                    <th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php if (empty($all_users)): ?>
                    <tr><td colspan="5" style="text-align: center;">No users found.</td></tr>
                <?php endif; ?>
                <?php foreach($all_users as $u): ?>
                <tr data-user-id="<?= $u['id'] ?>">
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td data-searchable><?= htmlspecialchars($u['username']) ?></td>
                    <td data-searchable><?= htmlspecialchars($u['full_name']) ?></td>
                    <td data-role="<?= htmlspecialchars($u['role_name']) ?>"><span class="role-label role-<?= strtolower(str_replace(' ', '', $u['role_name'])) ?>"><?= htmlspecialchars($u['role_name']) ?></span></td>
                    <td>
                        <button class="action-btn editBtn" data-id="<?= $u['id'] ?>"><span class="material-icons">edit</span></button>
                        <button class="action-btn deleteBtn" data-id="<?= $u['id'] ?>"><span class="material-icons">delete</span></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
         <p id="noResultsMessage" style="text-align:center; padding: 2rem; display:none;">No users match your search criteria.</p>
    </div>
</div>
</main>

<?php $form_action = '/iCensus-ent/public/sysadmin/users/process'; ?>
<?php include __DIR__ . '/../components/user_modal.php'; ?>
<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/iCensus-ent/public/assets/js/users.js"></script>

</body>
</html>