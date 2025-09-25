<?php
session_start();

// --- Bouncer ---
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Not logged in
    exit;
}
// Correct roles that can access this page
$allowed_roles = ['Barangay Admin', 'Encoder']; 
if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
    http_response_code(403);
    die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>");
}
// --- End Bouncer ---

// Get user role for conditionally showing elements
$user_role = $_SESSION['user']['role_name'];

$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

$db = new Database($config);
$auth = new Auth($db);
$auth->refreshUserSession($_SESSION['user']['id']); 

$user = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';

// Modal setup
$modalMessage = $_SESSION['modal']['message'] ?? '';
$modalType = $_SESSION['modal']['type'] ?? '';
unset($_SESSION['modal']);

// Fetch initial data for filters
$pdo = $db->getPdo();
$stmt = $pdo->query("SELECT purok, barangay FROM residents");
$all_residents_for_filters = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_residents_count = count($all_residents_for_filters);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Residents</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/modal.css">
<link rel="stylesheet" href="../assets/css/residents.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Residents Management</h2></div>

<main class="dashboard">
<?php if ($modalMessage):
    $id="resultModal"; $message=$modalMessage; $type=$modalType;
    include __DIR__ . '/../components/modal.php';
endif; ?>

<div style="padding:0 2rem; max-width:1400px; margin:auto;">
    
    <?php 
    // --- THIS IS THE FIX ---
    // Allow both Barangay Admins and Encoders to see the button
    $allowed_roles_for_adding = ['Barangay Admin', 'Encoder'];
    if (in_array($user_role, $allowed_roles_for_adding)): 
    ?>
        <button id="addResidentBtn" class="settings-card" style="cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
            <span class="material-icons">person_add</span> Add Resident
        </button>
    <?php endif; ?>

    <p style="margin: 1rem 0; font-weight: 500;">
        Total Residents in Database: <span id="totalCount"><?= $total_residents_count; ?></span>
    </p>

    <div style="margin: 0.5rem 0; font-weight: 500; display:none;" id="filteredResults">
         Filtered search results: <span id="filteredCount">0</span>
    </div>

    <div style="margin-top:1rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
        <input type="text" id="searchInput" placeholder="Search by name or address" style="padding:0.5rem; flex:1;">
        <select id="statusFilter" style="padding:0.5rem;">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Moved">Moved</option>
            <option value="Deceased">Deceased</option>
        </select>
        <select id="genderFilter" style="padding:0.5rem;">
            <option value="">All Genders</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <input type="number" id="ageMin" placeholder="Min Age" style="padding:0.5rem; width:100px;">
        <input type="number" id="ageMax" placeholder="Max Age" style="padding:0.5rem; width:100px;">
        <select id="purokFilter" style="padding:0.5rem;">
            <option value="">All Puroks</option>
            <?php
            $puroks = array_unique(array_column($all_residents_for_filters, 'purok'));
            sort($puroks);
            foreach($puroks as $p) if(!empty($p)) echo "<option value=\"".htmlspecialchars($p)."\">".htmlspecialchars($p)."</option>";
            ?>
        </select>
        <select id="barangayFilter" style="padding:0.5rem;">
            <option value="">All Barangays</option>
            <?php
            $barangays = array_unique(array_column($all_residents_for_filters, 'barangay'));
            sort($barangays);
            foreach($barangays as $b) if(!empty($b)) echo "<option value=\"".htmlspecialchars($b)."\">".htmlspecialchars($b)."</option>";
            ?>
        </select>
        <button id="clearFiltersBtn" style="padding:0.5rem; background:#ccc; border:none; border-radius:5px; cursor:pointer;">
            Clear Filters
        </button>
    </div>

    <div style="margin: 1rem 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
        <div>
            <label>Show
                <select id="pageSizeSelect" style="padding:0.3rem;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            entries</label>
        </div>
        <div style="margin-left:auto; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span>Showing <span id="shownCount">0–0</span> of <span id="totalCountEl"><?= $total_residents_count ?></span></span>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-left:1rem;">
            <button id="prevPageBtn" style="padding:0.3rem 0.5rem;">Prev</button>
            <span id="pageInfo">Page 1 of 1</span>
            <button id="nextPageBtn" style="padding:0.3rem 0.5rem;">Next</button>

            <input type="number" id="gotoPage" min="1" style="width:70px; padding:0.3rem;" placeholder="Page">
            <button id="gotoPageBtn" style="padding:0.3rem 0.5rem;">Go</button>
        </div>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="resident-table" id="residentsTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="residentsTableBody">
                </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../components/resident_modal2.php'; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../assets/js/residents.js"></script>
</body>
</html>