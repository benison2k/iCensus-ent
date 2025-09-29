// --- Report Modal Logic (Runs Immediately) ---
document.addEventListener('DOMContentLoaded', () => {
    const reportModal = document.getElementById('report-modal');
    const generateReportBtn = document.getElementById('generate-report-btn');
    
    // Check if the modal and button exist before adding listeners
    if (reportModal && generateReportBtn) {
        const closeBtn = reportModal.querySelector('.close-btn');
        const reportTypeSelect = document.getElementById('report_type');
        const purokSelectContainer = document.getElementById('purok_select_container');

        generateReportBtn.addEventListener('click', () => {
            reportModal.style.display = 'block';
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                reportModal.style.display = 'none';
            });
        }

        window.addEventListener('click', (event) => {
            if (event.target == reportModal) {
                reportModal.style.display = 'none';
            }
        });

        if (reportTypeSelect && purokSelectContainer) {
            reportTypeSelect.addEventListener('change', () => {
                if (reportTypeSelect.value === 'by_purok') {
                    purokSelectContainer.style.display = 'block';
                } else {
                    purokSelectContainer.style.display = 'none';
                }
            });
        }
    }

    // --- Chart Detail Modal Logic ---
    const detailModal = document.getElementById('chart-detail-modal');
    if (detailModal) {
        const closeBtn = detailModal.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => {
            detailModal.style.display = 'none';
        });
        window.addEventListener('click', (event) => {
            if (event.target == detailModal) {
                detailModal.style.display = 'none';
            }
        });
    }
});


// --- Google Charts and GridStack Logic ---
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
        cellHeight: 80,
        margin: 30, // Increased margin for more space between charts
        float: true,
        resizable: {
            handles: 'n, e, s, w, ne, nw, se, sw'
        }
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
        if (chartDiv && chartDiv.chartInstance && chartDiv.chartData && chartDiv.chartOptions) {
             if (chartDiv.chartType && (chartDiv.chartType === 'GroupedBar' || chartDiv.chartType === 'PopulationPyramid')) {
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
                    <div class="grid-stack-item-content chart-container" data-metric="${node.id}">
                        <div class="chart-title">
                            <span class="material-icons chart-icon">${getChartIcon(node.id)}</span>
                            ${getChartTitle(node.id)}
                        </div>
                        ${contentHtml}
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
        gender: 'Gender Distribution',
        age: 'Age Groups',
        purok: 'Population by Purok',
        barangay: 'Population by Barangay',
        civil_status: 'Civil Status',
        blood_type: 'Blood Type Distribution',
        nationality: 'Nationality',
        relationship: 'Relationship to Head',
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
        resident_status_overview: 'Resident Status Overview',
        youth_vs_seniors_by_purok: 'Youth vs. Seniors by Purok'
    };
    return titles[metric] || 'Unknown Chart';
}

