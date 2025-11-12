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
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/resident_modal.css">
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


<div id="analytics-resident-detail-modal" class="modal modal-modern">
    <div class="modal-modern-content">
        <div class="modal-modern-header">
            <h3 id="analyticsModalTitle">Resident Information</h3>
            <span class="close-btn material-icons" style="font-size:1.8rem; cursor:pointer;">close</span>
        </div>
        <form id="analyticsResidentForm" method="POST" action="/iCensus-ent/public/residents/process" style="display: contents;">
            <div class="modal-modern-body">
                <div class="modal-tabs">
                    <button type="button" class="tab-button active" data-tab="personal"><span class="material-icons">person</span> Personal</button>
                    <button type="button" class="tab-button" data-tab="household"><span class="material-icons">home</span> Household</button>
                    <button type="button" class="tab-button" data-tab="contact"><span class="material-icons">contact_phone</span> Contact</button>
                    <button type="button" class="tab-button" data-tab="other"><span class="material-icons">assignment</span> Other Info</button>
                </div>

                <div class="modal-form-area" style="padding-top: 1.5rem;">
                    <input type="hidden" name="resident_id" id="analytics_resident_id">

                    <div style="padding: 0 2rem;">
                        <p class="progress-label" id="analyticsFormProgressLabel">Required Information Completeness:</p>
                        <div class="progress-container">
                            <div class="progress-bar" id="analyticsFormProgressBar"></div>
                        </div>
                        <p style="font-size: 0.8rem; text-align: right; margin-top: -1rem; margin-bottom: 1rem; color: #666;">Fields marked with <span class="required-asterisk">*</span> are required.</p>
                    </div>
                    
                    <div id="tab-personal" class="tab-content active">
                        <h4>Personal Details</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>First Name<span class="required-asterisk">*</span></label><input type="text" name="first_name" required></div>
                            <div class="form-group"><label>Last Name<span class="required-asterisk">*</span></label><input type="text" name="last_name" required></div>
                            <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name"></div>
                            <div class="form-group"><label>Suffix</label><input type="text" name="suffix"></div>
                            <div class="form-group"><label>Date of Birth<span class="required-asterisk">*</span></label><input type="date" name="dob" required></div>
                            <div class="form-group"><label>Gender<span class="required-asterisk">*</span></label><select name="gender" required><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                            <div class="form-group"><label>Civil Status</label><select name="civil_status"><option value="">Select</option><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Separated">Separated</option></select></div>
                            <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="Filipino"></div>
                        </div>
                    </div>
                    
                    <div id="tab-household" class="tab-content">
                        <h4>Address & Household</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>House No.<span class="required-asterisk">*</span></label><input type="number" name="house_no" required></div>
                            <div class="form-group"><label>Purok<span class="required-asterisk">*</span></label><input type="number" name="purok" required></div>
                            <div class="form-group full-width"><label>Street<span class="required-asterisk">*</span></label><input type="text" name="street" required></div>
                            <div class="form-group"><label>Household No.</label><input type="text" name="household_no" placeholder="e.g., FAM-001"></div>
                            <div class="form-group"><label>Ownership Status</label><select name="ownership_status"><option value="">Select</option><option value="Owned">Owned</option><option value="Rented">Rented</option><option value="Living with Relatives">Living with Relatives</option></select></div>
                            <div class="form-group"><label>Head of Household</label><input type="text" name="head_of_household"></div>
                            <div class="form-group"><label>Relationship to Head</label><input type="text" name="relationship"></div>
                        </div>
                    </div>

                    <div id="tab-contact" class="tab-content">
                        <h4>Contact & Health</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number"></div>
                            <div class="form-group"><label>Email Address</label><input type="email" name="email"></div>
                            <div class="form-group"><label>PhilHealth No.</label><input type="text" name="philhealth_no"></div>
                            <div class="form-group"><label>Blood Type</label><input type="text" name="blood_type"></div>
                            <hr style="grid-column: 1 / -1; border: 0; border-top: 1px solid #e0e0e0; margin: 0.5rem 0;">
                            <div class="form-group"><label>Emergency Contact Name</label><input type="text" name="emergency_name"></div>
                            <div class="form-group"><label>Emergency Contact Number</label><input type="text" name="emergency_number"></div>
                            <div class="form-group full-width"><label>Relation to Emergency Contact</label><input type="text" name="emergency_relation"></div>
                        </div>
                    </div>

                    <div id="tab-other" class="tab-content">
                        <h4>Administrative & Other Info</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>Educational Attainment</label><select name="educational_attainment"><option value="">Select</option><option value="No Formal Education">No Formal Education</option><option value="Pre-school">Pre-school</option><option value="Elementary Level">Elementary Level</option><option value="Elementary Graduate">Elementary Graduate</option><option value="High School Level">High School Level</option><option value="High School Graduate">High School Graduate</option><option value="Vocational Graduate">Vocational Graduate</option><option value="College Level">College Level</option><option value="College Graduate">College Graduate</option><option value="Doctorate Degree">Doctorate Degree</option></select></div>
                            <div class="form-group"><label>Occupation</label><input type="text" name="occupation"></div>
                            <div class="form-group"><label>Status</label><select name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option><option value="Moved">Moved</option><option value="Deceased">Deceased</option></select></div>
                            <div class="form-group"><label>Registered Voter</label><select name="is_registered_voter"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>PWD</label><select name="is_pwd"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>Solo Parent</label><select name="is_solo_parent"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>Indigent</label><select name="is_indigent"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>4Ps Member</label><select name="is_4ps_member"><option value="0">No</option><option value="1">Yes</option></select></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-modern-footer">
                </div>
        </form>
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