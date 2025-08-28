<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database($config);
$auth = new Auth($db);
$auth->refreshUserSession($_SESSION['user']['id']); 

$user = $_SESSION['user'];
$theme = $user['theme'] ?? 'light';

// Modal setup
$modalMessage = $_SESSION['modal']['message'] ?? '';
$modalType = $_SESSION['modal']['type'] ?? '';
unset($_SESSION['modal']);

// Fetch residents
$pdo = $db->getPdo();
$stmt = $pdo->query("SELECT * FROM residents ORDER BY last_name ASC");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper: calculate age
function calculateAge($dob) {
    $dobObj = new DateTime($dob);
    $now = new DateTime();
    return $dobObj->diff($now)->y;
}

// Helper: get full name with middle initial
function formatName($r) {
    $middleInitial = $r['middle_name'] ? strtoupper($r['middle_name'][0]).'.' : '';
    return trim("{$r['first_name']} {$middleInitial} {$r['last_name']}");
}
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

<div style="padding:0 2rem; max-width:1200px; margin:auto;">
    <!-- Add Resident Button -->
    <button id="addResidentBtn" class="settings-card" style="cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
        <span class="material-icons">person_add</span> Add Resident
    </button>

    <!-- Filters -->
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
            $puroks = array_unique(array_map(fn($r) => $r['purok'], $residents));
            sort($puroks);
            foreach($puroks as $p) echo "<option value=\"".htmlspecialchars($p)."\">$p</option>";
            ?>
        </select>

        <select id="barangayFilter" style="padding:0.5rem;">
            <option value="">All Barangays</option>
            <?php
            $barangays = array_unique(array_map(fn($r) => $r['barangay'], $residents));
            sort($barangays);
            foreach($barangays as $b) echo "<option value=\"".htmlspecialchars($b)."\">$b</option>";
            ?>
        </select>

        <!-- Clear Filters Button -->
        <button id="clearFiltersBtn" style="padding:0.5rem; background:#ccc; border:none; border-radius:5px; cursor:pointer;">
            Clear Filters
        </button>
    </div>

    <!-- Residents Table -->
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
            <?php foreach($residents as $r): 
                $address = "{$r['house_no']} {$r['street']}, Purok {$r['purok']}, {$r['barangay']}";
                $statusClass = strtolower($r['status']);
                $fullName = formatName($r);
            ?>
            <tr data-id="<?= $r['id']; ?>" data-status="<?= htmlspecialchars($r['status']); ?>" 
                data-gender="<?= htmlspecialchars($r['gender']); ?>" data-age="<?= calculateAge($r['dob']); ?>"
                data-purok="<?= htmlspecialchars($r['purok']); ?>" data-barangay="<?= htmlspecialchars($r['barangay']); ?>">
                <td><?= htmlspecialchars($fullName); ?></td>
                <td><?= calculateAge($r['dob']); ?></td>
                <td><?= htmlspecialchars($r['gender']); ?></td>
                <td><?= htmlspecialchars($address); ?></td>
                <td><span class="status-label status-<?= $statusClass; ?>"><?= htmlspecialchars($r['status']); ?></span></td>
                <td>
                    <button class="moreBtn material-icons" data-id="<?= $r['id']; ?>" title="View Resident Info">more_vert</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit/View Resident Modal -->
<div id="residentModal" class="modal">
    <div class="modal-content modal-landscape">
        <span class="close"><span class="material-icons">close</span></span>
        <h3 id="modalTitle">Resident Info</h3>

        <form id="residentForm" method="POST" action="../core/residents_process.php">
            <div class="modal-columns">

                <!-- Left Column: Personal & Contact Info -->
                <div class="modal-col">
                    <h4>Personal Info</h4>
                    <div class="form-group">
                        <label><span class="material-icons">person</span> First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">badge</span> Middle Name</label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">badge</span> Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">cake</span> Date of Birth</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">wc</span> Gender</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <h4>Contact Info</h4>
                    <div class="form-group">
                        <label><span class="material-icons">phone</span> Contact Number</label>
                        <input type="text" name="contact_number">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">email</span> Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">person_add</span> Emergency Name</label>
                        <input type="text" name="emergency_name">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">supervised_user_circle</span> Relation</label>
                        <input type="text" name="emergency_relation">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">phone_in_talk</span> Emergency Number</label>
                        <input type="text" name="emergency_number">
                    </div>
                </div>

                <!-- Right Column: Address & Residency Info -->
                <div class="modal-col">
                    <h4>Address</h4>
                    <div class="form-group">
                        <label><span class="material-icons">home</span> House No.</label>
                        <input type="text" name="house_no" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">streetview</span> Street</label>
                        <input type="text" name="street" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">apartment</span> Purok</label>
                        <input type="text" name="purok" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">location_city</span> Barangay</label>
                        <input type="text" name="barangay" required>
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">house</span> Head of Household</label>
                        <input type="text" name="head_of_household">
                    </div>
                    <div class="form-group">
                        <label><span class="material-icons">groups</span> Relationship</label>
                        <input type="text" name="relationship">
                    </div>

                    <h4>Residency Info</h4>
                    <div class="form-group">
                        <label><span class="material-icons">assignment_ind</span> Status</label>
                        <select name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Moved">Moved</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="editBtn material-icons">edit</button>
                <button type="button" class="deleteBtn material-icons">delete</button>
                <button type="submit" name="saveResident" id="saveBtn" style="display:none;">
                    <span class="material-icons">save</span> Save
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="../assets/js/residents.js"></script>
<script>
window.addEventListener('load',()=>{document.body.style.overflow='';});
</script>
</body>
</html>
