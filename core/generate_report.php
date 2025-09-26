<?php
session_start();

// --- Bouncer ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] != 'Barangay Admin') {
    http_response_code(403);
    die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>");
}

// --- Includes ---
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

$db = new Database($config);
$pdo = $db->getPdo();

// --- Get Post Data ---
$sort_by = $_POST['sort_by'] ?? 'last_name';
$sort_order = $_POST['sort_order'] ?? 'ASC';
$selected_columns = $_POST['columns'] ?? [];
$selected_charts = $_POST['charts'] ?? [];
$font_size = $_POST['font_size'] ?? '12px';
$orientation = $_POST['orientation'] ?? 'portrait';

// --- Security, Validation, and Column Mapping ---
$allowed_sort_columns = ['last_name', 'first_name', 'date_added', 'dob'];
if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'last_name';
}
$sort_order = ($sort_order === 'DESC') ? 'DESC' : 'ASC';

$all_columns = [
    'full_name' => ['label' => 'Full Name', 'sql' => "CONCAT(first_name, ' ', last_name)"],
    'address' => ['label' => 'Full Address', 'sql' => "CONCAT(house_no, ' ', street, ', Purok ', purok)"],
    'dob' => ['label' => 'Date of Birth', 'sql' => 'dob'],
    'age' => ['label' => 'Age', 'sql' => 'TIMESTAMPDIFF(YEAR, dob, CURDATE())'],
    'gender' => ['label' => 'Gender', 'sql' => 'gender'],
    'civil_status' => ['label' => 'Civil Status', 'sql' => 'civil_status'],
    'contact_number' => ['label' => 'Contact Number', 'sql' => 'contact_number'],
    'email' => ['label' => 'Email', 'sql' => 'email'],
    'blood_type' => ['label' => 'Blood Type', 'sql' => 'blood_type'],
    'nationality' => ['label' => 'Nationality', 'sql' => 'nationality'],
    'status' => ['label' => 'Resident Status', 'sql' => 'status'],
    'date_added' => ['label' => 'Date Added', 'sql' => 'date_added']
];

$columns_to_select = [];
$report_headers = [];
if (!empty($selected_columns)) {
    foreach ($selected_columns as $col) {
        if (array_key_exists($col, $all_columns)) {
            $columns_to_select[$col] = $all_columns[$col]['sql'] . " AS " . $col;
            $report_headers[$col] = $all_columns[$col]['label'];
        }
    }
} else {
    $columns_to_select = [];
}

// --- Build Query ---
if (!empty($columns_to_select)) {
    $sql = "SELECT " . implode(", ", $columns_to_select) . " FROM residents ORDER BY $sort_by $sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $results = [];
}

