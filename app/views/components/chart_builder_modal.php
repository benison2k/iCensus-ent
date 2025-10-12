<?php
// /app/views/components/chart_builder_modal.php
?>

<style>
    /* Basic styling for the chart builder modal */
    .chart-builder-modal {
        display: none; /* Hidden by default */
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.6);
        padding-top: 50px;
    }

    .chart-builder-content {
        background-color: #fefefe;
        margin: auto;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .chart-builder-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-builder-group {
        display: flex;
        flex-direction: column;
    }

    .chart-builder-group label {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #333;
    }

    .chart-builder-group input,
    .chart-builder-group select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .btn-remove-filter {
        cursor: pointer;
        color: #d9534f;
        background: transparent;
        border: none;
        font-size: 1.5rem;
    }

</style>

<div id="chartBuilderModal" class="chart-builder-modal">
    <div class="chart-builder-content">
        <span class="close-btn" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top:0; margin-bottom: 25px;">Create Custom Chart</h2>

        <form id="chartBuilderForm">
            <h4>Step 1: What to show?</h4>
            <div class="chart-builder-grid">
                <div class="chart-builder-group">
                    <label for="chartTitle">Chart Title</label>
                    <input type="text" id="chartTitle" name="title" placeholder="e.g., Seniors by Purok" required>
                </div>
                <div class="chart-builder-group">
                    <label for="chartType">Chart Type</label>
                    <select id="chartType" name="chart_type">
                        <option value="PieChart">Pie Chart</option>
                        <option value="BarChart">Bar Chart</option>
                        <option value="ColumnChart">Column Chart</option>
                        <option value="KPI">KPI (Single Number)</option>
                    </select>
                </div>
            </div>

            <h4>Step 2: How to measure it?</h4>
            <div class="chart-builder-grid">
                <div class="chart-builder-group">
                    <label for="aggregateFunction">Measure</label>
                    <select id="aggregateFunction" name="aggregate_function">
                        <option value="COUNT">Count of Residents</option>
                        <option value="AVG">Average Age</option>
                    </select>
                </div>
                <div class="chart-builder-group">
                    <label for="groupByColumn">Group By</label>
                    <select id="groupByColumn" name="group_by_column">
                        <option value="">None (for KPIs)</option>
                        <option value="gender">Gender</option>
                        <option value="purok">Purok</option>
                        <option value="civil_status">Civil Status</option>
                        <option value="educational_attainment">Educational Attainment</option>
                        <option value="is_pwd">Is PWD?</option>
                        <option value="is_4ps_member">Is 4Ps Member?</option>
                        <option value="dob">Age Brackets</option>
                    </select>
                </div>
            </div>

            <h4>Step 3: (Optional) Filter the data</h4>
            <div id="filterContainer">
                </div>
            <button type="button" id="addFilterBtn" style="padding: 8px 12px; margin-top: 10px;">+ Add Filter</button>

            <hr style="margin: 25px 0;">

            <div style="text-align: right;">
                <button type="submit" id="saveChartBtn" style="padding: 12px 20px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Save Chart</button>
            </div>
        </form>
    </div>
</div>