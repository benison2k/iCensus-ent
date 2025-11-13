<?php
// /app/views/components/manage_charts_modal.php
?>

<div id="manageChartsModal" class="manage-charts-modal">
    <div class="manage-charts-content">
        <span class="close-btn" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top:0; margin-bottom: 25px;">Manage Dashboard Charts</h2>

        <p>Select charts to display, or use the actions to edit or delete them.</p>
        
        <ul id="chartSelectionList" class="chart-list">
            </ul>

        <div style="text-align: right;">
            <button type="button" id="saveChartSelectionBtn" style="padding: 12px 20px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Update Dashboard</button>
        </div>
    </div>
</div>