// --- Fetch Chart Data ---
$chart_data = [];
if (!empty($selected_charts)) {
    $stmt_all = $pdo->query("SELECT dob, gender, civil_status, blood_type, nationality, purok FROM residents");
    $all_residents = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

    foreach ($selected_charts as $metric) {
        $data = [];
        switch ($metric) {
            case 'gender':
                foreach ($all_residents as $r) $data[$r['gender'] ?: 'Unknown'] = ($data[$r['gender'] ?: 'Unknown'] ?? 0) + 1;
                break;
            case 'age':
                $ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
                 foreach ($all_residents as $r) {
                    $age = $r['dob'] ? (new DateTime($r['dob']))->diff(new DateTime('today'))->y : null;
                    if ($age === null) continue;
                    if ($age <= 17) $ageGroups['0-17']++;
                    elseif ($age <= 35) $ageGroups['18-35']++;
                    elseif ($age <= 59) $ageGroups['36-59']++;
                    else $ageGroups['60+']++;
                }
                $data = $ageGroups;
                break;
            case 'civil_status':
                 foreach ($all_residents as $r) $data[trim($r['civil_status']) ?: 'Unknown'] = ($data[trim($r['civil_status']) ?: 'Unknown'] ?? 0) + 1;
                break;
            case 'blood_type':
                 foreach ($all_residents as $r) $data[trim($r['blood_type']) ?: 'Unknown'] = ($data[trim($r['blood_type']) ?: 'Unknown'] ?? 0) + 1;
                break;
            case 'nationality':
                 foreach ($all_residents as $r) $data[trim($r['nationality']) ?: 'Unknown'] = ($data[trim($r['nationality']) ?: 'Unknown'] ?? 0) + 1;
                break;
            case 'purok':
                foreach ($all_residents as $r) $data[trim($r['purok']) ?: 'Unknown'] = ($data[trim($r['purok']) ?: 'Unknown'] ?? 0) + 1;
                break;
        }
        $chart_data[$metric] = $data;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Resident Report</title>
    <?php if (!empty($selected_charts)): ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?php endif; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Tinos:wght@400;700&display=swap');
        
        body { 
            font-family: 'Roboto', sans-serif; 
            font-size: <?= htmlspecialchars($font_size) ?>;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .report-container {
            max-width: 8.5in;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-header img {
            max-width: 150px;
        }
        .report-header .title-section {
            text-align: right;
        }
        .report-header h1 {
            font-family: 'Tinos', serif;
            font-size: 2.5em;
            margin: 0;
            color: #000;
        }
        .report-header p {
            margin: 5px 0 0;
            font-size: 0.9em;
            color: #555;
        }
        .content-section { page-break-inside: avoid; }
        .section-title {
            font-family: 'Tinos', serif;
            font-size: 1.8em;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            font-size: 0.9em;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .no-print { 
            position: fixed; 
            top: 20px; 
            right: 20px;
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 100;
        }
        .charts-section { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
        .chart-container { width: 100%; max-width: 600px; height: 400px; margin-bottom: 20px; }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            z-index: 1;
            pointer-events: none;
        }
        .report-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 0.8em;
            color: #777;
        }

        @page {
            size: A4 <?= htmlspecialchars($orientation) ?>;
            margin: 1in;

            @top-center {
                content: "iCensus Official Report";
                font-family: 'Tinos', serif;
                font-size: 1.2em;
                color: #555;
            }

            @bottom-right {
                content: "Page " counter(page);
                font-family: 'Roboto', sans-serif;
                font-size: 0.9em;
                color: #555;
            }
        }
        
        @media screen {
            .report-container {
                padding: 1in;
            }
        }

        @media print {
            body { background-color: white; }
            .no-print { display: none; }
            .report-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            body { -webkit-print-color-adjust: exact; }
            .charts-section { display: block; }
            .chart-container { page-break-inside: avoid; }
            .watermark {
                position: fixed !important;
                z-index: -1;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="../assets/img/iCensusLogo.png" alt="iCensus Logo" width="600">
    </div>
    <button class="no-print" onclick="window.print()">Print Report</button>
    
    <div class="report-container">
        <div class="report-header">
            <img src="../assets/img/iCensusLogo.png" alt="iCensus Logo">
            <div class="title-section">
                <h1>Official Report</h1>
                <p>Generated By: <strong><?= htmlspecialchars($_SESSION['user']['full_name']) ?></strong></p>
                <p>Date: <?= date('F j, Y, g:i a') ?></p>
            </div>
        </div>

        <?php if (!empty($results)): ?>
        <div class="content-section">
            <h2 class="section-title">Resident Data</h2>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($report_headers as $header): ?>
                            <th><?= htmlspecialchars($header) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach (array_keys($report_headers) as $col_key): ?>
                                <td><?= htmlspecialchars($row[$col_key] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($selected_charts)): ?>
        <div class="content-section">
            <h2 class="section-title">Visual Analytics</h2>
            <div class="charts-section">
                <?php foreach ($selected_charts as $chart_id): ?>
                    <div id="<?= htmlspecialchars($chart_id) ?>_chart_div" class="chart-container"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <script type="text/javascript">
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawCharts);

            function drawCharts() {
                <?php
                foreach ($selected_charts as $metric):
                    if (isset($chart_data[$metric])):
                        $title = ucwords(str_replace("_", " ", $metric)) . " Distribution";
                        $data_json = json_encode($chart_data[$metric]);

                        $chart_type = 'PieChart'; // Default
                        if (in_array($metric, ['age'])) {
                            $chart_type = 'ColumnChart';
                        } elseif (in_array($metric, ['purok', 'nationality'])) {
                             $chart_type = 'BarChart';
                        } elseif (in_array($metric, ['civil_status'])) {
                            $chart_type = 'DonutChart';
                        }
                ?>
                (function() {
                    var data_<?= $metric ?> = new google.visualization.DataTable();
                    data_<?= $metric ?>.addColumn('string', 'Category');
                    data_<?= $metric ?>.addColumn('number', 'Count');
                    
                    var rawData = <?= $data_json ?>;
                    var chartRows = [];
                    for (var key in rawData) {
                        var label = key + ' = ' + rawData[key];
                        chartRows.push([label, rawData[key]]);
                    }
                    data_<?= $metric ?>.addRows(chartRows);

                    var options_<?= $metric ?> = {
                      title: '<?= $title ?>',
                      fontName: 'Roboto',
                      titleTextStyle: { fontSize: 16, bold: false },
                      legend: { position: 'right', alignment: 'center', textStyle: { fontSize: 12 } },
                      pieSliceText: 'percentage',
                      pieSliceTextStyle: { color: 'black', fontSize: 14 },
                      chartArea: {width: '60%', height: '80%'}
                    };

                    var chart;
                    <?php if ($chart_type === 'PieChart'): ?>
                        chart = new google.visualization.PieChart(document.getElementById('<?= $metric ?>_chart_div'));
                    <?php elseif ($chart_type === 'DonutChart'): ?>
                        options_<?= $metric ?>.pieHole = 0.4;
                        chart = new google.visualization.PieChart(document.getElementById('<?= $metric ?>_chart_div'));
                    <?php elseif ($chart_type === 'ColumnChart'): ?>
                        options_<?= $metric ?>.legend.position = 'none';
                        chart = new google.visualization.ColumnChart(document.getElementById('<?= $metric ?>_chart_div'));
                     <?php elseif ($chart_type === 'BarChart'): ?>
                        options_<?= $metric ?>.legend.position = 'none';
                        chart = new google.visualization.BarChart(document.getElementById('<?= $metric ?>_chart_div'));
                    <?php endif; ?>
                    
                    chart.draw(data_<?= $metric ?>, options_<?= $metric ?>);
                })();
                <?php endif; endforeach; ?>
            }
        </script>
        <?php endif; ?>
        
        <div class="report-footer">
            <p>&copy; <?= date("Y") ?> iCensus System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>