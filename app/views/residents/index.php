<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Residents</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/settings.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Residents Management</h2></div>

    <main class="dashboard">
        <div style="padding:0 2rem; max-width:1400px; margin:auto;">
            
            <button id="addResidentBtn" class="settings-card" style="cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
                <span class="material-icons">person_add</span> Add Resident
            </button>

            <p style="margin: 1rem 0; font-weight: 500;">
                Total Residents in Database: <span><?= count($residents); ?></span>
            </p>

            <div style="margin: 0.5rem 0; font-weight: 500; display:none;" id="filteredResults">
                 Filtered search results: <span id="filteredCount">0</span>
            </div>

            <div class="filter-container">
                <div class="filter-group search-filter">
                    <label for="searchInput">Search Resident</label>
                    <input type="text" id="searchInput" placeholder="by name or address...">
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
                    <label for="purokFilter">Purok</label>
                    <select id="purokFilter">
                        <option value="">All</option>
                        <?php
                        $puroks = array_unique(array_column($residents, 'purok'));
                        sort($puroks);
                        foreach($puroks as $p) if(!empty($p)) echo "<option value=\"".htmlspecialchars($p)."\">".htmlspecialchars($p)."</option>";
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button id="clearFiltersBtn" class="clear-btn">
                        Clear Filters
                    </button>
                </div>
            </div>

            <div style="margin: 1rem 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
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
                            <th>Full Name</th><th>Age</th><th>Gender</th><th>Address</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="residentsTableBody">
                        </tbody>
                </table>
            </div>
        </div>

        <?php include __DIR__ . '/../components/resident_modal2.php'; ?>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script>
        const allResidentsData = <?= json_encode($residents); ?>;
    </script>
    <script src="<?= $base_url ?>/assets/js/residents.js"></script>
</body>
</html>