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
$pdo = $db->getPdo();

// Fetch distinct values for filters
$puroks = $pdo->query("SELECT DISTINCT purok FROM residents WHERE purok IS NOT NULL AND purok != '' ORDER BY purok")->fetchAll(PDO::FETCH_COLUMN);
$civil_statuses = $pdo->query("SELECT DISTINCT civil_status FROM residents WHERE civil_status IS NOT NULL AND civil_status != '' ORDER BY civil_status")->fetchAll(PDO::FETCH_COLUMN);

// Define available columns for the report
$available_columns = [
    'full_name' => 'Full Name',
    'address' => 'Full Address',
    'dob' => 'Date of Birth',
    'age' => 'Age',
    'gender' => 'Gender',
    'civil_status' => 'Civil Status',
    'contact_number' => 'Contact Number',
    'email' => 'Email',
    'blood_type' => 'Blood Type',
    'nationality' => 'Nationality',
    'status' => 'Resident Status',
    'date_added' => 'Date Added'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/analytics.css">
    <link rel="stylesheet" href="../assets/css/report-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack-all.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Analytics Dashboard</h2></div>

<main class="dashboard">
    <div class="dashboard-card">
        <div class="controls-wrapper">
            <div class="buttons-container">
                <button id="generate-report-btn"><span class="material-icons">assessment</span> Generate Report</button>
                <button id="reset-layout-btn"><span class="material-icons">refresh</span> Reset Layout</button>
                <button id="save-layout-btn"><span class="material-icons">save</span> Save Layout</button>
            </div>
        </div>
        <hr class="separator-line">
        <div class="grid-stack"></div>
    </div>
</main>

<div id="report-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Generate Custom Report</h2>
        <form action="../core/generate_report.php" method="post" target="_blank">
            <div class="modal-form-columns">
                <div class="modal-form-column">
                    <fieldset>
                        <legend>Sorting & Ordering</legend>
                        <div class="form-group-inline">
                            <div class="form-group">
                                <label for="sort_by">Sort By:</label>
                                <select name="sort_by" id="sort_by">
                                    <option value="last_name">Last Name</option>
                                    <option value="first_name">First Name</option>
                                    <option value="date_added">Date Added</option>
                                    <option value="dob">Age</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sort_order">Order:</label>
                                <select name="sort_order" id="sort_order">
                                    <option value="ASC">Ascending</option>
                                    <option value="DESC">Descending</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Filters</legend>
                        <div class="form-group-inline">
                             <div class="form-group">
                                <label for="filter_purok">Purok:</label>
                                <select name="filter_purok" id="filter_purok">
                                    <option value="">All</option>
                                    <?php foreach ($puroks as $purok) echo "<option value=\"" . htmlspecialchars($purok) . "\">" . htmlspecialchars($purok) . "</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filter_gender">Gender:</label>
                                <select name="filter_gender" id="filter_gender">
                                    <option value="">All</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-inline">
                             <div class="form-group">
                                <label for="filter_civil_status">Civil Status:</label>
                                <select name="filter_civil_status" id="filter_civil_status">
                                    <option value="">All</option>
                                    <?php foreach ($civil_statuses as $status) echo "<option value=\"" . htmlspecialchars($status) . "\">" . htmlspecialchars($status) . "</option>"; ?>
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="filter_status">Resident Status:</label>
                                <select name="filter_status" id="filter_status">
                                    <option value="">All</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Moved">Moved</option>
                                    <option value="Deceased">Deceased</option>
                                </select>
                            </div>
                        </div>
                         <div class="form-group-inline">
                            <div class="form-group">
                                <label for="age_min">Min Age:</label>
                                <input type="number" name="age_min" id="age_min" min="0" placeholder="e.g., 18">
                            </div>
                            <div class="form-group">
                                <label for="age_max">Max Age:</label>
                                <input type="number" name="age_max" id="age_max" min="0" placeholder="e.g., 60">
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-form-column">
                    <fieldset>
                        <legend>Columns to Include</legend>
                        <div class="checkbox-group">
                            <?php foreach($available_columns as $key => $label): ?>
                                <label><input type="checkbox" name="columns[]" value="<?= $key ?>" <?= in_array($key, ['full_name', 'address', 'age', 'gender', 'status']) ? 'checked' : '' ?>> <?= $label ?></label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Formatting</legend>
                        <div class="form-group">
                            <label for="font_size">Font Size:</label>
                            <select name="font_size" id="font_size">
                                <option value="10px">Small</option>
                                <option value="12px" selected>Normal</option>
                                <option value="14px">Large</option>
                            </select>
                        </div>
                    </fieldset>
                </div>
            </div>
            <button type="submit" class="btn-generate">Generate Report</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="../assets/js/analytics.js"></script>
</body>
</html>