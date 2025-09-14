google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid;
let chartsToDraw = {};

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

    grid.on('added', function(event, items) {
        items.forEach(item => {
            const chartId = item.id;
            if (chartsToDraw[chartId]) {
                chartsToDraw[chartId]();
                delete chartsToDraw[chartId];
            }
        });
    });
    
    const redrawChart = (el) => {
        const id = el.gridstackNode.id;
        const chartDiv = document.getElementById(`${id}_chart_div`);
        if (chartDiv && chartDiv.chartInstance) {
            chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
        }
    };

    grid.on('resizestop', (event, el) => redrawChart(el));

    window.addEventListener('resize', debounce(() => {
        grid.engine.nodes.forEach(node => redrawChart(node.el));
    }, 250));

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
}

function loadLayout() {
    fetch('../core/get_layout.php')
        .then(response => response.json())
        .then(layoutData => {
            grid.removeAll();
            layoutData.forEach(node => {
                chartsToDraw[node.id] = () => drawChart(node.id);

                const isKpi = getChartType(node.id) === 'KPI';
                const contentHtml = isKpi ? 
                    `<div class="kpi-content" id="${node.id}_chart_div">Loading...</div>` :
                    `<div class="chart-div" id="${node.id}_chart_div">Loading...</div>`;

                const widgetHtml = `
                    <div>
                        <div class="grid-stack-item-content">
                            <div class="chart-title">
                                <span class="material-icons chart-icon">${getChartIcon(node.id)}</span>
                                ${getChartTitle(node.id)}
                            </div>
                            ${contentHtml}
                        </div>
                    </div>`;
                grid.addWidget(widgetHtml, node);
            });
        });
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
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

function resetLayout() {
    if (confirm('Are you sure you want to reset your layout? This will revert to the default layout and cannot be undone.')) {
        fetch('../core/reset_layout.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                loadLayout();
                alert('Layout has been reset.');
            } else {
                alert('Error resetting layout: ' + result.message);
            }
        });
    }
}

function getChartTitle(metric) {
    const titles = {
        // Original 12
        gender: 'Gender Distribution',
        age: 'Age Groups',
        status: 'Resident Status',
        purok: 'Population by Purok',
        barangay: 'Population by Barangay',
        civil_status: 'Civil Status',
        blood_type: 'Blood Type Distribution',
        nationality: 'Nationality',
        relationship: 'Relationship to Head',
        voter_status: 'Voter Status',
        senior_citizens: 'Senior Citizens (60+)',
        youth_bracket: 'Youth Bracket (15-24)',

        // New 18
        generation_breakdown: 'Generation Breakdown',
        detailed_age_brackets: 'Detailed Age Brackets (10-year)',
        household_size_distribution: 'Household Size Distribution',
        dependency_ratio: 'Dependency Ratio',
        sex_ratio: 'Sex Ratio',
        population_pyramid: 'Population Pyramid',
        average_age_of_residents: 'Average Resident Age',
        average_household_size: 'Average Household Size',
        heads_of_household_by_gender: 'Heads of Household by Gender',
        residents_per_street: 'Top 10 Streets by Population',
        civil_status_distribution_by_gender: 'Civil Status by Gender',
        profile_completeness: 'Profile Completeness (%)',
        blood_type_data_coverage: 'Blood Type Data Coverage',
        emergency_contact_coverage: 'Emergency Contact Coverage',
        voter_population_by_purok: 'Voter Population by Purok',
        senior_citizens_by_purok: 'Senior Citizens by Purok',
        school_age_population_by_purok: 'School-Age Population by Purok',
        resident_status_overview: 'Resident Status Overview'
    };
    return titles[metric] || 'Unknown Chart';
}

