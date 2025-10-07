document.addEventListener('DOMContentLoaded', () => {
    // --- MODAL SETUP ---
    const reportModal = document.getElementById('report-modal');
    const generateReportBtn = document.getElementById('generate-report-btn');
    const detailModal = document.getElementById('chart-detail-modal');
    const filteredModal = document.getElementById('filtered-residents-modal');
    const residentDetailModal = document.getElementById('analytics-resident-detail-modal');

    // Report Modal Listeners
    if (reportModal && generateReportBtn) {
        const closeBtn = reportModal.querySelector('.close-btn');
        generateReportBtn.addEventListener('click', () => reportModal.style.display = 'flex');
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

    // Filtered Residents Modal Listeners
    if (filteredModal) {
        const closeBtn = filteredModal.querySelector('.close-btn');
        if (closeBtn) closeBtn.addEventListener('click', () => filteredModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target === filteredModal) filteredModal.style.display = 'none';
        });
    }

    // Resident Detail Modal Listeners
    if (residentDetailModal) {
        const closeBtn = residentDetailModal.querySelector('.close-btn');
        if (closeBtn) closeBtn.addEventListener('click', () => residentDetailModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target === residentDetailModal) residentDetailModal.style.display = 'none';
        });
    }

    // --- THIS IS THE FIX: Event delegation for dynamically added buttons ---
    document.body.addEventListener('click', function(e) {
        const viewButton = e.target.closest('.analytics-view-btn');
        if (viewButton) {
            const residentId = viewButton.dataset.id;
            openResidentDetailsModal(residentId);
        }
    });
});

// --- GOOGLE CHARTS & GRIDSTACK SETUP ---
google.charts.load('current', {
    'packages': ['corechart', 'bar']
});
google.charts.setOnLoadCallback(initializeDashboard);

