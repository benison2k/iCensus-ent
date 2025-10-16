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

    // Modal Closing Logic for all modals
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
let currentResidentList = [];
let currentSort = { column: 'first_name', order: 'asc' };

function initializeDynamicDashboard() {
    const autoFillEnabled = JSON.parse(localStorage.getItem('autoFillCharts')) ?? true;

    grid = GridStack.init({
        cellHeight: 80,
        margin: 20,
        float: autoFillEnabled,
    });

    const autoFillSwitch = document.getElementById('autoFillSwitch');
    autoFillSwitch.checked = autoFillEnabled;

    autoFillSwitch.addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        grid.float(isEnabled);
        if (isEnabled) {
            grid.compact();
        }
        localStorage.setItem('autoFillCharts', isEnabled);

        // --- AJAX call to save preference on the server ---
        try {
            const formData = new FormData();
            formData.append('autoFill', isEnabled);
            
            await fetch(`${basePath}/analytics/preferences/save`, {
                method: 'POST',
                body: formData
            });
            // You can add a success/error message here if you like
        } catch (error) {
            console.error('Failed to save auto-fill preference:', error);
        }
    });

    loadUserCharts();

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', () => {
        if(confirm('Are you sure you want to reset the layout? This will clear all chart settings, including saved date ranges.')) {
            localStorage.removeItem('chartLayout');
            localStorage.removeItem('visibleChartIds');
            localStorage.removeItem('autoFillCharts');
            // Clear all chart-specific date ranges
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('chartDateRange_')) {
                    localStorage.removeItem(key);
                }
            });
            location.reload();
        }
    });
}

