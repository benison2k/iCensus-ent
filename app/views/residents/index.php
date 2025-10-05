<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Residents</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css"> 
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Residents Management</h2></div>

    <main class="dashboard">
        <div style="padding:0 2rem; max-width:1400px; margin:auto;">
            
        <div class="page-actions-container">
                <button id="addResidentBtn" class="action-button-link">
                    <span class="material-icons">person_add</span> Add Resident
                </button>

                <?php if ($user['role_name'] === 'Barangay Admin'): ?>
                    
                    <a href="<?= $base_url ?>/residents?view=pending" 
                       class="action-button-link card" 
                       style="text-decoration: none; border-width: 2px; border-style: solid; border-color: <?= $isPendingView ? '#0d6efd' : 'transparent' ?>; padding: 0.6rem 1rem; min-width: auto; min-height: auto;">
                        
                        <?php if ($pending_count > 0): ?>
                            <span class="notification-badge"><?= $pending_count ?></span>
                        <?php endif; ?>
                        
                        <span class="material-icons">rate_review</span> Pending Review
                    </a>

                     <a href="<?= $base_url ?>/residents" class="action-button-link" style="text-decoration: none; border-width: 2px; border-style: solid; border-color: <?= !$isPendingView ? '#2e7d32' : 'transparent' ?>;">
                         <span class="material-icons">verified</span> Approved Residents
                    </a>

                    <?php if ($isPendingView && $pending_count > 0): ?>
                        <a href="<?= $base_url ?>/residents/approve-all" 
                           class="action-button-link" 
                           style="background-color: #28a745; color: white; margin-left: auto;" 
                           onclick="return confirm('Are you sure you want to approve all <?= $pending_count ?> pending entries?');">
                            <span class="material-icons">done_all</span> Approve All (<?= $pending_count ?>)
                        </a>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>

            <p style="margin: 1rem 0; font-weight: 500;">
                Viewing: <span style="font-weight: bold; color: <?= $isPendingView ? '#0d6efd' : '#2e7d32' ?>;"><?= $isPendingView ? 'Pending Entries' : 'Approved Residents' ?></span>
                <br>
                Total in this view: <span><?= count($residents); ?></span>
            </p>

            <div style="margin: 0.5rem 0; font-weight: 500; display:none;" id="filteredResults">
                 Filtered search results: <span id="filteredCount">0</span>
            </div>

            <div class="filter-container" style="<?= $isPendingView ? 'display:none;' : '' ?>">
                <div class="filter-group search-filter">
                    <label for="searchInput">Search by Name</label>
                    <input type="text" id="searchInput" placeholder="Enter name...">
                </div>
                 <div class="filter-group">
                    <label for="houseNoFilter">House No.</label>
                    <input type="text" id="houseNoFilter" placeholder="Enter house no...">
                </div>
                 <div class="filter-group">
                    <label for="streetFilter">Street</label>
                    <input type="text" id="streetFilter" placeholder="Enter street name...">
                </div>
                <div class="filter-group">
                    <label for="purokFilter">Purok</label>
                    <select id="purokFilter">
                        <option value="">All</option>
                        <?php
                        if (!$isPendingView) {
                            $puroks = array_unique(array_column($residents, 'purok'));
                            sort($puroks);
                            foreach($puroks as $p) if(!empty($p)) echo "<option value=\"".htmlspecialchars($p)."\">".htmlspecialchars($p)."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter">
                        <option value="">All</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Moved">Moved</option>
                        <option value="Deceased">Deceased</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="genderFilter">Gender</label>
                    <select id="genderFilter">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="filter-group age-filter">
                    <label>Age Range</label>
                    <div class="age-inputs">
                        <input type="number" id="ageMin" placeholder="Min">
                        <span>-</span>
                        <input type="number" id="ageMax" placeholder="Max">
                    </div>
                </div>
                <div class="filter-group">
                    <button id="clearFiltersBtn" class="clear-btn">
                        Clear Filters
                    </button>
                </div>
            </div>

            <div id="pagination-controls" style="margin: 1rem 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; <?= $isPendingView ? 'display:none;' : '' ?>">
                <div>
                    <label>Show
                        <select id="pageSizeSelect" style="padding:0.3rem;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    entries</label>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <span>Showing <span id="shownCount">0–0</span> of <span id="totalCountEl">0</span></span>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <button id="prevPageBtn" style="padding:0.3rem 0.5rem;">Prev</button>
                    <span id="pageInfo">Page 1 of 1</span>
                    <button id="nextPageBtn" style="padding:0.3rem 0.5rem;">Next</button>
                    <input type="number" id="gotoPage" min="1" style="width:70px; padding:0.3rem;" placeholder="Page">
                    <button id="gotoPageBtn" style="padding:0.3rem 0.5rem;">Go</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="resident-table" id="residentsTable">
                    <thead>
                        <tr>
                            <th>Full Name</th><th>Age</th><th>Gender</th><th>Address</th>
                            <th><?= $isPendingView ? 'Date Submitted' : 'Status' ?></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="residentsTableBody">
                        <?php if (empty($residents)): ?>
                            <tr><td colspan="6" style="text-align: center;">No residents found in this view.</td></tr>
                        <?php else: ?>
                            <?php foreach($residents as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                    <td><?= htmlspecialchars($r['age'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($r['gender']) ?></td>
                                    <td><?= htmlspecialchars($r['house_no'] . ' ' . $r['street'] . ', Purok ' . $r['purok']) ?></td>
                                    <td>
                                        <?php if ($isPendingView): ?>
                                            <?= date('M d, Y h:i A', strtotime($r['created_at'])) ?>
                                        <?php else: ?>
                                            <span class="status-label status-<?= strtolower($r['status'] ?? '') ?>"><?= htmlspecialchars($r['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isPendingView): ?>
                                            <a href="<?= $base_url ?>/residents/approve?id=<?= $r['id'] ?>" class="action-btn" title="Approve"><span class="material-icons" style="color:green;">check_circle</span></a>
                                            <a href="<?= $base_url ?>/residents/reject?id=<?= $r['id'] ?>" class="action-btn" title="Reject" onclick="return confirm('Are you sure you want to reject and delete this entry?');"><span class="material-icons" style="color:red;">cancel</span></a>
                                        <?php else: ?>
                                            <button class="moreBtn material-icons" data-id="<?= $r['id'] ?>" title="View Resident Info">more_vert</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include __DIR__ . '/../components/resident_modal2.php'; ?>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script>
        const allResidentsData = <?= json_encode($residents); ?>;
        const isPendingView = <?= $isPendingView ? 'true' : 'false' ?>;
    </script>
    <script src="<?= $base_url ?>/assets/js/residents.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!isPendingView) {
                applyFilters(); 
            }
        });
    </script>
</body>
</html>