let grid;
const chartsToDraw = {};
const basePath = '/iCensus-ent/public';

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
        margin: 20,
        float: true,
        resizable: {
            handles: 'n, e, s, w, ne, nw, se, sw'
        }
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
    const layout = serializedData.map(d => ({
        id: d.id,
        x: d.x,
        y: d.y,
        w: d.w,
        h: d.h
    }));
    fetch(`${basePath}/analytics/layout/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(layout)
        })
        .then(res => res.json()).then(result => alert(result.status === 'success' ? 'Layout saved!' : 'Error saving layout.'));
}

function resetLayout() {
    if (confirm('Are you sure you want to reset your layout to the default?')) {
        fetch(`${basePath}/analytics/layout/reset`, {
                method: 'POST'
            })
            .then(res => res.json()).then(result => {
                if (result.status === 'success') loadLayout();
            });
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

            const isDarkMode = document.body.classList.contains('dark-mode');
            const fontColor = isDarkMode ? '#CFD8DC' : '#333';

            let data;
            let options = {
                title: '',
                width: '100%',
                height: '100%',
                backgroundColor: 'transparent',
                chartArea: {
                    'width': '85%',
                    'height': '70%'
                },
                legend: {
                    position: 'bottom',
                    textStyle: {
                        color: fontColor
                    }
                },
                hAxis: {
                    textStyle: {
                        color: fontColor
                    },
                    titleTextStyle: {
                        color: fontColor
                    }
                },
                vAxis: {
                    textStyle: {
                        color: fontColor
                    },
                    titleTextStyle: {
                        color: fontColor
                    }
                }
            };


            if (chartType === 'PopulationPyramid') {
                const pyramidData = [
                    ['Age', 'Male', {
                        role: 'style'
                    }, 'Female', {
                        role: 'style'
                    }]
                ];
                for (const age in apiData) {
                    const maleVal = Math.abs(apiData[age]['Male'] || 0);
                    const femaleVal = Math.abs(apiData[age]['Female'] || 0);
                    pyramidData.push([age, maleVal, 'color: #3366cc', femaleVal, 'color: #ffc0cb']);
                }
                data = google.visualization.arrayToDataTable(pyramidData);
                options.isStacked = false;
                options.hAxis.title = 'Population';

            } else if (chartType === 'GroupedBar') {
                const categories = Object.keys(apiData);
                if (categories.length === 0) {
                    chartDiv.innerHTML = `<div class="chart-error">No data available.</div>`;
                    return;
                }
                const firstCategoryData = apiData[categories[0]];
                const groups = Object.keys(firstCategoryData);
                const dataArray = [
                    [getChartTitle(metric), ...groups]
                ];
                for (const category in apiData) {
                    const row = [category];
                    groups.forEach(group => {
                        row.push(apiData[category][group] || 0);
                    });
                    dataArray.push(row);
                }
                data = google.visualization.arrayToDataTable(dataArray);
                options.hAxis.title = '';

            } else {
                const dataArray = [
                    [getChartTitle(metric), 'Count']
                ];
                for (const key in apiData) {
                    dataArray.push([key, apiData[key]]);
                }
                data = google.visualization.arrayToDataTable(dataArray);
            }

            if (metric === 'gender' || metric === 'civil_status' || metric === 'sex_ratio') options.pieHole = 0.4;
            chartDiv.chartData = data;
            chartDiv.chartOptions = options;

            let chart;
            if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') chart = new google.charts.Bar(chartDiv);
            else if (chartType === 'ColumnChart') chart = new google.visualization.ColumnChart(chartDiv);
            else if (chartType === 'BarChart') chart = new google.visualization.BarChart(chartDiv);
            else chart = new google.visualization.PieChart(chartDiv);

            chartDiv.chartInstance = chart;
            if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') chart.draw(data, google.charts.Bar.convertOptions(options));
            else chart.draw(data, options);
        })
        .catch(error => {
            console.error('Error fetching/drawing chart:', metric, error);
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (chartDiv) chartDiv.innerHTML = `<div class="chart-error">Could not load.</div>`;
        });
}

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
    modalOptions.chartArea = {
        'width': '80%',
        'height': '80%'
    };
    modalOptions.legend.position = 'right';

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';

    modalOptions.legend.textStyle = {
        color: fontColor
    };
    if (modalOptions.hAxis) {
        modalOptions.hAxis.textStyle = {
            color: fontColor
        };
        modalOptions.hAxis.titleTextStyle = {
            color: fontColor
        };
    }
    if (modalOptions.vAxis) {
        modalOptions.vAxis.textStyle = {
            color: fontColor
        };
        modalOptions.vAxis.titleTextStyle = {
            color: fontColor
        };
    }

    const chartType = originalChartDiv.chartType;
    let chart;
    if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') chart = new google.charts.Bar(chartContentDiv);
    else if (chartType === 'ColumnChart') chart = new google.visualization.ColumnChart(chartContentDiv);
    else if (chartType === 'BarChart') chart = new google.visualization.BarChart(chartContentDiv);
    else chart = new google.visualization.PieChart(chartContentDiv);

    modal.style.display = 'flex';

    google.visualization.events.addListener(chart, 'select', () => {
        const selection = chart.getSelection();
        if (selection.length > 0) {
            const row = selection[0].row;
            const dataTable = originalChartDiv.chartData;
            const category = dataTable.getValue(row, 0);

            const column = selection[0].column;
            let series = null;
            if (chartType === 'PopulationPyramid') {
                if (column === 1) series = 'Male';
                if (column === 3) series = 'Female'; // Column 3 because of a hidden style column
            } else if (chartType === 'GroupedBar') {
                if (column > 0) { // Column 0 is the category label itself
                    series = dataTable.getColumnLabel(column);
                }
            }

            const filterParams = getFilterParamForMetric(metric, category, series);
            if (filterParams) {
                showFilteredResidentsModal(filterParams, category, series);
            }
        }
    });

    setTimeout(() => {
        if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') {
            chart.draw(originalChartDiv.chartData, google.charts.Bar.convertOptions(modalOptions));
        } else {
            chart.draw(originalChartDiv.chartData, modalOptions);
        }
    }, 50);
}

async function showFilteredResidentsModal(params, category, series = null) {
    const modal = document.getElementById('filtered-residents-modal');
    const titleEl = document.getElementById('filtered-title');
    const tableBody = modal.querySelector('tbody');

    tableBody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
    modal.style.display = 'flex';
    titleEl.textContent = 'Loading...'; // Set a placeholder title

    try {
        const response = await fetch(`${basePath}/analytics/filtered-residents?${params.toString()}`);
        const result = await response.json();
        
        const count = result.residents ? result.residents.length : 0;
        const cleanCategory = category.split(' = ')[0]; // Clean up labels like "Single = 15" to just "Single"

        let titleText;
        if (series) {
            // Handles grouped charts like the population pyramid or civil status by gender
            titleText = `Number of Residents that are ${series} and ${cleanCategory}: ${count}`;
        } else {
            titleText = `Number of Residents that are ${cleanCategory}: ${count}`;
        }
        titleEl.textContent = titleText;


        modal.querySelector('thead tr').innerHTML = `
            <th>Full Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Status</th>
            <th>Actions</th>`;

        if (result.status === 'success' && result.residents.length > 0) {
            tableBody.innerHTML = '';
            result.residents.forEach(r => {
                const row = `<tr>
                    <td>${r.first_name} ${r.last_name}</td>
                    <td>${r.age}</td>
                    <td>${r.gender}</td>
                    <td>${r.house_no} ${r.street}, Purok ${r.purok}</td>
                    <td><span class="status-label status-${(r.status || '').toLowerCase()}">${r.status}</span></td>
                    <td>
                        <button class="action-btn analytics-view-btn material-icons" data-id="${r.id}" title="View More Details">more_vert</button>
                    </td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6">No residents found for this selection.</td></tr>';
        }
    } catch (error) {
        console.error("Failed to fetch filtered residents:", error);
        titleEl.textContent = 'Error Loading Data';
        tableBody.innerHTML = '<tr><td colspan="6">Error loading data.</td></tr>';
    }
}