function getChartIcon(metric) {
    const icons = {
        gender: 'wc', age: 'cake', purok: 'location_on',
        barangay: 'map', civil_status: 'favorite', blood_type: 'opacity', nationality: 'flag',
        relationship: 'people', generation_breakdown: 'groups', detailed_age_brackets: 'bar_chart',
        household_size_distribution: 'home', dependency_ratio: 'reduce_capacity', sex_ratio: 'transgender',
        population_pyramid: 'stacked_bar_chart', average_age_of_residents: 'escalator_warning',
        average_household_size: 'roofing', heads_of_household_by_gender: 'supervisor_account',
        residents_per_street: 'add_road', civil_status_distribution_by_gender: 'merge_type',
        profile_completeness: 'fact_check', blood_type_data_coverage: 'science',
        emergency_contact_coverage: 'contact_phone', voter_population_by_purok: 'where_to_vote',
        senior_citizens_by_purok: 'assist_walker', school_age_population_by_purok: 'school',
        resident_status_overview: 'visibility', youth_vs_seniors_by_purok: 'elderly_woman'
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

    if (['age', 'purok', 'barangay', 'detailed_age_brackets', 'voter_population_by_purok', 'senior_citizens_by_purok', 'average_age_by_purok'].includes(metric)) {
        return 'ColumnChart';
    }
    return 'PieChart';
}

// --- Function to get chart explanations ---
function getChartExplanation(metric) {
    const explanations = {
        gender: 'This pie chart shows the breakdown of residents by gender, providing a quick overview of the male-to-female ratio in the community.',
        age: 'This column chart categorizes the population into key age groups: children (0-17), young adults (18-35), adults (36-59), and seniors (60+). It helps in understanding the age structure of the barangay.',
        population_pyramid: 'This chart visualizes the distribution of different age groups in the population, split by gender. It is useful for understanding population trends, such as aging or youth bulges.',
        generation_breakdown: 'This chart segments the population into recognized generations (e.g., Gen Z, Millennials, Baby Boomers). It offers insights into the cultural and social landscape of the community.',
        average_age_of_residents: 'This Key Performance Indicator (KPI) shows the average age of all residents. A lower number may indicate a younger, growing community, while a higher number might suggest an aging population.',
        dependency_ratio: 'This KPI measures the ratio of dependents (people younger than 15 or older than 64) to the working-age population (15-64). A higher ratio means more pressure on the working population to support dependents.',
        default: 'This chart visualizes the selected data, providing insights into the demographic and social composition of the community.'
    };
    return explanations[metric] || explanations.default;
}


// --- Function to open the detail modal ---
function openDetailModal(metric, chartData, chartOptions) {
    const detailModal = document.getElementById('chart-detail-modal');
    const titleEl = document.getElementById('chart-detail-title');
    const contentEl = document.getElementById('chart-detail-content');
    const explanationEl = document.getElementById('chart-detail-explanation');

    titleEl.textContent = getChartTitle(metric);
    contentEl.innerHTML = ''; // Clear previous chart
    explanationEl.innerHTML = getChartExplanation(metric); // Add explanation

    // Make the modal visible *before* drawing the chart
    detailModal.style.display = 'block';

    // Use a short timeout to allow the browser to render the modal first
    setTimeout(() => {
        // Make options suitable for a larger display
        let detailOptions = JSON.parse(JSON.stringify(chartOptions)); // Deep copy
        detailOptions.width = '100%';
        detailOptions.height = '100%';
        detailOptions.chartArea = {'width': '80%', 'height': '80%'};
        detailOptions.legend.position = 'right';

        const chartType = getChartType(metric);
        
        if (chartType === 'KPI') {
            const kpiContent = document.getElementById(`${metric}_chart_div`).innerHTML;
            contentEl.innerHTML = `<div class="kpi-content large-kpi">${kpiContent}</div>`;
        } else {
            let chart;
            if (chartType === 'GroupedBar' || chartType === 'PopulationPyramid') {
                chart = new google.charts.Bar(contentEl);
                chart.draw(chartData, google.charts.Bar.convertOptions(detailOptions));
            } else {
                chart = new google.visualization[chartType](contentEl);
                chart.draw(chartData, detailOptions);
            }
        }
    }, 50); // A small delay is enough
}


function drawChart(metric) {
    fetch(`../core/analytics_data.php?metric=${metric}`)
        .then(response => {
            if (!response.ok) throw new Error(`Network response was not ok for metric: ${metric}`);
            return response.json();
        })
        .then(apiData => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            const chartContainer = chartDiv.closest('.chart-container');

            if (!chartDiv) return;
            if (apiData.error) {
                chartDiv.innerHTML = `<div class="chart-error">Error: ${apiData.error}</div>`;
                return;
            }

            const chartType = getChartType(metric);
            chartDiv.chartType = chartType;

            if(chartType === 'KPI') {
                chartDiv.innerHTML = `<div class="kpi-value">${apiData.value}</div><div class="kpi-label">${apiData.label || ''}</div>`;
                // Add click listener for KPI
                if(chartContainer) {
                    chartContainer.style.cursor = 'pointer';
                    chartContainer.onclick = () => openDetailModal(metric, apiData, {});
                }
                return;
            }

            let data;
            let options = {
                title: '',
                legend: { position: 'bottom' },
                width: '100%',
                height: '100%',
                backgroundColor: 'transparent',
                chartArea: {'width': '85%', 'height': '70%'},
                hAxis: { textStyle: { color: '#555' }, slantedText: false },
                vAxis: { textStyle: { color: '#555' }, viewWindow: { min: 0 } }
            };

            switch(chartType) {
                case 'PopulationPyramid':
                    let maxVal = 0;
                    const pyramidData = [['Age Bracket', 'Male', { role: 'style' }, 'Female', { role: 'style' }]];
                    for (const age in apiData) {
                        const maleVal = Math.abs(apiData[age]['Male'] || 0);
                        const femaleVal = Math.abs(apiData[age]['Female'] || 0);
                        if (maleVal > maxVal) maxVal = maleVal;
                        if (femaleVal > maxVal) maxVal = femaleVal;
                        pyramidData.push([age, -maleVal, 'color: #3366cc', femaleVal, 'color: #dc3912']);
                    }
                    data = google.visualization.arrayToDataTable(pyramidData);
                    options.isStacked = true;
                    const tickMax = Math.ceil(maxVal / 5) * 5;
                    options.hAxis.ticks = Array.from({length: (tickMax / 5) * 2 + 1}, (_, i) => (i - tickMax / 5) * 5).map(v => ({v: v, f: String(Math.abs(v))}));
                    break;

                case 'GroupedBar':
                    const firstRow = apiData[Object.keys(apiData)[0]];
                    if(!firstRow) return; // No data to draw
                    const headers = ['Category', ...Object.keys(firstRow)];
                    const groupData = [headers];
                    for (const cat in apiData) {
                        groupData.push([cat, ...Object.values(apiData[cat])]);
                    }
                    data = google.visualization.arrayToDataTable(groupData);
                    break;
                
                default: // PieChart, ColumnChart, BarChart
                    const dataArray = [[getChartTitle(metric), 'Count']];
                    for (const key in apiData) {
                        dataArray.push([key, apiData[key]]);
                    }
                    data = google.visualization.arrayToDataTable(dataArray);
                    if (['gender', 'civil_status', 'blood_type_data_coverage', 'emergency_contact_coverage', 'resident_status_overview', 'sex_ratio', 'heads_of_household_by_gender'].includes(metric)) {
                        options.pieHole = 0.4;
                    }
                    if (chartType.includes('Column')) options.legend = { position: 'none' };
                    break;
            }

            chartDiv.chartData = data;
            chartDiv.chartOptions = options;
            
            let chart;
            if (chartType === 'GroupedBar' || chartType === 'PopulationPyramid') {
                chart = new google.charts.Bar(chartDiv);
                chartDiv.chartInstance = chart;
                chart.draw(data, google.charts.Bar.convertOptions(options));
            } else {
                chart = new google.visualization[chartType](chartDiv);
                chartDiv.chartInstance = chart;
                chart.draw(data, options);
            }

            // --- Add click listener to the chart ---
             if(chartContainer) {
                chartContainer.style.cursor = 'pointer';
                chartContainer.onclick = () => openDetailModal(metric, data, options);
            }

        })
        .catch(error => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if(chartDiv) chartDiv.innerHTML = `<div class="chart-error">Could not load chart.</div>`;
            console.error('Error fetching/drawing chart:', metric, error);
        });
}