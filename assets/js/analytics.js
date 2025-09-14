google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid; // Make grid globally accessible
let chartsToDraw = {}; // A registry for our charts

// A simple debounce function to prevent rapid-fire resizing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function initializeDashboard() {
    grid = GridStack.init({
        cellHeight: 150,
        margin: 10,
        float: true
    });

    loadLayout();

    // When a widget is added, draw its chart
    grid.on('added', function(event, items) {
        items.forEach(item => {
            const chartId = item.id;
            if (chartsToDraw[chartId]) {
                chartsToDraw[chartId](); // Call the drawing function
                delete chartsToDraw[chartId]; // Clean up
            }
        });
    });
    
    // When a widget stops resizing, redraw its chart
    const redrawChart = (el) => {
        const id = el.gridstackNode.id;
        const chartDiv = document.getElementById(`${id}_chart_div`);
        if (chartDiv && chartDiv.chartInstance) {
            chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
        }
    };

    grid.on('resizestop', (event, el) => redrawChart(el));

    // Also redraw all charts if the browser window is resized
    window.addEventListener('resize', debounce(() => {
        grid.engine.nodes.forEach(node => redrawChart(node.el));
    }, 250));

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
}

function loadLayout() {
    fetch('../core/get_layout.php')
        .then(response => response.json())
        .then(layoutData => {
            grid.removeAll();
            // Prepare drawing functions before adding widgets
            layoutData.forEach(node => {
                chartsToDraw[node.id] = () => drawChart(node.id);
                const widgetHtml = `
                    <div>
                        <div class="grid-stack-item-content">
                            <div class="chart-title">${getChartTitle(node.id)}</div>
                            <div class="chart-div" id="${node.id}_chart_div">Loading...</div>
                        </div>
                    </div>`;
                grid.addWidget(widgetHtml, node);
            });
        });
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h, 
        keepAspectRatio: d.keepAspectRatio 
    }));
    
    fetch('../core/save_layout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(layout)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Layout saved successfully!');
        } else {
            alert('Error saving layout: ' + result.message);
        }
    });
}

function getChartTitle(metric) {
    const titles = {
        gender: 'Gender Distribution',
        age: 'Age Distribution',
        status: 'Resident Status',
        purok: 'Population by Purok',
        barangay: 'Population by Barangay',
        civil_status: 'Civil Status',
        blood_type: 'Blood Type',
        residency_status: 'Residency Status'
    };
    return titles[metric] || 'Unknown Chart';
}

function drawChart(metric) {
    fetch(`../core/analytics_data.php?metric=${metric}`)
        .then(response => response.json())
        .then(apiData => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (!chartDiv || !apiData) return;

            const dataArray = [[getChartTitle(metric), 'Count']];
            for (const key in apiData) {
                if (key) dataArray.push([key, apiData[key]]);
            }
            const data = google.visualization.arrayToDataTable(dataArray);
            
            let options = {
                title: '',
                legend: 'bottom',
                width: '100%',
                height: '100%',
                backgroundColor: 'transparent',
                chartArea: {'width': '90%', 'height': '75%'}
            };

            let chartType = 'PieChart';

            if (['age', 'purok', 'barangay', 'residency_status'].includes(metric)) {
                chartType = 'ColumnChart';
                options.legend = { position: 'none' };
            } else if (['gender', 'civil_status'].includes(metric)) {
                options.pieHole = 0.4;
            }
            
            chartDiv.chartData = data;
            chartDiv.chartOptions = options;
            
            const chart = new google.visualization[chartType](chartDiv);
            chartDiv.chartInstance = chart;

            chart.draw(data, options);
        });
}