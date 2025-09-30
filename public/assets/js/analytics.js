document.addEventListener('DOMContentLoaded', () => {
    const reportModal = document.getElementById('report-modal');
    const generateReportBtn = document.getElementById('generate-report-btn');
    
    if (reportModal && generateReportBtn) {
        const closeBtn = reportModal.querySelector('.close-btn');
        generateReportBtn.addEventListener('click', () => {
            reportModal.style.display = 'block';
        });
        if (closeBtn) closeBtn.addEventListener('click', () => reportModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target == reportModal) reportModal.style.display = 'none';
        });
    }

    const detailModal = document.getElementById('chart-detail-modal');
    if (detailModal) {
        const closeBtn = detailModal.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => detailModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target == detailModal) detailModal.style.display = 'none';
        });
    }
});

google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid;
let chartsToDraw = {};
const basePath = '/iCensus-ent/public';

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function initializeDashboard() {
    grid = GridStack.init({
        cellHeight: 80, margin: 20, float: true,
        resizable: { handles: 'n, e, s, w, ne, nw, se, sw' }
    });
    loadLayout();
    grid.on('added', (event, items) => {
        items.forEach(item => {
            if (chartsToDraw[item.id]) {
                chartsToDraw[item.id]();
                delete chartsToDraw[item.id];
            }
        });
    });
    const redrawChart = (el) => {
        const id = el.gridstackNode.id;
        const chartDiv = document.getElementById(`${id}_chart_div`);
        if (chartDiv && chartDiv.chartInstance && chartDiv.chartData && chartDiv.chartOptions) {
             if (chartDiv.chartType === 'PopulationPyramid' || chartDiv.chartType === 'GroupedBar') {
                chartDiv.chartInstance.draw(chartDiv.chartData, google.charts.Bar.convertOptions(chartDiv.chartOptions));
            } else {
                chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
            }
        }
    };
    grid.on('resizestop', (event, el) => redrawChart(el));
    window.addEventListener('resize', debounce(() => {
        if(grid && grid.engine && grid.engine.nodes) {
            grid.engine.nodes.forEach(node => redrawChart(node.el));
        }
    }, 250));
    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
}

