<?php
// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/app/views/residents/index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Residents</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="icon" type="image/png" href="<?= $base_url ?>/assets/img/iCensusLogoOnly2.png">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_table.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_filters.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/page_actions.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/view_tabs.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : ''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Residents Management</h2></div>

    <main class="dashboard dashboard-management">
        <div style="padding:0 2rem; max-width:1600px; margin:auto; width: 100%;">

            <div class="page-actions-container">
                <button id="addResidentBtn" class="action-button-link">
                    <span class="material-icons">person_add</span> Add Resident
                </button>

                <?php if ($user['role_name'] === 'Barangay Admin' && $isPendingView && $pending_count > 0): ?>
                    <a href="<?= $base_url ?>/residents/approve-all"
                       class="action-button-link"
                       style="background-color: #28a745; color: white; margin-left: auto;"
                       onclick="return confirm('Are you sure you want to approve all <?= $pending_count ?> pending entries?');">
                        <span class="material-icons">done_all</span> Approve All (<?= $pending_count ?>)
                    </a>
                <?php endif; ?>
            </div>

            <div class="filter-wrapper" style="<?= $isPendingView ? 'display:none;' : '' ?>">
                <div class="filter-container">
                    <div class="main-filter-controls">
                        <div class="filter-group search-filter">
                            <label for="searchInput">Search by Name</label>
                            <input type="text" id="searchInput" placeholder="Enter name...">
                        </div>
                        
                        <div class="filter-group">
                            <label>Quick Age Groups</label>
                            <div class="button-group">
                                <button class="clear-btn demographic-btn" data-min="60">Seniors</button>
                                <button class="clear-btn demographic-btn" data-min="15" data-max="30">Youth</button>
                                 <button class="clear-btn demographic-btn" data-max="17">Minors</button>
                            </div>
                        </div>
                        <div class="filter-group">
                            <button id="toggleFiltersBtn" class="clear-btn" style="background-color: #ffffffff;">
                                Advanced Filters <span class="material-icons">expand_more</span>
                            </button>
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

                <div id="advanced-filters" class="accordion">
                    <div class="accordion-item">
                        <div class="accordion-header"><h4>Personal Info</h4><span class="material-icons">expand_more</span></div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group">
                                    <label for="genderFilter">Gender</label>
                                    <select id="genderFilter"><option value="">All</option><option value="Male">Male</option><option value="Female">Female</option></select>
                                </div>
                                <div class="filter-group age-filter">
                                    <label>Age Range</label>
                                    <div class="age-inputs"><input type="number" id="ageMin" placeholder="Min"><span>-</span><input type="number" id="ageMax" placeholder="Max"></div>
                                </div>
                                <div class="filter-group">
                                    <label for="civilStatusFilter">Civil Status</label>
                                    <select id="civilStatusFilter">
                                        <option value="">All</option>
                                        <?php foreach($civil_statuses as $cs): ?><option value="<?= htmlspecialchars($cs) ?>"><?= htmlspecialchars($cs) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="birthMonthFilter">Birthday Month</label>
                                    <select id="birthMonthFilter">
                                        <option value="">All</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option><?php endfor; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="nationalityFilter">Nationality</label>
                                    <select id="nationalityFilter">
                                        <option value="">All</option>
                                        <?php foreach($nationalities as $nat): ?><option value="<?= htmlspecialchars($nat) ?>"><?= htmlspecialchars($nat) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <div class="accordion-header"><h4>Address & Household</h4><span class="material-icons">expand_more</span></div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group"><label for="purokFilter">Purok</label>
                                    <select id="purokFilter">
                                        <option value="">All</option>
                                        <?php if (!$isPendingView) { $puroks = array_unique(array_column($residents, 'purok')); sort($puroks); foreach($puroks as $p) if(!empty($p)) echo "<option value=\"".htmlspecialchars($p)."\">".htmlspecialchars($p)."</option>"; } ?>
                                    </select>
                                </div>
                                <div class="filter-group"><label for="streetFilter">Street</label><input type="text" id="streetFilter" placeholder="Enter street name..."></div>
                                <div class="filter-group"><label for="houseNoFilter">House No.</label><input type="text" id="houseNoFilter" placeholder="Enter house no..."></div>
                                <div class="filter-group"><label for="householdFilter">Head of Household</label>
                                    <select id="householdFilter">
                                        <option value="">All</option>
                                        <?php if (!$isPendingView) { foreach($household_heads as $head) { echo "<option value=\"".htmlspecialchars($head)."\">".htmlspecialchars($head)."</option>"; } } ?>
                                    </select>
                                </div>
                                <div class="filter-group"><label for="relationshipFilter">Relationship to Head</label>
                                    <select id="relationshipFilter">
                                        <option value="">All</option>
                                        <?php foreach($relationships as $rel): ?><option value="<?= htmlspecialchars($rel) ?>"><?= htmlspecialchars($rel) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="ownershipStatusFilter">Ownership Status</label>
                                    <select id="ownershipStatusFilter">
                                        <option value="">All</option>
                                        <?php foreach($ownership_statuses as $os): ?><option value="<?= htmlspecialchars($os) ?>"><?= htmlspecialchars($os) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isHeadFilter"><span class="slider round"></span></label><label for="isHeadFilter">Is Head of Household?</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header"><h4>Contact & Health</h4><span class="material-icons">expand_more</span></div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group"><label for="bloodTypeFilter">Blood Type</label>
                                    <select id="bloodTypeFilter">
                                        <option value="">All</option>
                                        <?php foreach($blood_types as $bt): ?><option value="<?= htmlspecialchars($bt) ?>"><?= htmlspecialchars($bt) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="emergencyContactFilter"><span class="slider round"></span></label><label for="emergencyContactFilter">Has Emergency Contact?</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header"><h4>Education & Occupation</h4><span class="material-icons">expand_more</span></div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group"><label for="educationFilter">Educational Attainment</label>
                                    <select id="educationFilter">
                                        <option value="">All</option>
                                        <?php foreach($educations as $edu): ?><option value="<?= htmlspecialchars($edu) ?>"><?= htmlspecialchars($edu) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group"><label for="occupationFilter">Occupation</label>
                                    <select id="occupationFilter">
                                        <option value="">All</option>
                                        <?php foreach($occupations as $occ): ?><option value="<?= htmlspecialchars($occ) ?>"><?= htmlspecialchars($occ) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group"><label for="employmentStatusFilter">Employment Status</label>
                                    <select id="employmentStatusFilter">
                                        <option value="">All</option><option value="employed">Employed</option><option value="unemployed">Unemployed</option>
                                    </select>
                                </div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isStudentFilter"><span class="slider round"></span></label><label for="isStudentFilter">Is Student?</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header"><h4>Administrative & Welfare</h4><span class="material-icons">expand_more</span></div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group">
                                    <label for="statusFilter">Resident Status</label>
                                    <select id="statusFilter"><option value="">All</option><option value="Active">Active</option><option value="Inactive">Inactive</option><option value="Moved">Moved</option><option value="Deceased">Deceased</option></select>
                                </div>
                                <div class="filter-group">
                                    <label for="residencyStatusFilter">Residency Type</label>
                                    <select id="residencyStatusFilter">
                                        <option value="">All</option>
                                        <?php foreach($residency_statuses as $rs): ?><option value="<?= htmlspecialchars($rs) ?>"><?= htmlspecialchars($rs) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Date Added</label>
                                    <div class="age-inputs"><input type="date" id="dateAddedMin" placeholder="From"><input type="date" id="dateAddedMax" placeholder="To"></div>
                                </div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isVoterFilter"><span class="slider round"></span></label><label for="isVoterFilter">Is Registered Voter?</label></div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isPwdFilter"><span class="slider round"></span></label><label for="isPwdFilter">Is PWD?</label></div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isSoloParentFilter"><span class="slider round"></span></label><label for="isSoloParentFilter">Is Solo Parent?</label></div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="is4psMemberFilter"><span class="slider round"></span></label><label for="is4psMemberFilter">Is 4Ps Member?</label></div>
                                <div class="toggle-switch-group"><label class="switch"><input type="checkbox" id="isIndigentFilter"><span class="slider round"></span></label><label for="isIndigentFilter">Is Indigent?</label></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-area-wrapper">
                <?php if ($user['role_name'] === 'Barangay Admin'): ?>
                    <div class="view-tabs-container">
                        <div class="view-tabs">
                            <a href="<?= $base_url ?>/residents" class="view-tab <?= !$isPendingView ? 'active-view' : '' ?>">
                                <span class="material-icons">verified</span> Approved Residents
                            </a>
                            <a href="<?= $base_url ?>/residents?view=pending" class="view-tab <?= $isPendingView ? 'active-view' : '' ?>">
                                <?php if ($pending_count > 0): ?>
                                    <span class="notification-badge"><?= $pending_count ?></span>
                                <?php endif; ?>
                                <span class="material-icons">rate_review</span> Pending Review
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table class="resident-table" id="residentsTable">
                        <thead>
                            <tr class="table-controls-header">
                                <th colspan="6">
                                    <div id="pagination-controls" style="margin: 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                        <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                                            <div>
                                                <label>Show
                                                    <select id="pageSizeSelect" style="padding:0.3rem;">
                                                        <option value="10" <?= ($isPendingView && $pageSize == 10) ? 'selected' : '' ?>>10</option>
                                                        <option value="25" <?= ($isPendingView && $pageSize == 25) ? 'selected' : '' ?>>25</option>
                                                        <option value="50" <?= ($isPendingView && $pageSize == 50) ? 'selected' : '' ?>>50</option>
                                                    </select>
                                                entries</label>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                                <span>Showing <span id="shownCount">0–0</span> of <span id="totalCountEl"><?= $isPendingView ? $totalResidents : 0 ?></span></span>
                                            </div>
                                            <div style="margin: 0; font-weight: 500; display:none;" id="filteredResults">
                                                <span style="font-weight: 500;">(Filtered: <span id="filteredCount">0</span>)</span>
                                            </div>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                            <button id="prevPageBtn" style="padding:0.3rem 0.5rem;" <?= ($isPendingView && $currentPage <= 1) ? 'disabled' : '' ?>>Prev</button>
                                            <span id="pageInfo">Page <?= $isPendingView ? $currentPage : 1 ?> of <?= $isPendingView ? $totalPages : 1 ?></span>
                                            <button id="nextPageBtn" style="padding:0.3rem 0.5rem;" <?= ($isPendingView && $currentPage >= $totalPages) ? 'disabled' : '' ?>>Next</button>
                                            <input type="number" id="gotoPage" min="1" max="<?= $isPendingView ? $totalPages : 1 ?>" style="width:70px; padding:0.3rem;" placeholder="Page">
                                            <button id="gotoPageBtn" style="padding:0.3rem 0.5rem;">Go</button>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th class="sortable" data-sort="last_name">
                                    <div class="sort-header-content">
                                        <div class="sort-header-top-line">
                                            <span>Full Name</span>
                                            <div class="sort-dropdown-container">
                                                <span class="material-icons">arrow_drop_down</span>
                                                <select id="nameSortSelect" class="sort-select-overlay">
                                                    <option value="last_name-asc">Last Name (A-Z)</option>
                                                    <option value="last_name-desc">Last Name (Z-A)</option>
                                                    <option value="first_name-asc">First Name (A-Z)</option>
                                                    <option value="first_name-desc">First Name (Z-A)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <span class="sort-icon"></span>
                                    </div>
                                </th>
                                <th class="sortable" data-sort="age">Age <span class="sort-icon"></span></th>
                                <th>Gender</th>
                                <th>Address</th>
                                <th><?= $isPendingView ? 'Date Submitted' : 'Status' ?></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="residentsTableBody">
                            
                            <?php
                            // The partials are now included here
                            if ($isPendingView) {
                                include __DIR__ . '/_pending_rows.php';
                            } else {
                                include __DIR__ . '/_approved_placeholder.php';
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../components/resident_modal2.php'; ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script>
        const allResidentsData = <?= $isPendingView ? '[]' : json_encode($residents); ?>;
        const isPendingView = <?= $isPendingView ? 'true' : 'false' ?>;
        const userRole = '<?= htmlspecialchars($user['role_name']) ?>';
        <?php if ($isPendingView): ?>
        // This global var is used by pending_view.js
        const totalPages = <?= $totalPages ?>;
        <?php endif; ?>
    </script>
    
    <script type="module" src="<?= $base_url ?>/assets/js/residents.js"></script>
    
    <?php if ($isPendingView): ?>
    <script src="<?= $base_url ?>/assets/js/pending_view.js" defer></script>
    <?php endif; ?>

    <?php if (!empty($modalMessage)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast-notification <?= $modalType ?>';
            toast.innerHTML = `<span class="material-icons"><?= $modalType === 'success' ? 'check_circle' : 'error' ?></span><p><?= addslashes($modalMessage) ?></p>`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 4000);
        });
    </script>
    <?php endif; ?>
</body>
</html>