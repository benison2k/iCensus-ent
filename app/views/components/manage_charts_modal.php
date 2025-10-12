<?php
// /app/views/components/manage_charts_modal.php
?>

<style>
    .manage-charts-modal { display: none; position: fixed; z-index: 2001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
    .manage-charts-content { background-color: #fefefe; margin: 10% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; }
    .chart-list { list-style: none; padding: 0; margin: 0 0 20px 0; max-height: 40vh; overflow-y: auto; }
    .chart-list-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
    .chart-list-item label { font-size: 1rem; margin-left: 10px; flex-grow: 1; }
    .chart-list-item input[type="checkbox"] { width: 20px; height: 20px; }
</style>

<div id="manageChartsModal" class="manage-charts-modal">
    <div class="manage-charts-content">
        <span class="close-btn" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top:0; margin-bottom: 25px;">Manage Dashboard Charts</h2>

        <p>Select the charts you want to display on your dashboard.</p>
        
        <ul id="chartSelectionList" class="chart-list">
            <!-- Chart checkboxes will be dynamically inserted here by JavaScript -->
        </ul>

        <div style="text-align: right;">
            <button type="button" id="saveChartSelectionBtn" style="padding: 12px 20px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Update Dashboard</button>
        </div>
    </div>
</div>
