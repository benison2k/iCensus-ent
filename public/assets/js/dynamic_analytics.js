document.addEventListener('DOMContentLoaded', () => {
    // Load Google Charts and then initialize the dashboard
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(initializeDynamicDashboard);
});

const basePath = '/iCensus-ent/public';
let grid;

/**
 * Initializes the GridStack dashboard and loads user-defined charts.
 */
function initializeDynamicDashboard() {
    grid = GridStack.init({
        cellHeight: 80,
        margin: 20,
        float: true,
    });

    // Load saved charts from the database
    loadUserCharts();

    // Standard dashboard buttons
    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', () => {
        if(confirm('Are you sure you want to reset the layout? This will clear the current dashboard and reload with default positions.')) {
            localStorage.removeItem('chartLayout'); // Also clear saved layout positions
            localStorage.removeItem('visibleChartIds'); // Clear visibility preferences
            location.reload();
        }
    });
}

/**
 * Fetches all charts for the current user and adds only the visible ones to the grid.
 */
async function loadUserCharts() {
    try {
        const response = await fetch(`${basePath}/charts/user-charts`);
        const result = await response.json();
        
        // Get the list of chart IDs that the user wants to see
        const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || null;

        if (result.status === 'success' && result.charts) {
            
            // Filter the charts to only include ones the user has selected to be visible
            const chartsToDisplay = result.charts.filter(chart => {
                // If no preference is saved (first-time load), show all charts.
                if (visibleChartIds === null) return true;
                // Otherwise, only include charts whose ID is in the visible list.
                return visibleChartIds.includes(chart.id.toString());
            });

            // Load saved layout from localStorage
            const savedLayout = JSON.parse(localStorage.getItem('chartLayout')) || [];

            for (const chartDef of chartsToDisplay) {
                const chartId = chartDef.id.toString();

                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" data-chart-id="${chartId}">
                        <div class="chart-title">${chartDef.title}</div>
                        <div class="chart-div" id="chart-div-${chartId}">Loading...</div>
                    </div>`;
                
                // Find saved position or use default
                const layoutItem = savedLayout.find(item => item.id === chartId);
                const gridOptions = layoutItem ? { w: layoutItem.w, h: layoutItem.h, x: layoutItem.x, y: layoutItem.y, id: chartId } : { w: 4, h: 4, id: chartId };
                
                grid.addWidget(widgetHtml, gridOptions);
                
                const dataResponse = await fetch(`${basePath}/charts/data?chart_id=${chartId}`);
                const dataResult = await dataResponse.json();

                if (dataResult.status === 'success') {
                    drawChart(chartId, dataResult.type, dataResult.data);
                } else {
                     document.getElementById(`chart-div-${chartId}`).innerHTML = `<div class="chart-error">Error loading data.</div>`;
                }
            }
        }
    } catch (error) {
        console.error("Failed to load user charts:", error);
    }
}

/**
 * Draws a single chart using Google Charts.
 */
function drawChart(chartId, chartType, chartData) {
    const chartDiv = document.getElementById(`chart-div-${chartId}`);
    if (!chartDiv) return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';

    const options = {
        width: '100%', height: '100%', backgroundColor: 'transparent',
        chartArea: { 'width': '80%', 'height': '70%' },
        legend: { position: 'bottom', textStyle: { color: fontColor } },
        hAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } },
        vAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } }
    };
    
    if (chartType === 'KPI') {
        chartDiv.innerHTML = `<div class="kpi-content"><div class="kpi-value">${chartData.value || 0}</div></div>`;
        return;
    }
    
    const dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Category');
    dataTable.addColumn('number', 'Value');
    const rows = Object.entries(chartData).map(([key, value]) => [key, value]);
    dataTable.addRows(rows);

    let chart;
    switch (chartType) {
        case 'BarChart': chart = new google.visualization.BarChart(chartDiv); break;
        case 'ColumnChart': chart = new google.visualization.ColumnChart(chartDiv); break;
        case 'PieChart': default:
            chart = new google.visualization.PieChart(chartDiv);
            if (chartType === 'DonutChart') options.pieHole = 0.4;
            break;
    }
    chart.draw(dataTable, options);
}

/**
 * Saves the current GridStack layout to localStorage.
 */
function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
    }));

    localStorage.setItem('chartLayout', JSON.stringify(layout));
    alert('Layout Saved!');
}

// Make a function globally available for the chart builder to call
window.addChartToDashboard = async function(chartDef) {
    const chartId = chartDef.id.toString();
    try {
        const dataResponse = await fetch(`${basePath}/charts/data?chart_id=${chartId}`);
        const dataResult = await dataResponse.json();

        if (dataResult.status === 'success') {
            const widgetHtml = `
                <div class="grid-stack-item-content chart-container" data-chart-id="${chartId}">
                    <div class="chart-title">${dataResult.title}</div>
                    <div class="chart-div" id="chart-div-${chartId}"></div>
                </div>`;
            
            grid.addWidget(widgetHtml, { w: 4, h: 4, id: chartId });
            drawChart(chartId, dataResult.type, dataResult.data);
        } else {
            alert('Could not dynamically add chart. Please refresh the page.');
        }
    } catch (error) {
        console.error('Failed to add chart to dashboard:', error);
    }
};