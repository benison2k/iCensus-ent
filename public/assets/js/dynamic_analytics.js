// public/assets/js/dynamic_analytics.js

document.addEventListener('DOMContentLoaded', () => {
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(initializeDynamicDashboard);
});

const basePath = '/iCensus-ent/public';
let grid;

function initializeDynamicDashboard() {
    grid = GridStack.init({
        cellHeight: 80,
        margin: 20,
        float: true,
    });

    loadUserCharts();

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', () => {
        if(confirm('Are you sure you want to reset the layout? This will reload the page.')) {
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
            // Use Promise.all to load all charts concurrently for a faster initial load
            await Promise.all(result.charts.map(chartDef => addAndDrawChart(chartDef)));
        }
    } catch (error) {
        console.error("Failed to load user charts:", error);
    }
}

/**
 * Reusable Function: Adds a single chart widget to the grid and draws it.
 * @param {object} chartDef An object with {id, title, chart_type}
 */
async function addAndDrawChart(chartDef) {
    const chartId = chartDef.id;

    // Prevent adding a duplicate widget if it somehow already exists
    if (grid.engine.nodes.some(node => node.id == chartId)) {
        return;
    }

    const widgetHtml = `
        <div class="grid-stack-item-content chart-container" data-chart-id="${chartId}">
            <div class="chart-title">${chartDef.title}</div>
            <div class="chart-div" id="chart-div-${chartId}">Loading...</div>
        </div>`;

    grid.addWidget(widgetHtml, { w: 4, h: 4, id: chartId });

    try {
        const dataResponse = await fetch(`${basePath}/charts/data?chart_id=${chartId}`);
        const dataResult = await dataResponse.json();

        if (dataResult.status === 'success') {
            drawChart(chartId, dataResult.type, dataResult.data);
        } else {
            document.getElementById(`chart-div-${chartId}`).innerHTML = `<div class="chart-error">Error: ${dataResult.error}</div>`;
        }
    } catch (error) {
        document.getElementById(`chart-div-${chartId}`).innerHTML = `<div class="chart-error">Network error while fetching data.</div>`;
    }
}

// --- EXPOSE THE FUNCTION TO THE GLOBAL WINDOW OBJECT ---
// This allows chart_builder.js to call it directly after saving a new chart.
window.addChartToDashboard = addAndDrawChart;


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
            if (chartType === 'DonutChart') { options.pieHole = 0.4; }
            break;
    }
    chart.draw(dataTable, options);
}

/**
 * Saves the current GridStack layout to the database.
 */
function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({ id: d.id, x: d.x, y: d.y, w: d.w, h: d.h }));

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

