<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/analytics.css">
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/report-modal.css">
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
                <button id="reset-layout-btn"><span class="material-icons">refresh</span> Reset Layout</button>
                <button id="save-layout-btn"><span class="material-icons">save</span> Save Layout</button>
            </div>
        </div>
        <hr class="separator-line">
        <div class="grid-stack"></div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/js/analytics.js"></script>
</body>
</html>