google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid; // Make grid globally accessible

function initializeDashboard() {
    grid = GridStack.init({
        cellHeight: 150,
        margin: 10,
        float: true
    });

    loadLayout();

    grid.on('resizestop', function(event, el) {
        const id = el.gridstackNode.id;
        const chartDiv = document.getElementById(`${id}_chart_div`);
        if (chartDiv && chartDiv.chart) {
            setTimeout(() => {
                chartDiv.chart.draw(chartDiv.data, chartDiv.options);
            }, 100);
        }
    });

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
}

function loadLayout() {
    fetch('../core/get_layout.php')
        .then(response => response.json())
        .then(layoutData => {
            grid.removeAll();
            layoutData.forEach(node => {
                const widgetHtml = `
                    <div>
                        <div class="grid-stack-item-content">
                            <div class="chart-title">${getChartTitle(node.id)}</div>
                            <div class="chart-div" id="${node.id}_chart_div">Loading...</div>
                        </div>
                    </div>`;
                grid.addWidget(widgetHtml, node);
                drawChart(node.id);
            });
        });
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    // **MODIFIED PART**: Ensure 'keepAspectRatio' is included when saving
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
                if (key) { 
                    dataArray.push([key, apiData[key]]);
                }
            }
            const data = google.visualization.arrayToDataTable(dataArray);
            chartDiv.data = data;

            let options = { title: '', legend: 'bottom' };
            let chartType = 'PieChart';

            if (['age', 'purok', 'barangay', 'residency_status'].includes(metric)) {
                chartType = 'ColumnChart';
                options.legend = { position: 'none' };
            } else if (['gender', 'civil_status'].includes(metric)) {
                options.pieHole = 0.4;
            }
            
            chartDiv.options = options;
            
            const chart = new google.visualization[chartType](chartDiv);
            chartDiv.chart = chart;

            setTimeout(() => {
                chart.draw(data, options);
            }, 100);
        });
}