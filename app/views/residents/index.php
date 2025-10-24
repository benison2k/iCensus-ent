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
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Residents Management</h2></div>

    <main class="dashboard dashboard-management">
        <div id="ajaxResultModal" class="modal" data-show="false">
            <div class="modal-content">
                <span class="close">&times;</span>
                <p id="ajaxResultMessage"></p>
            </div>
        </div>

        <div style="padding:0 2rem; max-width:1600px; margin:auto; width: 100%;">

        <div class="page-actions-container">
                <button id="addResidentBtn" class="action-button-link">
                    <span class="material-icons">person_add</span> Add Resident
                </button>

                <?php if ($user['role_name'] === 'Barangay Admin'): ?>

                    <a href="<?= $base_url ?>/residents?view=pending"
                       class="action-button-link card <?= $isPendingView ? 'active-view' : '' ?>"
                       style="text-decoration: none; padding: 0.6rem 1rem; min-width: auto; min-height: auto;">

                        <?php if ($pending_count > 0): ?>
                            <span class="notification-badge"><?= $pending_count ?></span>
                        <?php endif; ?>

                        <span class="material-icons">rate_review</span> Pending Review
                    </a>

                     <a href="<?= $base_url ?>/residents" class="action-button-link <?= !$isPendingView ? 'active-view' : '' ?>" style="text-decoration: none;">
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

            
            <div class="filter-wrapper" style="<?= $isPendingView ? 'display:none;' : '' ?>">
                <div class="filter-container">
                    <div class="main-filter-controls">
                        <div class="filter-group search-filter">
                            <label for="searchInput">Search by Name</label>
                            <input type="text" id="searchInput" placeholder="Enter name...">
                        </div>
                        
                        <div class="filter-group">
                            <label>Attributes</label>
                            <div class="toggle-switch-group">
                                <label class="switch">
                                    <input type="checkbox" id="isVoterFilter">
                                    <span class="slider"></span>
                                </label>
                                <label for="isVoterFilter">Is Voter?</label>
                            </div>
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
                        <div class="accordion-header">
                            <h4>Demographics</h4>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
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
                                    <label for="civilStatusFilter">Civil Status</label>
                                    <select id="civilStatusFilter">
                                        <option value="">All</option>
                                        <?php foreach($civil_statuses as $cs): ?>
                                            <option value="<?= htmlspecialchars($cs) ?>"><?= htmlspecialchars($cs) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                 <div class="filter-group">
                                    <label for="birthMonthFilter">Birthday Month</label>
                                    <select id="birthMonthFilter">
                                        <option value="">All</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h4>Address & Household</h4>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
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
                                    <label for="streetFilter">Street</label>
                                    <input type="text" id="streetFilter" placeholder="Enter street name...">
                                </div>
                                <div class="filter-group">
                                    <label for="houseNoFilter">House No.</label>
                                    <input type="text" id="houseNoFilter" placeholder="Enter house no...">
                                </div>
                                <div class="filter-group">
                                    <label for="householdFilter">Head of Household</label>
                                    <select id="householdFilter">
                                        <option value="">All</option>
                                        <?php
                                        if (!$isPendingView) {
                                            foreach($household_heads as $head) {
                                                echo "<option value=\"".htmlspecialchars($head)."\">".htmlspecialchars($head)."</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="isHeadFilter">Is Head of Household?</label>
                                    <select id="isHeadFilter">
                                        <option value="">All</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="relationshipFilter">Relationship to Head</label>
                                    <select id="relationshipFilter">
                                        <option value="">All</option>
                                        <?php foreach($relationships as $rel): ?>
                                            <option value="<?= htmlspecialchars($rel) ?>"><?= htmlspecialchars($rel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h4>Welfare & Education</h4>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group">
                                    <label for="employmentStatusFilter">Employment Status</label>
                                    <select id="employmentStatusFilter">
                                        <option value="">All</option>
                                        <option value="employed">Employed</option>
                                        <option value="unemployed">Unemployed</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="isStudentFilter">Is Student?</label>
                                    <select id="isStudentFilter">
                                        <option value="">All</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="educationFilter">Educational Attainment</label>
                                    <select id="educationFilter">
                                        <option value="">All</option>
                                        <?php foreach($educations as $edu): ?>
                                            <option value="<?= htmlspecialchars($edu) ?>"><?= htmlspecialchars($edu) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="occupationFilter">Occupation</label>
                                    <select id="occupationFilter">
                                        <option value="">All</option>
                                        <?php foreach($occupations as $occ): ?>
                                            <option value="<?= htmlspecialchars($occ) ?>"><?= htmlspecialchars($occ) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="isPwdFilter">Is PWD?</label>
                                    <select id="isPwdFilter"><option value="">All</option><option value="1">Yes</option><option value="0">No</option></select>
                                </div>
                                <div class="filter-group">
                                    <label for="isSoloParentFilter">Is Solo Parent?</label>
                                    <select id="isSoloParentFilter"><option value="">All</option><option value="1">Yes</option><option value="0">No</option></select>
                                </div>
                                 <div class="filter-group">
                                    <label for="is4psMemberFilter">Is 4Ps Member?</label>
                                    <select id="is4psMemberFilter"><option value="">All</option><option value="1">Yes</option><option value="0">No</option></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h4>Administrative</h4>
                            <span class="material-icons">expand_more</span>
                        </div>
                        <div class="accordion-content">
                            <div class="filter-fieldset">
                                <div class="filter-group">
                                    <label for="statusFilter">Resident Status</label>
                                    <select id="statusFilter">
                                        <option value="">All</option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Moved">Moved</option>
                                        <option value="Deceased">Deceased</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="residencyStatusFilter">Residency Type</label>
                                    <select id="residencyStatusFilter">
                                        <option value="">All</option>
                                         <?php foreach($residency_statuses as $rs): ?>
                                            <option value="<?= htmlspecialchars($rs) ?>"><?= htmlspecialchars($rs) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="bloodTypeFilter">Blood Type</label>
                                    <select id="bloodTypeFilter">
                                        <option value="">All</option>
                                         <?php foreach($blood_types as $bt): ?>
                                            <option value="<?= htmlspecialchars($bt) ?>"><?= htmlspecialchars($bt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                 <div class="filter-group">
                                    <label for="emergencyContactFilter">Has Emergency Contact?</label>
                                    <select id="emergencyContactFilter">
                                        <option value="">All</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                 <div class="filter-group">
                                    <label>Date Added</label>
                                    <div class="age-inputs">
                                        <input type="date" id="dateAddedMin" placeholder="From">
                                        <input type="date" id="dateAddedMax" placeholder="To">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="pagination-controls" style="margin: 1rem 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; <?= $isPendingView ? 'display:none;' : '' ?>">
                <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
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

                    <div style="margin: 0; font-weight: 500; display:none;" id="filteredResults">
                         <span style="font-weight: 500;">(Filtered: <span id="filteredCount">0</span>)</span>
                    </div>
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
                        <tr><td colspan="6" style="text-align: center;">Loading residents...</td></tr>
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
        const userRole = '<?= htmlspecialchars($user['role_name']) ?>';
    </script>
    <script src="<?= $base_url ?>/assets/js/residents.js"></script>
</body>
</html>