// public/assets/js/dynamic_analytics.js

// Load Google Charts and create a global promise that resolves when it's ready.
window.googleChartsPromise = new Promise(resolve => {
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(resolve);
});

document.addEventListener('DOMContentLoaded', () => {
    // Once the DOM is ready AND Google Charts is loaded, initialize the dashboard.
    window.googleChartsPromise.then(() => {
        initializeDynamicDashboard();
    });

    // --- MODAL CLOSING LOGIC ---
    // This handles all modals on the page
    document.querySelectorAll('.modal').forEach(modal => {
        const closeBtn = modal.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
});

const basePath = '/iCensus-ent/public';
let grid;
// --- Global state for sorting resident list ---
let currentResidentList = [];
let currentSort = { column: 'first_name', order: 'asc' };

function initializeDynamicDashboard() {
    grid = GridStack.init({
        cellHeight: 80,
        margin: 20,
        float: true,
    });

    loadUserCharts();

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', () => {
        if(confirm('Are you sure you want to reset the layout? This will clear the current dashboard and reload with default positions.')) {
            localStorage.removeItem('chartLayout');
            localStorage.removeItem('visibleChartIds');
            location.reload();
        }
    });
}

async function loadUserCharts() {
    try {
        const response = await fetch(`${basePath}/charts/user-charts`);
        const result = await response.json();
        
        const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || null;

        if (result.status === 'success' && result.charts) {
            const chartsToDisplay = result.charts.filter(chart => visibleChartIds === null || visibleChartIds.includes(chart.id.toString()));
            const savedLayout = JSON.parse(localStorage.getItem('chartLayout')) || [];

            for (const chartDef of chartsToDisplay) {
                const chartId = chartDef.id.toString();

                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" 
                         data-chart-id="${chartId}" 
                         data-group-by="${chartDef.group_by_column || ''}">
                        <div class="chart-title">${chartDef.title}</div>
                        <div class="chart-div" id="chart-div-${chartId}">Loading...</div>
                    </div>`;
                
                const layoutItem = savedLayout.find(item => item.id === chartId);
                const gridOptions = layoutItem ? { w: layoutItem.w, h: layoutItem.h, x: layoutItem.x, y: layoutItem.y, id: chartId } : { w: 4, h: 4, id: chartId };
                
                grid.addWidget(widgetHtml, gridOptions);
                
                const dataResponse = await fetch(`${basePath}/charts/data?chart_id=${chartId}`);
                const dataResult = await dataResponse.json();

                if (dataResult.status === 'success') {
                    const chartDiv = document.getElementById(`chart-div-${chartId}`);
                    chartDiv.chartData = dataResult.data;
                    chartDiv.chartType = dataResult.type;
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

function drawChart(chartId, chartType, chartData) {
    const chartDiv = (chartId === 'DetailContent') ? document.getElementById('chartDetailContent') : document.getElementById(`chart-div-${chartId}`);
    if (!chartDiv) return null;

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
        return null;
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
        default:
            chart = new google.visualization.PieChart(chartDiv);
            if (chartType === 'DonutChart') options.pieHole = 0.4;
            break;
    }
    chart.draw(dataTable, options);
    return { chart, dataTable };
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
    }));
    localStorage.setItem('chartLayout', JSON.stringify(layout));
    alert('Layout Saved!');
}

function renderResidentList() {
    const residentListContainer = document.getElementById('residentListContainer');
    if (!residentListContainer) return;

    currentResidentList.sort((a, b) => {
        const valA = a[currentSort.column];
        const valB = b[currentSort.column];
        let comparison = 0;
        if (valA > valB) comparison = 1;
        else if (valA < valB) comparison = -1;
        return (currentSort.order === 'desc') ? (comparison * -1) : comparison;
    });

    let tableHtml = `<table><thead><tr>
        <th>#</th>
        <th class="sortable" data-column="first_name">Name</th>
        <th class="sortable" data-column="age">Age</th>
        <th class="sortable" data-column="purok">Purok</th>
        <th>Actions</th>
    </tr></thead><tbody>`;

    if (currentResidentList.length > 0) {
        currentResidentList.forEach((r, index) => {
            const fullName = `${r.first_name || ''} ${r.last_name || ''}`.trim();
            tableHtml += `<tr>
                <td>${index + 1}</td>
                <td>${fullName}</td>
                <td>${r.age || 'N/A'}</td>
                <td>${r.purok || 'N/A'}</td>
                <td><button class="resident-info-btn" data-id="${r.id}">More Info</button></td>
            </tr>`;
        });
    } else {
        tableHtml += '<tr><td colspan="5">No residents found.</td></tr>';
    }
    tableHtml += '</tbody></table>';
    residentListContainer.innerHTML = tableHtml;

    document.querySelectorAll('#residentListContainer .sortable').forEach(th => {
        if (th.dataset.column === currentSort.column) {
            th.innerHTML += currentSort.order === 'asc' ? ' &#9650;' : ' &#9660;';
        }
    });
}

async function showFilteredResidents(filterColumn, category) {
    const residentListContainer = document.getElementById('residentListContainer');
    residentListContainer.innerHTML = '<div class="list-placeholder">Loading residents...</div>';
    currentSort = { column: 'first_name', order: 'asc' };

    if (['is_pwd', 'is_solo_parent', 'is_4ps_member', 'is_registered_voter', 'is_indigent'].includes(filterColumn)) {
        category = (category.toLowerCase() === 'yes') ? '1' : '0';
    }

    const filterParams = new URLSearchParams({ [filterColumn]: category });
    
    try {
        const response = await fetch(`${basePath}/analytics/filtered-residents?${filterParams}`);
        const result = await response.json();
        currentResidentList = (result.status === 'success' && result.residents) ? result.residents : [];
        renderResidentList();
    } catch (error) {
        console.error("Error fetching filtered residents:", error);
        currentResidentList = [];
        renderResidentList();
    }
}

function showChartDetailModal(chartId) {
    const chartContainer = document.querySelector(`.chart-container[data-chart-id='${chartId}']`);
    const sourceChartDiv = document.getElementById(`chart-div-${chartId}`);
    const modal = document.getElementById('chartDetailModal');
    const modalTitle = document.getElementById('chartDetailTitle');
    const residentListContainer = document.getElementById('residentListContainer');

    if (!sourceChartDiv || !sourceChartDiv.chartData || !modal) return;

    const chartTitle = chartContainer.querySelector('.chart-title').textContent;
    const groupByColumn = chartContainer.dataset.groupBy;
    modalTitle.textContent = chartTitle;
    residentListContainer.innerHTML = '<div class="list-placeholder">Click on a chart segment to see the list of residents.</div>';

    modal.style.display = 'flex';

    setTimeout(() => {
        const chartObj = drawChart('DetailContent', sourceChartDiv.chartType, sourceChartDiv.chartData);
        
        if(chartObj && chartObj.chart && groupByColumn) {
            google.visualization.events.addListener(chartObj.chart, 'select', () => {
                const selection = chartObj.chart.getSelection();
                if (selection.length > 0) {
                    const { row } = selection[0];
                    if (row !== null) {
                        const category = chartObj.dataTable.getValue(row, 0);
                        showFilteredResidents(groupByColumn, category);
                        chartObj.chart.setSelection([]);
                    }
                }
            });
        }
    }, 50);
}

async function openResidentDetailsModal(residentId) {
    const modal = document.getElementById('analytics-resident-detail-modal');
    const modalContent = modal.querySelector('.modal-content');
    modalContent.innerHTML = 'Loading...';
    modal.style.display = 'flex';

    try {
        const response = await fetch(`${basePath}/residents/process?action=get&resident_id=${residentId}`);
        const result = await response.json();

        if (result.status === 'success' && result.resident) {
            const r = result.resident;
            const booleanCheck = (value) => value == 1 ? 'Yes' : 'No';
            const fullName = `${r.first_name || ''} ${r.middle_name || ''} ${r.last_name || ''} ${r.suffix || ''}`.trim();

            modalContent.innerHTML = `
                <span class="close-btn material-icons">close</span>
                <h3 style="text-align: left; margin-top: 0;">Details for ${fullName}</h3>
                <div class="resident-details-grid">
                    <div class="detail-group">
                        <h4><span class="material-icons">person</span>Personal Info</h4>
                        <div class="detail-item"><strong>Date of Birth:</strong> <span>${r.dob}</span></div>
                        <div class="detail-item"><strong>Gender:</strong> <span>${r.gender}</span></div>
                        <div class="detail-item"><strong>Civil Status:</strong> <span>${r.civil_status || 'N/A'}</span></div>
                        <div class="detail-item"><strong>Nationality:</strong> <span>${r.nationality || 'N/A'}</span></div>
                    </div>
                    <div class="detail-group">
                        <h4><span class="material-icons">home</span>Address & Household</h4>
                        <div class="detail-item"><strong>Address:</strong> <span>${r.house_no || ''} ${r.street || ''}, Purok ${r.purok || ''}</span></div>
                        <div class="detail-item"><strong>Head of Household:</strong> <span>${r.head_of_household || 'N/A'}</span></div>
                        <div class="detail-item"><strong>Relationship:</strong> <span>${r.relationship || 'N/A'}</span></div>
                    </div>
                    <div class="detail-group">
                        <h4><span class="material-icons">contact_phone</span>Contact & Health</h4>
                        <div class="detail-item"><strong>Contact No:</strong> <span>${r.contact_number || 'N/A'}</span></div>
                        <div class="detail-item"><strong>Blood Type:</strong> <span>${r.blood_type || 'N/A'}</span></div>
                    </div>
                    <div class="detail-group">
                        <h4><span class="material-icons">admin_panel_settings</span>Administrative</h4>
                        <div class="detail-item"><strong>Resident Status:</strong> <span>${r.status}</span></div>
                        <div class="detail-item"><strong>Registered Voter:</strong> <span>${booleanCheck(r.is_registered_voter)}</span></div>
                    </div>
                </div>`;
        } else {
            modalContent.innerHTML = 'Error: Could not fetch resident details.';
        }
    } catch (error) {
        console.error("Error fetching resident details:", error);
        modalContent.innerHTML = 'An error occurred while fetching details.';
    }
}

document.addEventListener('click', function(event) {
    const chartContainer = event.target.closest('.chart-container');
    if (chartContainer) {
        const chartId = chartContainer.dataset.chartId;
        const chartDiv = document.getElementById(`chart-div-${chartId}`);
        if (chartDiv && chartDiv.chartType !== 'KPI') {
            showChartDetailModal(chartId);
        }
    }
    
    const sortableHeader = event.target.closest('#residentListContainer .sortable');
    if (sortableHeader) {
        const column = sortableHeader.dataset.column;
        if (currentSort.column === column) {
            currentSort.order = (currentSort.order === 'asc') ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.order = 'asc';
        }
        renderResidentList();
    }
    
    // --- NEW: Event listener for the "More Info" button ---
    const infoBtn = event.target.closest('.resident-info-btn');
    if (infoBtn) {
        openResidentDetailsModal(infoBtn.dataset.id);
    }
});

window.addChartToDashboard = async function(chartDef) {
    const chartId = chartDef.id.toString();
    try {
        await window.googleChartsPromise;
        const dataResponse = await fetch(`${basePath}/charts/data?chart_id=${chartId}`);
        const dataResult = await dataResponse.json();

        if (dataResult.status === 'success') {
            const widgetHtml = `
                <div class="grid-stack-item-content chart-container" 
                     data-chart-id="${chartId}" 
                     data-group-by="${chartDef.group_by_column || ''}">
                    <div class="chart-title">${dataResult.title}</div>
                    <div class="chart-div" id="chart-div-${chartId}"></div>
                </div>`;
            
            grid.addWidget(widgetHtml, { w: 4, h: 4, id: chartId });

            const newChartDiv = document.getElementById(`chart-div-${chartId}`);
            newChartDiv.chartData = dataResult.data;
            newChartDiv.chartType = dataResult.type;
            
            drawChart(chartId, dataResult.type, dataResult.data);
        } else {
            alert('Could not dynamically add chart. Please refresh the page.');
        }
    } catch (error) {
        console.error('Failed to add chart to dashboard:', error);
    }
};