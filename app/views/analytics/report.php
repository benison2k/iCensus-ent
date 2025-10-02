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

        /* --- NEW: Cover Page Styles --- */
        .cover-page {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 100vh; /* Full viewport height */
            page-break-after: always; /* Force a page break after this element when printing */
            position: relative;
        }
        .cover-page .logo {
            max-width: 300px;
            margin-bottom: 2rem;
        }
        .cover-page h1 {
            font-family: 'Tinos', serif;
            font-size: 4em;
            margin: 0;
        }
        .cover-page h2 {
            font-family: 'Tinos', serif;
            font-size: 2em;
            margin: 0.5rem 0 2rem 0;
            font-weight: 400;
        }
        .cover-page .meta-info {
            font-size: 1.1em;
            line-height: 1.8;
        }
        .cover-page .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            z-index: -1;
            pointer-events: none;
            width: 70%;
        }
        .cover-page .watermark img {
            width: 100%;
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
        }
        
        @media screen {
            .report-content { /* Use this wrapper for screen view padding */
                padding: 1in;
            }
        }

        @media print {
            body { 
                background-color: white !important;
                margin: 0;
                padding: 0;
            }
            .no-print { display: none !important; }
            .report-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .cover-page {
                height: 100%; /* Use 100% height for print */
            }
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .charts-section { 
                display: block !important; 
            }
            .chart-container { 
                page-break-inside: avoid; 
            }
        }
    </style>
</head>
<body>

    <button class="no-print" onclick="window.print()">Print Report</button>
    
    <div class="cover-page">
        <div class="watermark">
            <img src="/iCensus-ent/public/assets/img/iCensusLogo.png" alt="iCensus Logo">
        </div>
        <img src="/iCensus-ent/public/assets/img/iCensusLogo.png" alt="iCensus Logo" class="logo">
        <h1>Official Report</h1>
        <h2>Barangay Census Data</h2>
        <div class="meta-info">
            <p><strong>Generated By:</strong> <?= htmlspecialchars($_SESSION['user']['full_name']) ?></p>
            <p><strong>Date:</strong> <?= date('F j, Y, g:i a') ?></p>
        </div>
    </div>

    <div class="report-content">
        <div class="report-container">
            <div class="report-header">
                <img src="/iCensus-ent/public/assets/img/iCensusLogoSmaller.png" alt="iCensus Logo">
                <div class="title-section">
                    <h1>Official Report</h1>
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
                            var label = key + ' (' + rawData[key] + ')'; // Label includes count now
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
    </div>
</body>
</html>