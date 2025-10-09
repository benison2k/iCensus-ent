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
                <button id="add-widget-btn" style="background-color: #e8f5e9; color: #2e7d32;"><span class="material-icons">add</span> Add Widget</button>
                <button id="reset-layout-btn"><span class="material-icons">refresh</span> Reset Layout</button>
                <button id="save-layout-btn"><span class="material-icons">save</span> Save Layout</button>
            </div>
            
            <div class="date-filter-container">
                </div>
        </div>
        <hr class="separator-line">
        <div class="grid-stack"></div>
    </div>
</main>

<div id="widget-library-modal" class="modal">
    <div class="modal-content wide">
        <span class="close-btn">&times;</span>
        <h2 class="modal-title">Chart Library</h2>
        <div id="chart-list-container" style="margin-bottom: 1.5rem;">
            </div>
        <button id="create-new-chart-btn" class="btn-generate" style="background: #1e88e5;"><span class="material-icons">add_chart</span> Create New Chart</button>
    </div>
</div>

<div id="chart-builder-modal" class="modal">
    <div class="modal-content wide">
        <span class="close-btn">&times;</span>
        <h2 id="chart-builder-title" class="modal-title">Create New Chart</h2>
        <form id="chart-builder-form">
            <input type="hidden" name="chart_id" id="chart_id_input">
            <div class="modal-form-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
                
                <fieldset>
                    <legend>Basic Info</legend>
                    <div class="form-group">
                        <label for="chart-title">Chart Title</label>
                        <input type="text" id="chart-title" name="title" required placeholder="e.g., Population by Purok">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Display</legend>
                    <div class="form-group">
                        <label for="chart-type">Display As</label>
                        <select id="chart-type" name="chart_type" required>
                            <option value="PieChart">Pie Chart</option>
                            <option value="BarChart">Bar Chart (Horizontal)</option>
                            <option value="ColumnChart">Column Chart (Vertical)</option>
                            <option value="KPI">KPI (Single Number)</option>
                        </select>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Metric (The "What")</legend>
                    <div class="form-group">
                        <label for="aggregate-function">Calculation</label>
                        <select id="aggregate-function" name="aggregate_function" required>
                            <option value="COUNT">Count of Residents</option>
                            <option value="AVG">Average Age</option>
                        </select>
                    </div>
                     <div class="form-group">
                        <label for="aggregate-column">Data to Calculate</label>
                        <select id="aggregate-column" name="aggregate_column" required>
                            <option value="*">All Residents (*)</option>
                            <option value="dob">Age (dob)</option>
                        </select>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Dimension (The "How")</legend>
                    <div class="form-group">
                        <label for="group-by-column">Group By</label>
                        <select id="group-by-column" name="group_by_column">
                            <option value="">None (for KPIs)</option>
                            <option value="gender">Gender</option>
                            <option value="purok">Purok</option>
                            <option value="civil_status">Civil Status</option>
                            <option value="educational_attainment">Educational Attainment</option>
                            <option value="is_pwd">Is PWD?</option>
                            <option value="is_solo_parent">Is Solo Parent?</option>
                            <option value="is_4ps_member">Is 4Ps Member?</option>
                            <option value="status">Resident Status</option>
                        </select>
                    </div>
                </fieldset>

            </div>
            <button type="submit" class="btn-generate">Save Chart</button>
        </form>
    </div>
</div>


<?php include __DIR__ . '/../components/footer.php'; ?>
<script type="module" src="<?= $base_url ?>/assets/js/analytics-main.js"></script>
</body>
</html>