<div id="chartModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close material-icons">&times;</span>
        <h3>Customize Chart</h3>
        <form id="chartForm">
            <label for="chart-data-source">Data Source</label>
            <select id="chart-data-source" name="data-source" required>
                <option value="">Select a Data Source</option>
                <option value="gender">Gender Distribution</option>
                <option value="age_group">Age Group Distribution</option>
                <option value="status">Resident Status</option>
            </select>

            <label for="chart-type">Chart Type</label>
            <select id="chart-type" name="chart-type" required>
                <option value="bar">Bar Chart</option>
                <option value="pie">Pie Chart</option>
                <option value="doughnut">Doughnut Chart</option>
            </select>

            <label for="chart-size">Chart Size (Grid Columns)</label>
            <select id="chart-size" name="chart-size" required>
                <option value="1">Small (1 Column)</option>
                <option value="2">Medium (2 Columns)</option>
                <option value="3">Large (3 Columns)</option>
            </select>

            <button type="submit" id="create-chart-btn">Create Chart</button>
        </form>
    </div>
</div>