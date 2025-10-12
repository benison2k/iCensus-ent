// public/assets/js/dynamic_analytics.js

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
            // A more robust reset would clear the layout from the database.
            // For now, reloading is a simple way to reset the view.
            location.reload();
        }
    });
}

/**
 * Fetches all charts for the current user and adds them to the grid.
 */
async function loadUserCharts() {
    try {
        const response = await fetch(`${basePath}/charts/user-charts`);
        const result = await response.json();

        if (result.status === 'success' && result.charts) {
            // First, add all chart widgets to the page with a "Loading..." message.
            result.charts.forEach(chartDef => {
                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" data-chart-id="${chartDef.id}">
                        <div class="chart-title">${chartDef.title}</div>
                        <div class="chart-div" id="chart-div-${chartDef.id}">Loading...</div>
                    </div>`;
                grid.addWidget(widgetHtml, { w: 4, h: 4, id: chartDef.id });
            });

            // Then, fetch the data for all charts concurrently.
            const dataPromises = result.charts.map(chartDef =>
                fetch(`${basePath}/charts/data?chart_id=${chartDef.id}`)
                    .then(res => res.json())
                    .then(dataResult => {
                        if (dataResult.status === 'success') {
                            drawChart(chartDef.id, dataResult.type, dataResult.data);
                        } else {
                            document.getElementById(`chart-div-${chartDef.id}`).innerHTML = `<div class="chart-error">Error loading data.</div>`;
                        }
                    })
            );
            
            // Wait for all data fetches to complete.
            await Promise.all(dataPromises);
        }
    } catch (error) {
        console.error("Failed to load user charts:", error);
        // You could display a global error message on the dashboard here.
    }
}


/**
 * Draws a single chart using Google Charts.
 * @param {number} chartId The ID of the chart.
 * @param {string} chartType The type of chart (e.g., 'PieChart', 'BarChart').
 * @param {object} chartData The data for the chart.
 */
function drawChart(chartId, chartType, chartData) {
    const chartDiv = document.getElementById(`chart-div-${chartId}`);
    if (!chartDiv) return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';

    const options = {
        width: '100%',
        height: '100%',
        backgroundColor: 'transparent',
        chartArea: { 'width': '80%', 'height': '70%' },
        legend: { position: 'bottom', textStyle: { color: fontColor } },
        hAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } },
        vAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } }
    };
    
    // Handle KPI display separately
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
        case 'BarChart':
            chart = new google.visualization.BarChart(chartDiv);
            break;
        case 'ColumnChart':
            chart = new google.visualization.ColumnChart(chartDiv);
            break;
        case 'PieChart':
        default:
            chart = new google.visualization.PieChart(chartDiv);
            if (chartType === 'DonutChart') {
                options.pieHole = 0.4;
            }
            break;
    }

    chart.draw(dataTable, options);
}

/**
 * Saves the current GridStack layout to the database.
 */
function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
    }));

    fetch(`${basePath}/analytics/layout/save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(layout)
    })
    .then(res => res.json()).then(result => {
        if (result.status === 'success') {
            alert('Layout Saved!');
        } else {
            alert('Error saving layout.');
        }
    });
}