async function loadUserCharts() {
    if (grid) {
        grid.removeAll(false); // Clear existing charts without destroying the grid
    }
    
    try {
        const response = await fetch(`${basePath}/charts/user-charts`);
        const result = await response.json();
        
        if (result.status === 'success' && result.charts) {
            let visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds'));
            
            if (visibleChartIds === null) {
                visibleChartIds = result.charts.map(chart => chart.id.toString());
                localStorage.setItem('visibleChartIds', JSON.stringify(visibleChartIds));
            }

            const chartsToDisplay = result.charts.filter(chart => visibleChartIds.includes(chart.id.toString()));
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
                
                const savedDates = JSON.parse(localStorage.getItem(`chartDateRange_${chartId}`)) || {};
                let dataUrl = `${basePath}/charts/data?chart_id=${chartId}`;
                if (savedDates.start && savedDates.end) {
                    dataUrl += `&start_date=${savedDates.start}&end_date=${savedDates.end}`;
                }

                const dataResponse = await fetch(dataUrl);
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
    const layout = serializedData.map(d => ({ id: d.id, x: d.x, y: d.y, w: d.w, h: d.h }));
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
        tableHtml += '<tr><td colspan="5" style="text-align:center;">No residents found.</td></tr>';
    }
    tableHtml += '</tbody></table>';
    residentListContainer.innerHTML = tableHtml;

    document.querySelectorAll('#residentListContainer .sortable').forEach(th => {
        if (th.dataset.column === currentSort.column) {
            th.innerHTML += currentSort.order === 'asc' ? ' &#9650;' : ' &#9660;';
        }
    });
}

async function showFilteredResidents(filterColumn, category, startDate, endDate) {
    const residentListContainer = document.getElementById('residentListContainer');
    residentListContainer.innerHTML = '<div class="list-placeholder">Loading residents...</div>';
    currentSort = { column: 'first_name', order: 'asc' };

    if (['is_pwd', 'is_solo_parent', 'is_4ps_member', 'is_registered_voter', 'is_indigent'].includes(filterColumn)) {
        category = (category.toLowerCase() === 'yes') ? '1' : '0';
    } else if (filterColumn === 'employment_status') {
        category = category.toLowerCase();
    }

    const filterParams = new URLSearchParams({ [filterColumn]: category });
    if (startDate) filterParams.append('start_date', startDate);
    if (endDate) filterParams.append('end_date', endDate);
    
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

async function redrawDashboardChart(chartId, startDate, endDate) {
    const chartDiv = document.getElementById(`chart-div-${chartId}`);
    if (!chartDiv) return;

    chartDiv.innerHTML = 'Loading...'; 

    let dataUrl = `${basePath}/charts/data?chart_id=${chartId}`;
    if (startDate && endDate) {
        dataUrl += `&start_date=${startDate}&end_date=${endDate}`;
    }

    try {
        const dataResponse = await fetch(dataUrl);
        const dataResult = await dataResponse.json();

        if (dataResult.status === 'success') {
            chartDiv.chartData = dataResult.data;
            chartDiv.chartType = dataResult.type;
            drawChart(chartId, dataResult.type, dataResult.data);
        } else {
            chartDiv.innerHTML = `<div class="chart-error">Error loading data.</div>`;
        }
    } catch (error) {
        console.error(`Failed to redraw chart ${chartId}:`, error);
        chartDiv.innerHTML = `<div class="chart-error">An error occurred.</div>`;
    }
}

function showChartDetailModal(chartId) {
    const chartContainer = document.querySelector(`.chart-container[data-chart-id='${chartId}']`);
    const modal = document.getElementById('chartDetailModal');
    modal.dataset.chartId = chartId;
    const chartDetailContent = document.getElementById('chartDetailContent');
    const modalGrid = modal.querySelector('.modal-grid');
    const modalTitle = document.getElementById('chartDetailTitle');
    const residentListContainer = document.getElementById('residentListContainer');
    const startDateInput = modal.querySelector('#modalStartDate');
    const endDateInput = modal.querySelector('#modalEndDate');
    const filterBtn = modal.querySelector('#modalFilterBtn');
    const clearBtn = modal.querySelector('#modalClearBtn');
    const editBtn = modal.querySelector('#editChartFromModalBtn');
    const hideBtn = modal.querySelector('#hideChartFromModalBtn');
    const deleteBtn = modal.querySelector('#deleteChartFromModalBtn');

    const chartTitle = chartContainer.querySelector('.chart-title').textContent;
    const groupByColumn = chartContainer.dataset.groupBy;
    modalTitle.textContent = chartTitle;
    
    const originalChartDiv = document.getElementById(`chart-div-${chartId}`);
    const chartType = originalChartDiv.chartType;

    const savedDates = JSON.parse(localStorage.getItem(`chartDateRange_${chartId}`)) || {};
    startDateInput.value = savedDates.start || '';
    endDateInput.value = savedDates.end || '';

    const fetchAndDisplayKpiResidents = async (start, end) => {
        residentListContainer.innerHTML = '<div class="list-placeholder">Loading residents...</div>';
        let url = `${basePath}/analytics/filtered-residents?chart_id=${chartId}`;
        if (start && end) { url += `&start_date=${start}&end_date=${end}`; }
        try {
            const response = await fetch(url);
            const result = await response.json();
            currentResidentList = (result.status === 'success' && result.residents) ? result.residents : [];
            renderResidentList();
        } catch (error) {
            console.error("Error fetching KPI residents:", error);
            currentResidentList = [];
            renderResidentList();
        }
    };

    const redrawModalChart = async (start, end) => {
        let dataUrl = `${basePath}/charts/data?chart_id=${chartId}`;
        if (start && end) { dataUrl += `&start_date=${start}&end_date=${end}`; }
        try {
            const response = await fetch(dataUrl);
            const result = await response.json();
            if (result.status === 'success') {
                const chartObj = drawChart('DetailContent', result.type, result.data);
                if (chartObj && chartObj.chart && groupByColumn) {
                    google.visualization.events.addListener(chartObj.chart, 'select', () => {
                        const selection = chartObj.chart.getSelection();
                        if (selection.length > 0) {
                            const { row } = selection[0];
                            if (row !== null) {
                                const category = chartObj.dataTable.getValue(row, 0);
                                showFilteredResidents(groupByColumn, category, startDateInput.value, endDateInput.value);
                                chartObj.chart.setSelection([]);
                            }
                        }
                    });
                }
            }
        } catch (error) { console.error("Failed to redraw modal chart:", error); }
    };

    const handleFilter = () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        if (startDate && endDate) {
            localStorage.setItem(`chartDateRange_${chartId}`, JSON.stringify({ start: startDate, end: endDate }));
            chartType === 'KPI' ? fetchAndDisplayKpiResidents(startDate, endDate) : redrawModalChart(startDate, endDate);
            redrawDashboardChart(chartId, startDate, endDate); 
        } else {
            alert('Please select both a start and end date.');
        }
    };
    
    const handleClear = () => {
        startDateInput.value = '';
        endDateInput.value = '';
        localStorage.removeItem(`chartDateRange_${chartId}`);
        chartType === 'KPI' ? fetchAndDisplayKpiResidents(null, null) : redrawModalChart(null, null);
        redrawDashboardChart(chartId, null, null); 
    };

    const handleHide = () => {
        const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || [];
        const updatedVisibleIds = visibleChartIds.filter(id => id !== chartId);
        localStorage.setItem('visibleChartIds', JSON.stringify(updatedVisibleIds));
        
        removeChartFromDashboard(chartId);
        modal.style.display = 'none';
    };

    const handleDelete = async () => {
        if (confirm('Are you sure you want to permanently delete this chart? This action cannot be undone.')) {
            try {
                const formData = new FormData();
                formData.append('chart_id', chartId);

                const response = await fetch(`${basePath}/charts/delete`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    handleHide(); // Hide it first, which also closes the modal
                } else {
                    alert('Error: ' + (result.message || 'Could not delete the chart.'));
                }
            } catch (error) {
                console.error("Deletion failed:", error);
                alert('An unexpected error occurred.');
            }
        }
    };

    if (chartType === 'KPI') {
        chartDetailContent.style.display = 'none';
        modalGrid.style.gridTemplateColumns = '1fr';
        residentListContainer.innerHTML = '';
        fetchAndDisplayKpiResidents(startDateInput.value, endDateInput.value);
    } else {
        chartDetailContent.style.display = 'block';
        modalGrid.style.gridTemplateColumns = '';
        residentListContainer.innerHTML = '<div class="list-placeholder">Click on a chart segment to see the list of residents.</div>';
        redrawModalChart(startDateInput.value, endDateInput.value);
    }

    filterBtn.onclick = handleFilter;
    clearBtn.onclick = handleClear;
    editBtn.onclick = () => openChartBuilderForEdit(chartId);
    hideBtn.onclick = handleHide;
    deleteBtn.onclick = handleDelete;
    modal.style.display = 'flex';
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
        showChartDetailModal(chartId);
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
    
    const infoBtn = event.target.closest('.resident-info-btn');
    if (infoBtn) {
        openResidentDetailsModal(infoBtn.dataset.id);
    }
});

