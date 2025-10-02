document.addEventListener('DOMContentLoaded', () => {
    // --- MODAL SETUP ---
    const reportModal = document.getElementById('report-modal');
    const generateReportBtn = document.getElementById('generate-report-btn');
    const detailModal = document.getElementById('chart-detail-modal');

    // Report Modal Listeners
    if (reportModal && generateReportBtn) {
        const closeBtn = reportModal.querySelector('.close-btn');
        generateReportBtn.addEventListener('click', () => reportModal.style.display = 'block');
        if (closeBtn) closeBtn.addEventListener('click', () => reportModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target === reportModal) reportModal.style.display = 'none';
        });
    }

    // Chart Detail Modal Listeners
    if (detailModal) {
        const closeBtn = detailModal.querySelector('.close-btn');
        if (closeBtn) closeBtn.addEventListener('click', () => detailModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target === detailModal) detailModal.style.display = 'none';
        });
    }
});

// --- GOOGLE CHARTS & GRIDSTACK SETUP ---
google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid;
const chartsToDraw = {};
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
            const metric = item.id;
            if (chartsToDraw[metric]) {
                chartsToDraw[metric]();
                delete chartsToDraw[metric];
            }
            if (getChartType(metric) !== 'KPI' && item.el) {
                 item.el.addEventListener('click', () => showDetailModal(metric));
            }
        });
    });

    const redrawChartOnResize = (el) => {
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

    grid.on('resizestop', (event, el) => redrawChartOnResize(el));
    window.addEventListener('resize', debounce(() => {
        if (grid && grid.engine && grid.engine.nodes) {
            grid.engine.nodes.forEach(node => redrawChartOnResize(node.el));
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
    .then(res => res.json()).then(result => alert(result.status === 'success' ? 'Layout saved!' : 'Error saving layout.'));
}

function resetLayout() {
    if (confirm('Are you sure you want to reset your layout to the default?')) {
        fetch(`${basePath}/analytics/layout/reset`, { method: 'POST' })
            .then(res => res.json()).then(result => { if (result.status === 'success') loadLayout(); });
    }
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
            let data, options = {
                title: '', legend: { position: 'bottom' },
                width: '100%', height: '100%',
                backgroundColor: 'transparent',
                chartArea: {'width': '85%', 'height': '70%'}
            };

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
            chartDiv.chartData = data;
            chartDiv.chartOptions = options;

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

// *** THIS IS THE CORRECTED FUNCTION ***
function showDetailModal(metric) {
    const originalChartDiv = document.getElementById(`${metric}_chart_div`);
    if (!originalChartDiv || !originalChartDiv.chartData) {
        console.error("Source chart data not found for metric:", metric);
        return;
    }

    const modal = document.getElementById('chart-detail-modal');
    const chartContentDiv = document.getElementById('chart-detail-content');
    const titleEl = document.getElementById('chart-detail-title');
    const explanationEl = document.getElementById('chart-detail-explanation');

    chartContentDiv.innerHTML = '';
    titleEl.textContent = getChartTitle(metric);
    explanationEl.textContent = getChartExplanation(metric);

    const modalOptions = JSON.parse(JSON.stringify(originalChartDiv.chartOptions));
    modalOptions.height = '100%';
    modalOptions.width = '100%';
    modalOptions.chartArea = {'width': '80%', 'height': '80%'};
    modalOptions.legend.position = 'right';

    const chartType = originalChartDiv.chartType;
    let chart;
    if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') chart = new google.charts.Bar(chartContentDiv);
    else if (chartType === 'ColumnChart') chart = new google.visualization.ColumnChart(chartContentDiv);
    else if (chartType === 'BarChart') chart = new google.visualization.BarChart(chartContentDiv);
    else chart = new google.visualization.PieChart(chartContentDiv);

    // FIX: Show the modal using 'flex' to trigger the centering styles
    modal.style.display = 'flex';

    // FIX: Delay the chart drawing to allow the modal to render
    setTimeout(() => {
        if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') {
            chart.draw(originalChartDiv.chartData, google.charts.Bar.convertOptions(modalOptions));
        } else {
            chart.draw(originalChartDiv.chartData, modalOptions);
        }
    }, 50);
}

// --- METADATA HELPERS ---
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

function getChartExplanation(metric) {
    const explanations = {
        average_age_of_residents: 'This Key Performance Indicator (KPI) represents the average age of all residents, providing a quick snapshot of the population\'s age demographic.',
        average_household_size: 'This KPI shows the average number of residents per household. A higher number may indicate larger family sizes within the community.',
        dependency_ratio: 'This ratio compares the number of dependents (age 0-14 and 65+) to the working-age population (15-64). A higher ratio means more financial stress on the working population.',
        sex_ratio: 'This chart illustrates the proportion of male versus female residents. It helps in understanding the gender balance within the barangay.',
        population_pyramid: 'This chart shows the distribution of various age groups, separated by gender. It is crucial for understanding the age and sex structure of the population for long-term planning.',
        generation_breakdown: 'This chart categorizes the population into major generational cohorts (e.g., Gen Z, Millennials, Gen X) to show demographic distribution and potential community needs.',
        detailed_age_brackets: 'Provides a granular, 10-year breakdown of the population by age. This is useful for planning age-specific programs (e.g., for toddlers, teens, or young adults).',
        civil_status_distribution_by_gender: 'This chart breaks down the civil status (Single, Married, etc.) of residents and further separates each category by gender.',
        household_size_distribution: 'This shows how many households have 1 person, 2 people, 3 people, and so on. It helps in understanding family structures and housing needs.',
        heads_of_household_by_gender: 'This chart displays the gender distribution of individuals identified as the head of their household.',
        relationship: 'This illustrates the relationship of members to the head of the household (e.g., Spouse, Son, Daughter), giving insight into family compositions.',
        purok: 'This bar chart displays the total number of residents in each purok, helping to identify the most and least populated areas within the barangay.',
        voter_population_by_purok: 'This chart shows the number of registered voters (residents aged 18 and above) in each purok.',
        senior_citizens_by_purok: 'This chart highlights the distribution of senior citizens (residents aged 60 and above) across different puroks, useful for senior-focused programs.',
        school_age_population_by_purok: 'This visualization breaks down the population of children and teenagers by educational level (e.g., Elementary, High School) within each purok.',
        residents_per_street: 'This chart lists the top 10 most populated streets in the barangay, which can be useful for infrastructure and service planning.',
        nationality: 'Displays the breakdown of residents by nationality.',
        blood_type: 'Shows the distribution of different blood types (O, A, B, AB) among residents, which can be critical information for health emergencies.',
        profile_completeness: 'This is a data quality metric showing the percentage of resident profiles that have key information filled out, such as contact numbers or emergency contacts.',
        emergency_contact_coverage: 'This chart shows the percentage of residents who have an emergency contact person listed versus those who do not.',
        resident_status_overview: 'Provides a summary of the current status of all residents (e.g., Active, Inactive, Moved, Deceased).'
    };
    return explanations[metric] || 'Detailed view of the selected metric.';
}