async function openResidentDetailsModal(residentId) {
    const modal = document.getElementById('analytics-resident-detail-modal');
    const modalTitle = document.getElementById('detail-modal-title');
    const modalContent = document.getElementById('detail-modal-content');
    
    modalContent.innerHTML = '<p>Loading...</p>';
    modal.style.display = 'flex';

    try {
        const res = await fetch(`${basePath}/residents/process?action=get&resident_id=${residentId}`);
        const result = await res.json();
        if (result.status !== 'success' || !result.resident) {
            modalContent.innerHTML = '<p>Error: Could not fetch resident details.</p>';
            return;
        }

        const r = result.resident;
        modalTitle.textContent = `Details for ${r.first_name} ${r.last_name}`;

        const booleanCheck = (value) => value == 1 ? 'Yes' : 'No';

        // Build the HTML content dynamically
        modalContent.innerHTML = `
            <div class="detail-group">
                <h4><span class="material-icons">person</span>Personal Info</h4>
                <div class="detail-item"><strong>Full Name:</strong> <span>${r.first_name || ''} ${r.middle_name || ''} ${r.last_name || ''} ${r.suffix || ''}</span></div>
                <div class="detail-item"><strong>Date of Birth:</strong> <span>${r.dob}</span></div>
                <div class="detail-item"><strong>Gender:</strong> <span>${r.gender}</span></div>
                <div class="detail-item"><strong>Civil Status:</strong> <span>${r.civil_status || 'N/A'}</span></div>
                <div class="detail-item"><strong>Nationality:</strong> <span>${r.nationality || 'N/A'}</span></div>
            </div>
            <div class="detail-group">
                <h4><span class="material-icons">home</span>Address & Household</h4>
                <div class="detail-item"><strong>Address:</strong> <span>${r.house_no || ''} ${r.street || ''}, Purok ${r.purok || ''}</span></div>
                <div class="detail-item"><strong>Household No:</strong> <span>${r.household_no || 'N/A'}</span></div>
                <div class="detail-item"><strong>Head of Household:</strong> <span>${r.head_of_household || 'N/A'}</span></div>
                <div class="detail-item"><strong>Relationship:</strong> <span>${r.relationship || 'N/A'}</span></div>
                <div class="detail-item"><strong>Ownership Status:</strong> <span>${r.ownership_status || 'N/A'}</span></div>
            </div>
            <div class="detail-group">
                <h4><span class="material-icons">contact_phone</span>Contact & Health</h4>
                <div class="detail-item"><strong>Contact No:</strong> <span>${r.contact_number || 'N/A'}</span></div>
                <div class="detail-item"><strong>Email:</strong> <span>${r.email || 'N/A'}</span></div>
                <div class="detail-item"><strong>PhilHealth No:</strong> <span>${r.philhealth_no || 'N/A'}</span></div>
                <div class="detail-item"><strong>Blood Type:</strong> <span>${r.blood_type || 'N/A'}</span></div>
            </div>
             <div class="detail-group">
                <h4><span class="material-icons">work</span>Education & Occupation</h4>
                <div class="detail-item"><strong>Education:</strong> <span>${r.educational_attainment || 'N/A'}</span></div>
                <div class="detail-item"><strong>Occupation:</strong> <span>${r.occupation || 'N/A'}</span></div>
            </div>
            <div class="detail-group">
                <h4><span class="material-icons">admin_panel_settings</span>Administrative</h4>
                <div class="detail-item"><strong>Resident Status:</strong> <span>${r.status}</span></div>
                <div class="detail-item"><strong>Registered Voter:</strong> <span>${booleanCheck(r.is_registered_voter)}</span></div>
                <div class="detail-item"><strong>PWD:</strong> <span>${booleanCheck(r.is_pwd)}</span></div>
                <div class="detail-item"><strong>Solo Parent:</strong> <span>${booleanCheck(r.is_solo_parent)}</span></div>
                <div class="detail-item"><strong>4Ps Member:</strong> <span>${booleanCheck(r.is_4ps_member)}</span></div>
            </div>
        `;

    } catch (err) {
        console.error('Failed to fetch resident data:', err);
        modalContent.innerHTML = '<p>An error occurred while fetching details.</p>';
    }
}

