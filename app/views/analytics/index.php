<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <?php $base_url = '/iCensus-ent/public'; ?>
    <link rel="icon" type="image/png" href="/iCensus-ent/public/assets/img/iCensusLogoOnly2.png">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/analytics1.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/report-modal.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/chart_builder_modal.css">
    
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/page_actions.css">
    
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
                <button id="addChartBtn" style="background-color: #e0f2f1; color: #00796b;"><span class="material-icons">add_chart</span> Add New Chart</button>
                <button id="manageChartsBtn" style="background-color: #e3f2fd; color: #0d6efd;"><span class="material-icons">visibility</span> Manage Charts</button>
                <button id="generate-report-btn"><span class="material-icons">assessment</span> Generate Report</button>
                <button id="reset-layout-btn"><span class="material-icons">refresh</span> Reset Layout</button>
                <button id="save-layout-btn"><span class="material-icons">save</span> Save Layout</button>
            </div>
            <div class="toggle-switch-group" style="background-color: transparent;">
                <label for="autoFillSwitch">Auto-fill</label>
                <label class="switch">
                    <input type="checkbox" id="autoFillSwitch">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        <hr class="separator-line">
        <div class="grid-stack"></div>
    </div>
</main>

<div id="chartDetailModal" class="modal">
    <div class="modal-content large">
        <span class="close-btn material-icons">close</span>
        <div class="modal-header-controls">
            <h3 id="chartDetailTitle">Chart Details</h3>
            <div class="modal-date-filter">
                <input type="date" id="modalStartDate">
                <span>to</span>
                <input type="date" id="modalEndDate">
                <button id="modalFilterBtn" title="Apply Date Filter"><span class="material-icons">filter_alt</span></button>
                <button id="modalClearBtn" title="Clear Date Filter"><span class="material-icons">clear</span></button>
                
                <button id="editChartFromModalBtn" class="action-btn btn-edit" title="Edit Chart" style="margin-left: 1rem;">
                    <span class="material-icons">edit</span>
                </button>
                <button id="hideChartFromModalBtn" class="action-btn btn-hide" title="Hide Chart">
                    <span class="material-icons">visibility_off</span>
                </button>
                <button id="deleteChartFromModalBtn" class="action-btn btn-delete" title="Delete Chart">
                    <span class="material-icons">delete_forever</span>
                </button>
            </div>
        </div>
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
<?php include __DIR__ . '/../components/report_modal.php'; ?>
<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="<?= $base_url ?>/assets/js/dynamic_analytics.js"></script>
<script src="<?= $base_url ?>/assets/js/chart_builder.js"></script>
<script src="<?= $base_url ?>/assets/js/manage_chart.js"></script> 

</body>
</html>