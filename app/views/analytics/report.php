<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Resident Report</title>
    <?php if (!empty($charts)): ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?php endif; ?>
    <style>
        body { font-family: sans-serif; }
        .report-container { max-width: 1000px; margin: auto; padding: 2rem; background: #fff; }
        .report-header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .charts-section { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-top: 2rem; }
        .chart-container { width: 100%; max-width: 500px; height: 350px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="position:fixed; top:10px; right:10px; padding:10px;">Print</button>
    <div class="report-container">
        <div class="report-header">
            <h1>iCensus Official Report</h1>
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        </div>
        
        <?php if (!empty($results)): ?>
            <section>
                <h2>Resident Data</h2>
                <table>
                    <thead><tr><?php foreach ($headers as $header): ?><th><?= htmlspecialchars($header) ?></th><?php endforeach; ?></tr></thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr><?php foreach ($row as $cell): ?><td><?= htmlspecialchars($cell) ?></td><?php endforeach; ?></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
        
        <?php if (!empty($charts)): ?>
            <section class="charts-section">
                <?php foreach ($charts as $metric => $chartData): ?>
                    <div id="<?= $metric ?>_chart_div" class="chart-container"></div>
                <?php endforeach; ?>
            </section>
            <script>
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawCharts);
                function drawCharts() {
                    <?php foreach ($charts as $metric => $chartData): ?>
                        (function() {
                            const data = google.visualization.arrayToDataTable([
                                ['Category', 'Count'],
                                <?php foreach ($chartData as $label => $value): ?>
                                    ['<?= addslashes($label) ?>', <?= $value ?>],
                                <?php endforeach; ?>
                            ]);
                            const options = { title: '<?= ucfirst(str_replace("_", " ", $metric)) ?> Distribution' };
                            const chart = new google.visualization.PieChart(document.getElementById('<?= $metric ?>_chart_div'));
                            chart.draw(data, options);
                        })();
                    <?php endforeach; ?>
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>