function loadLayout() {
    fetch(`${basePath}/analytics/layout`)
        .then(response => response.json())
        .then(layoutData => {
            if (!layoutData || layoutData.length === 0) return;
            grid.removeAll();
            layoutData.forEach(node => {
                chartsToDraw[node.id] = () => drawChart(node.id);
                const isKpi = getChartType(node.id) === 'KPI';
                const contentHtml = isKpi ? `<div class="kpi-content" id="${node.id}_chart_div"></div>` : `<div class="chart-div" id="${node.id}_chart_div"></div>`;
                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" data-metric="${node.id}">
                        <div class="chart-title"><span class="material-icons chart-icon">${getChartIcon(node.id)}</span>${getChartTitle(node.id)}</div>
                        ${contentHtml}
                    </div>`;
                grid.addWidget(widgetHtml, node);
            });
        });
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({ id: d.id, x: d.x, y: d.y, w: d.w, h: d.h }));
    fetch(`${basePath}/analytics/layout/save`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(layout)
    })
    .then(res => res.json()).then(result => alert(result.status === 'success' ? 'Layout saved!' : 'Error.'));
}

function resetLayout() {
    if (confirm('Are you sure you want to reset your layout?')) {
        fetch(`${basePath}/analytics/layout/reset`, { method: 'POST' })
            .then(res => res.json()).then(result => { if (result.status === 'success') loadLayout(); });
    }
}

function getChartTitle(metric) {
    const t = {
        gender: 'Gender Distribution', age: 'Age Groups', purok: 'Population by Purok',
        generation_breakdown: 'Generation Breakdown', dependency_ratio: 'Dependency Ratio', sex_ratio: 'Sex Ratio',
        population_pyramid: 'Population Pyramid', average_age_of_residents: 'Average Resident Age',
        average_household_size: 'Average Household Size', civil_status: 'Civil Status', detailed_age_brackets: 'Detailed Age Brackets (10-year)',
        household_size_distribution: 'Household Size Distribution', heads_of_household_by_gender: 'Heads of Household by Gender',
        relationship: 'Relationship to Head', voter_population_by_purok: 'Voter Population by Purok',
        senior_citizens_by_purok: 'Senior Citizens by Purok', school_age_population_by_purok: 'School-Age Population by Purok',
        residents_per_street: 'Top 10 Streets by Population', nationality: 'Nationality', blood_type: 'Blood Type Distribution',
        profile_completeness: 'Profile Completeness (%)', emergency_contact_coverage: 'Emergency Contact Coverage',
        resident_status_overview: 'Resident Status Overview', civil_status_distribution_by_gender: 'Civil Status by Gender'
    };
    return t[metric] || 'Chart';
}

function getChartIcon(metric) {
    const i = {
        gender: 'wc', age: 'cake', purok: 'location_on', generation_breakdown: 'groups',
        dependency_ratio: 'reduce_capacity', sex_ratio: 'transgender', population_pyramid: 'stacked_bar_chart',
        average_age_of_residents: 'escalator_warning', average_household_size: 'roofing', civil_status: 'favorite'
    };
    return i[metric] || 'pie_chart';
}

function getChartType(metric) {
    const t = {
        average_age_of_residents: 'KPI', average_household_size: 'KPI', dependency_ratio: 'KPI',
        population_pyramid: 'PopulationPyramid', civil_status_distribution_by_gender: 'GroupedBar',
        age: 'ColumnChart', detailed_age_brackets: 'ColumnChart', purok: 'BarChart'
    };
    return t[metric] || 'PieChart';
}

function drawChart(metric) {
    fetch(`${basePath}/analytics/data?metric=${metric}`)
        .then(response => response.json())
        .then(apiData => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (!chartDiv || apiData.error) {
                if (chartDiv) chartDiv.innerHTML = `<div class="chart-error">Error: ${apiData.error || 'No data'}</div>`;
                return;
            }
            const chartType = getChartType(metric);
            chartDiv.chartType = chartType;
            if (chartType === 'KPI') {
                chartDiv.innerHTML = `<div class="kpi-value">${apiData.value}</div><div class="kpi-label">${apiData.label || ''}</div>`;
                return;
            }
            let data, options = { title: '', legend: { position: 'bottom' }, width: '100%', height: '100%', backgroundColor: 'transparent', chartArea: {'width': '85%', 'height': '70%'} };
            
            if(chartType === 'PopulationPyramid'){
                let maxVal = 0;
                const pyramidData = [['Age', 'Male', { role: 'style' }, 'Female', { role: 'style' }]];
                for (const age in apiData) {
                    const maleVal = Math.abs(apiData[age]['Male'] || 0);
                    const femaleVal = Math.abs(apiData[age]['Female'] || 0);
                    maxVal = Math.max(maxVal, maleVal, femaleVal);
                    pyramidData.push([age, -maleVal, 'color: #3366cc', femaleVal, 'color: #dc3912']);
                }
                data = google.visualization.arrayToDataTable(pyramidData);
                options.isStacked = true;
                const tickMax = Math.ceil(maxVal / 5) * 5;
                options.hAxis = { ticks: Array.from({length: (tickMax/5)*2+1}, (_, i) => (i - tickMax/5)*5).map(v => ({v:v, f:String(Math.abs(v))})) };
            } else {
                const dataArray = [[getChartTitle(metric), 'Count']];
                for (const key in apiData) { dataArray.push([key, apiData[key]]); }
                data = google.visualization.arrayToDataTable(dataArray);
            }
            
            if (metric === 'gender' || metric === 'civil_status' || metric === 'sex_ratio') options.pieHole = 0.4;
            chartDiv.chartData = data; chartDiv.chartOptions = options;
            
            let chart;
            if (chartType === 'PopulationPyramid') chart = new google.charts.Bar(chartDiv);
            else if (chartType === 'ColumnChart') chart = new google.visualization.ColumnChart(chartDiv);
            else if (chartType === 'BarChart') chart = new google.visualization.BarChart(chartDiv);
            else chart = new google.visualization.PieChart(chartDiv);
            
            chartDiv.chartInstance = chart;
            if(chartType === 'PopulationPyramid') chart.draw(data, google.charts.Bar.convertOptions(options));
            else chart.draw(data, options);
        })
        .catch(error => {
            console.error('Error fetching/drawing chart:', metric, error);
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (chartDiv) chartDiv.innerHTML = `<div class="chart-error">Could not load.</div>`;
        });
}
