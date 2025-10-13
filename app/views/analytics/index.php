<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <?php $base_url = '/iCensus-ent/public'; ?>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/analytics1.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/report-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/8.2.1/gridstack-all.js"></script>
    <style>
        .date-filter-container { display: flex; align-items: flex-end; gap: 1rem; margin-left: auto; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 0.85rem; font-weight: 500; color: #495057; margin-bottom: 0.3rem; }
        .filter-group input[type="date"] { border: 1px solid #ccc; padding: 0.5rem; border-radius: 8px; font-size: 0.9rem; }
        .filter-group button { border: none; padding: 0.6rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; }
        #filter-btn { background-color: #0d6efd; color: white; }
        #clear-filter-btn { background-color: #6c757d; color: white; }
        body.dark-mode .filter-group label { color: #adb5bd; }
        body.dark-mode .filter-group input[type="date"] { background-color: #2C3E50; border-color: #4a5a6a; color: #fff; }
    </style>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>Analytics Dashboard</h2></div>

<main class="dashboard">
    <div class="dashboard-card">
        <div class="controls-wrapper">
            <div class="buttons-container">
                <button id="addChartBtn" style="background-color: #e0f2f1; color: #00796b;"><span class="material-icons">add_chart</span> Add New Chart</button>
                <button id="manageChartsBtn" style="background-color: #e3f2fd; color: #0d6efd;"><span class="material-icons">visibility</span> Manage Charts</button>
                <button id="generate-report-btn"><span class="material-icons">assessment</span> Generate Report</button>
                <button id="reset-layout-btn"><span class="material-icons">refresh</span> Reset Layout</button>
                <button id="save-layout-btn"><span class="material-icons">save</span> Save Layout</button>
            </div>
            
            <div class="date-filter-container">
                <div class="filter-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate">
                </div>
                <div class="filter-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate">
                </div>
                <div class="filter-group">
                    <button id="filter-btn" title="Apply Date Filter"><span class="material-icons">filter_alt</span></button>
                    <button id="clear-filter-btn" title="Clear Date Filter"><span class="material-icons">clear</span></button>
                </div>
            </div>
        </div>
        <hr class="separator-line">
        <div class="grid-stack"></div>
    </div>
</main>

<div id="chartDetailModal" class="modal">
    <div class="modal-content large">
        <span class="close-btn material-icons">close</span>
        <h3 id="chartDetailTitle">Chart Details</h3>
        <div class="modal-grid">
            <div id="chartDetailContent" class="chart-div" style="height: 100%;"></div>
            <div id="residentListContainer">
                <div class="list-placeholder">Click on a chart segment to see the list of residents.</div>
            </div>
        </div>
    </div>
</div>

<div id="analytics-resident-detail-modal" class="modal">
    <div class="modal-content">
        </div>
</div>

<?php include __DIR__ . '/../components/chart_builder_modal.php'; ?>
<?php include __DIR__ . '/../components/manage_charts_modal.php'; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="<?= $base_url ?>/assets/js/dynamic_analytics.js"></script>
<script src="<?= $base_url ?>/assets/js/chart_builder.js"></script>
<script src="<?= $base_url ?>/assets/js/manage_chart.js"></script> 

</body>
</html>