function getChartIcon(metric) {
    const icons = {
        gender: 'wc', age: 'cake', status: 'assignment_ind', purok: 'location_on',
        barangay: 'map', civil_status: 'favorite', blood_type: 'opacity', nationality: 'flag',
        relationship: 'people', voter_status: 'how_to_vote', senior_citizens: 'elderly',
        youth_bracket: 'school', generation_breakdown: 'groups', detailed_age_brackets: 'bar_chart',
        household_size_distribution: 'home', dependency_ratio: 'reduce_capacity', sex_ratio: 'transgender',
        population_pyramid: 'stacked_bar_chart', average_age_of_residents: 'escalator_warning',
        average_household_size: 'roofing', heads_of_household_by_gender: 'supervisor_account',
        residents_per_street: 'add_road', civil_status_distribution_by_gender: 'merge_type',
        profile_completeness: 'fact_check', blood_type_data_coverage: 'science',
        emergency_contact_coverage: 'contact_phone', voter_population_by_purok: 'where_to_vote',
        senior_citizens_by_purok: 'assist_walker', school_age_population_by_purok: 'school',
        resident_status_overview: 'visibility'
    };
    return icons[metric] || 'pie_chart';
}

function getChartType(metric) {
    const types = {
        average_age_of_residents: 'KPI',
        average_household_size: 'KPI',
        dependency_ratio: 'KPI',
        population_pyramid: 'PopulationPyramid',
        civil_status_distribution_by_gender: 'GroupedBar',
        school_age_population_by_purok: 'GroupedBar',
        youth_vs_seniors_by_purok: 'GroupedBar',
        profile_completeness: 'BarChart',
        residents_per_street: 'BarChart'
    };
    if (types[metric]) return types[metric];

    // Default types
    if (['age', 'purok', 'barangay', 'detailed_age_brackets', 'voter_population_by_purok', 'senior_citizens_by_purok', 'average_age_by_purok'].includes(metric)) {
        return 'ColumnChart';
    }
    return 'PieChart'; // Default
}


function drawChart(metric) {
    fetch(`../core/analytics_data.php?metric=${metric}`)
        .then(response => response.json())
        .then(apiData => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (!chartDiv || !apiData) return;

            const chartType = getChartType(metric);
            
            // --- KPI Card Handling ---
            if(chartType === 'KPI') {
                chartDiv.innerHTML = `<div class="kpi-value">${apiData.value}</div><div class="kpi-label">${apiData.label || ''}</div>`;
                return;
            }

            let data;
            let options = {
                title: '',
                legend: 'bottom',
                width: '100%',
                height: '100%',
                backgroundColor: 'transparent',
                chartArea: {'width': '85%', 'height': '70%'}
            };

            // --- Data & Options setup for different charts ---
            switch(chartType) {
                case 'PopulationPyramid':
                    const pyramidData = [['Age Bracket', 'Male', 'Female']];
                    for (const age in apiData) {
                        pyramidData.push([age, -Math.abs(apiData[age]['Male']), apiData[age]['Female']]);
                    }
                    data = google.visualization.arrayToDataTable(pyramidData);
                    options.isStacked = true;
                    options.hAxis = {
                        title: 'Population',
                        format: 'short',
                        ticks: [-10, -5, 0, 5, 10] // Example ticks
                    };
                    break;

                case 'GroupedBar':
                    const groupData = [['Category', 'Male', 'Female']]; // Example for gender
                    for (const cat in apiData) {
                        groupData.push([cat, apiData[cat]['Male'], apiData[cat]['Female']]);
                    }
                    data = google.visualization.arrayToDataTable(groupData);
                    break;
                
                default: // PieChart, ColumnChart, BarChart
                    const dataArray = [[getChartTitle(metric), 'Count']];
                    for (const key in apiData) {
                        dataArray.push([key, apiData[key]]);
                    }
                    data = google.visualization.arrayToDataTable(dataArray);
                    if (['gender', 'civil_status', 'voter_status', 'senior_citizens', 'youth_bracket', 'blood_type_data_coverage', 'emergency_contact_coverage', 'resident_status_overview', 'sex_ratio'].includes(metric)) {
                        options.pieHole = 0.4; // Doughnut chart
                    }
                    if (chartType.includes('Column')) options.legend = { position: 'none' };
                    break;
            }

            chartDiv.chartData = data;
            chartDiv.chartOptions = options;
            
            let chart;
            if (chartType === 'GroupedBar' || chartType === 'PopulationPyramid') {
                chart = new google.charts.Bar(chartDiv);
            } else {
                chart = new google.visualization[chartType](chartDiv);
            }
            chartDiv.chartInstance = chart;

            chart.draw(data, chartType.includes('Bar') ? google.charts.Bar.convertOptions(options) : options);
        });
}