<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <?php $base_url = '/iCensus-ent/public'; ?>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/analytics.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/report-modal.css">
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
    <div class="modal-content wide">
        <span class="close-btn">&times;</span>
        <h2 class="modal-title">Generate Custom Report</h2>
        <form action="<?= $base_url ?>/analytics/report" method="post" target="_blank">
            <div class="modal-form-grid">
                <div class="modal-form-column">
                     <fieldset>
                        <legend><span class="material-icons">description</span> Report Data</legend>
                        <div class="checkbox-group">
                            <?php foreach($available_columns as $key => $label): ?>
                                <label><input type="checkbox" name="columns[]" value="<?= $key ?>" <?= in_array($key, ['full_name', 'address', 'age', 'gender', 'status']) ? 'checked' : '' ?>> <?= $label ?></label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-form-column">
                    <fieldset>
                        <legend><span class="material-icons">settings</span> Configuration</legend>
                        <div class="form-group">
                            <label for="orientation">Orientation:</label>
                            <select name="orientation" id="orientation"><option value="portrait" selected>Portrait</option><option value="landscape">Landscape</option></select>
                        </div>
                        <div class="form-group">
                            <label for="font_size">Font Size:</label>
                            <select name="font_size" id="font_size"><option value="10px">Small</option><option value="12px" selected>Normal</option><option value="14px">Large</option></select>
                        </div>
                         <div class="form-group">
                            <label for="sort_by">Sort Table By:</label>
                            <select name="sort_by" id="sort_by"><option value="last_name">Last Name</option><option value="first_name">First Name</option><option value="date_added">Date Added</option><option value="dob">Age</option></select>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Order:</label>
                            <select name="sort_order" id="sort_order"><option value="ASC">Ascending</option><option value="DESC">Descending</option></select>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-form-column">
                    <fieldset>
                        <legend><span class="material-icons">pie_chart</span> Visualizations</legend>
                        <div class="checkbox-group">
                            <?php foreach($available_charts as $key => $label): ?>
                                <label><input type="checkbox" name="charts[]" value="<?= $key ?>"> <?= $label ?></label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>
            </div>
            <button type="submit" class="btn-generate">Generate Report</button>
        </form>
    </div>
</div>

<div id="chart-detail-modal" class="modal">
    <div class="modal-content large">
        <span class="close-btn">&times;</span>
        <div class="modal-grid">
            <div id="chart-detail-content">
                </div>
            <div class="chart-info-panel">
                <h3 id="chart-detail-title"></h3>
                <p id="chart-detail-explanation"></p>
                <div class="chart-interaction-tip">
                    <span class="material-icons">ads_click</span>
                    <p>Click on a chart segment (e.g., a pie slice or bar) to view the filtered residents.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="filtered-residents-modal" class="modal">
    <div class="modal-content large">
        <span class="close-btn">&times;</span>
        <h3 id="filtered-title">Filtered Residents</h3>
        <div class="table-responsive" style="overflow-y: auto; max-height: 60vh;">
            <table class="resident-table" id="filtered-residents-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div id="analytics-resident-detail-modal" class="modal">
    <div class="modal-content" style="max-width: 800px;"> <span class="close-btn">&times;</span>
        <h3 id="detail-modal-title">Resident Details</h3>
        <div id="detail-modal-content" class="resident-details-grid">
            <p>Loading...</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="<?= $base_url ?>/assets/js/analytics.js"></script>
</body>
</html>