async function openChartBuilderForEdit(chartId) {
    try {
        const response = await fetch(`${basePath}/charts/get?id=${chartId}`);
        const result = await response.json();

        if (result.status !== 'success') {
            alert('Error: Could not fetch chart data.');
            return;
        }
        const chartData = result.chart;

        const chartBuilderModal = document.getElementById('chartBuilderModal');
        const form = document.getElementById('chartBuilderForm');
        const filterContainer = document.getElementById('filterContainer');

        form.reset();
        filterContainer.innerHTML = '';
        form.querySelector('#chartTitle').value = chartData.title;
        form.querySelector('#chartType').value = chartData.chart_type;
        form.querySelector('#aggregateFunction').value = chartData.aggregate_function;
        form.querySelector('#groupByColumn').value = chartData.group_by_column;
        
        let chartIdInput = form.querySelector('#chart_id');
        if (!chartIdInput) {
            chartIdInput = document.createElement('input');
            chartIdInput.type = 'hidden';
            chartIdInput.id = 'chart_id';
            chartIdInput.name = 'chart_id';
            form.appendChild(chartIdInput);
        }
        chartIdInput.value = chartId;
        
        document.getElementById('manageChartsModal').style.display = 'none';
        document.getElementById('chartDetailModal').style.display = 'none';
        chartBuilderModal.style.display = 'block';

    } catch (error) {
        console.error('Failed to open chart for editing:', error);
        alert('An unexpected error occurred.');
    }
}

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

window.updateDashboardGrid = function(allCharts, visibleChartIds) {
    const currentWidgets = grid.engine.nodes;
    const currentChartIds = currentWidgets.map(n => n.id);

    // Find charts to remove
    const chartsToRemove = currentWidgets.filter(widget => !visibleChartIds.includes(widget.id));
    chartsToRemove.forEach(widget => grid.removeWidget(widget.el));

    // Find chart IDs to add
    const chartsToAddIds = visibleChartIds.filter(id => !currentChartIds.includes(id));
    chartsToAddIds.forEach(chartId => {
        const chartDef = allCharts.find(c => c.id.toString() === chartId);
        if (chartDef && window.addChartToDashboard) {
            window.addChartToDashboard(chartDef);
        }
    });
};

window.removeChartFromDashboard = function(chartId) {
    const widgetEl = document.querySelector(`.grid-stack-item[gs-id='${chartId}']`);
    if (widgetEl) {
        grid.removeWidget(widgetEl);
        if (grid.opts.float) {
            grid.compact();
        }
    }
};

window.redrawChartInPlace = function(chartId, updatedChartDef) {
    const widget = grid.engine.nodes.find(n => n.id === chartId.toString());
    if (widget) {
        // Update title
        const titleEl = widget.el.querySelector('.chart-title');
        if (titleEl) {
            titleEl.textContent = updatedChartDef.title;
        }
        // Update dataset for group-by
        widget.el.querySelector('.grid-stack-item-content').dataset.groupBy = updatedChartDef.group_by_column || '';
        
        // Redraw chart with potentially new data
        redrawDashboardChart(chartId);
        
        const detailModal = document.getElementById('chartDetailModal');
        if (detailModal.style.display === 'flex' && detailModal.dataset.chartId === chartId.toString()) {
            showChartDetailModal(chartId); 
        }
    }
};

const reportModal = document.getElementById('report-modal');
const generateReportBtn = document.getElementById('generate-report-btn');
if (reportModal && generateReportBtn) {
    const closeBtn = reportModal.querySelector('.close-btn');
    generateReportBtn.addEventListener('click', () => reportModal.style.display = 'flex');
    if (closeBtn) closeBtn.addEventListener('click', () => reportModal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target === reportModal) reportModal.style.display = 'none';
    });
}