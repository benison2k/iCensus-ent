// public/assets/js/manage_chart.js

document.addEventListener('DOMContentLoaded', () => {
    const manageModal = document.getElementById('manageChartsModal');
    if (!manageModal) return;

    const openBtn = document.getElementById('manageChartsBtn');
    const closeBtn = manageModal.querySelector('.close-btn');
    const saveSelectionBtn = document.getElementById('saveChartSelectionBtn');
    const chartList = document.getElementById('chartSelectionList');
    const basePath = '/iCensus-ent/public';
    let allCharts = [];

    // --- Modal Controls ---
    if (openBtn) {
        openBtn.addEventListener('click', async () => {
            await populateChartList();
            manageModal.style.display = 'block';
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', () => manageModal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === manageModal) manageModal.style.display = 'none'; });

    async function populateChartList() {
        chartList.innerHTML = '<li>Loading...</li>';
        try {
            const response = await fetch(`${basePath}/charts/user-charts`);
            const result = await response.json();
            if (result.status !== 'success' || !result.charts) {
                chartList.innerHTML = '<li>Could not load charts.</li>';
                return;
            }
            allCharts = result.charts;
            const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || null;
            chartList.innerHTML = '';

            allCharts.forEach(chart => {
                const isChecked = visibleChartIds === null || visibleChartIds.includes(chart.id.toString());
                const listItem = document.createElement('li');
                listItem.classList.add('chart-list-item');
                listItem.innerHTML = `
                    <input type="checkbox" id="chart-toggle-${chart.id}" value="${chart.id}" ${isChecked ? 'checked' : ''}>
                    <label for="chart-toggle-${chart.id}">${chart.title}</label>
                    <div class="chart-item-actions">
                        <button class="edit-chart-btn" data-id="${chart.id}" title="Edit Chart"><span class="material-icons">edit</span></button>
                        <button class="delete-chart-btn" data-id="${chart.id}" title="Delete Chart"><span class="material-icons" style="color:red;">delete</span></button>
                    </div>
                `;
                chartList.appendChild(listItem);
            });

        } catch (error) {
            console.error("Failed to populate chart list:", error);
            chartList.innerHTML = '<li>Error loading charts.</li>';
        }
    }

    if (saveSelectionBtn) {
        saveSelectionBtn.addEventListener('click', () => {
            const selectedIds = Array.from(manageModal.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
            localStorage.setItem('visibleChartIds', JSON.stringify(selectedIds));
            alert('Dashboard updated! The page will now reload.');
            location.reload();
        });
    }

    // --- NEW: Event delegation for edit and delete buttons ---
    chartList.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-chart-btn');
        if (editBtn) {
            const chartId = editBtn.dataset.id;
            openChartBuilderForEdit(chartId);
        }
        // Add delete logic here in the future
    });
});

/**
 * NEW: Function to open the chart builder in edit mode.
 */
async function openChartBuilderForEdit(chartId) {
    const basePath = '/iCensus-ent/public';
    try {
        const response = await fetch(`${basePath}/charts/get?id=${chartId}`);
        const result = await response.json();

        if (result.status !== 'success') {
            alert('Error: Could not fetch chart data.');
            return;
        }
        const chartData = result.chart;

        // Get modal elements
        const chartBuilderModal = document.getElementById('chartBuilderModal');
        const form = document.getElementById('chartBuilderForm');
        const filterContainer = document.getElementById('filterContainer');

        // Reset form and set values
        form.reset();
        filterContainer.innerHTML = '';
        form.querySelector('#chartTitle').value = chartData.title;
        form.querySelector('#chartType').value = chartData.chart_type;
        form.querySelector('#aggregateFunction').value = chartData.aggregate_function;
        form.querySelector('#groupByColumn').value = chartData.group_by_column;
        
        // Add a hidden input for the chart ID
        let chartIdInput = form.querySelector('#chart_id');
        if (!chartIdInput) {
            chartIdInput = document.createElement('input');
            chartIdInput.type = 'hidden';
            chartIdInput.id = 'chart_id';
            chartIdInput.name = 'chart_id';
            form.appendChild(chartIdInput);
        }
        chartIdInput.value = chartId;
        
        // Hide the "Manage Charts" modal and show the builder
        document.getElementById('manageChartsModal').style.display = 'none';
        chartBuilderModal.style.display = 'block';

    } catch (error) {
        console.error('Failed to open chart for editing:', error);
        alert('An unexpected error occurred.');
    }
}