// public/assets/js/analytics.js

function getFilterParamForMetric(metric, category, series = null) {
    let params = new URLSearchParams();
    const cleanCategory = category.split(' = ')[0];

    switch (metric) {
        case 'gender':
        case 'civil_status':
        case 'purok':
        case 'blood_type':
        case 'nationality':
        case 'occupation':
        case 'educational_attainment':
        case 'ownership_status':
        case 'relationship':
        case 'resident_status_overview':
        case 'residents_per_street':
            const paramKey = (metric === 'resident_status_overview') ? 'status' : (metric === 'residents_per_street' ? 'street' : metric.replace('_status', ''));
            params.set(paramKey, cleanCategory);
            break;
        case 'pwd_distribution':
            params.set('is_pwd', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case 'solo_parent_distribution':
            params.set('is_solo_parent', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case '4ps_distribution':
            params.set('is_4ps_member', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case 'age':
        case 'detailed_age_brackets':
            const ages = cleanCategory.match(/\d+/g);
            if (ages) {
                params.set('age_min', ages[0]);
                if (ages.length > 1) params.set('age_max', ages[1]);
            }
            break;
        case 'population_pyramid':
            const ageBrackets = cleanCategory.match(/\d+/g);
            if (ageBrackets) {
                params.set('age_min', ageBrackets[0]);
                if (ageBrackets.length > 1) params.set('age_max', ageBrackets[1]);
            }
            if (series) params.set('gender', series);
            break;
        case 'generation_breakdown':
            params.set('generation', cleanCategory);
            break;
        case 'sex_ratio':
            params.set('gender', cleanCategory);
            break;
        case 'heads_of_household_by_gender':
            params.set('gender', cleanCategory);
            params.set('is_head', 'Yes');
            break;
        case 'voter_population_by_purok':
            params.set('purok', cleanCategory);
            params.set('is_voter', '1');
            break;
        case 'senior_citizens_by_purok':
            params.set('purok', cleanCategory);
            params.set('age_min', '60');
            break;
        case 'emergency_contact_coverage':
            params.set('has_emergency_contact', cleanCategory.startsWith('Has') ? 'Yes' : 'No');
            break;
        
        // --- NEWLY ADDED CASES ---
        case 'civil_status_distribution_by_gender':
            params.set('civil_status', cleanCategory);
            if(series) params.set('gender', series);
            break;
        case 'household_size_distribution':
            const size = cleanCategory.match(/\d+/);
            if (size) params.set('household_size', size[0]);
            break;
        case 'school_age_population_by_purok':
             params.set('purok', cleanCategory);
             if (series) {
                const ageGroup = series.match(/\((\d+)-(\d+)\)/);
                if (ageGroup) {
                    params.set('age_min', ageGroup[1]);
                    params.set('age_max', ageGroup[2]);
                }
            }
            break;
        case 'profile_completeness':
            const fieldMap = { 'Contact Info': 'contact_number', 'Email': 'email', 'Emergency Contact': 'emergency_name', 'Blood Type': 'blood_type'};
            const field = fieldMap[cleanCategory];
            if(field) params.set('has_field', field);
            break;
            
        default:
            return null;
    }
    return params;
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
        resident_status_overview: 'Resident Status Overview', civil_status_distribution_by_gender: 'Civil Status by Gender',
        educational_attainment: 'Educational Attainment', occupation: 'Top 15 Occupations', ownership_status: 'Household Ownership Status',
        pwd_distribution: 'PWD Distribution', solo_parent_distribution: 'Solo Parent Distribution', '4ps_distribution': '4Ps Beneficiaries'
    };
    return t[metric] || 'Chart';
}

function getChartIcon(metric) {
    const i = {
        gender: 'wc', age: 'cake', purok: 'location_on', generation_breakdown: 'groups',
        dependency_ratio: 'reduce_capacity', sex_ratio: 'transgender', population_pyramid: 'stacked_bar_chart',
        average_age_of_residents: 'escalator_warning', average_household_size: 'roofing', civil_status: 'favorite',
        educational_attainment: 'school', occupation: 'work', ownership_status: 'home', pwd_distribution: 'accessible',
        solo_parent_distribution: 'person', '4ps_distribution': 'savings'
    };
    return i[metric] || 'pie_chart';
}

function getChartType(metric) {
    const t = {
        average_age_of_residents: 'KPI', average_household_size: 'KPI', dependency_ratio: 'KPI',
        population_pyramid: 'PopulationPyramid', 
        civil_status_distribution_by_gender: 'GroupedBar',
        school_age_population_by_purok: 'GroupedBar',
        age: 'ColumnChart', detailed_age_brackets: 'ColumnChart', purok: 'BarChart',
        educational_attainment: 'BarChart', occupation: 'BarChart'
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
        resident_status_overview: 'Provides a summary of the current status of all residents (e.g., Active, Inactive, Moved, Deceased).',
        educational_attainment: 'This chart displays the distribution of the highest educational level achieved by residents, from elementary to college graduates.',
        occupation: 'This bar chart shows the top 15 most common occupations reported by residents, providing insight into the local economy and workforce.',
        ownership_status: 'This chart breaks down the housing situation in the barangay, showing the proportion of residents who own their homes, rent, or live with relatives.',
        pwd_distribution: 'This chart shows the number of residents identified as Persons with Disabilities (PWDs) versus those who are not.',
        solo_parent_distribution: 'This chart illustrates the distribution of residents who are registered as solo parents.',
        '4ps_distribution': 'This chart shows the proportion of households that are beneficiaries of the Pantawid Pamilyang Pilipino Program (4Ps).'
    };
    return explanations[metric] || 'Detailed view of the selected metric.';
}