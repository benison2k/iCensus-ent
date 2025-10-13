document.addEventListener('DOMContentLoaded', () => {
    const manageModal = document.getElementById('manageChartsModal');
    if (!manageModal) return;

    const openBtn = document.getElementById('manageChartsBtn');
    const closeBtn = manageModal.querySelector('.close-btn');
    const saveSelectionBtn = document.getElementById('saveChartSelectionBtn');
    const chartList = document.getElementById('chartSelectionList');
    const basePath = '/iCensus-ent/public';

    let allCharts = []; // To store all available charts

    // --- Modal Controls ---
    if (openBtn) {
        openBtn.addEventListener('click', async () => {
            await populateChartList();
            manageModal.style.display = 'block';
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', () => (manageModal.style.display = 'none'));
    }
    window.addEventListener('click', (e) => {
        if (e.target === manageModal) manageModal.style.display = 'none';
    });

    /**
     * Fetches all user-created charts and populates the checkbox list.
     */
    async function populateChartList() {
        chartList.innerHTML = '<li>Loading...</li>';

        try {
            // 1. Fetch all charts created by the user
            const response = await fetch(`${basePath}/charts/user-charts`);
            const result = await response.json();
            if (result.status !== 'success' || !result.charts) {
                chartList.innerHTML = '<li>Could not load charts.</li>';
                return;
            }
            allCharts = result.charts;

            // 2. Get the currently visible charts from localStorage
            const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || null;
            
            chartList.innerHTML = ''; // Clear loading message

            // 3. Create a checkbox for each chart
            allCharts.forEach(chart => {
                const listItem = document.createElement('li');
                listItem.classList.add('chart-list-item');

                // If no preference is saved, show all charts by default. Otherwise, check the saved ones.
                const isChecked = visibleChartIds === null || visibleChartIds.includes(chart.id.toString());

                listItem.innerHTML = `
                    <input type="checkbox" id="chart-toggle-${chart.id}" value="${chart.id}" ${isChecked ? 'checked' : ''}>
                    <label for="chart-toggle-${chart.id}">${chart.title}</label>
                `;
                chartList.appendChild(listItem);
            });

        } catch (error) {
            console.error("Failed to populate chart list:", error);
            chartList.innerHTML = '<li>Error loading charts.</li>';
        }
    }

    /**
     * Saves the user's selection to localStorage and reloads the page.
     */
    if (saveSelectionBtn) {
        saveSelectionBtn.addEventListener('click', () => {
            const selectedIds = [];
            manageModal.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });

            localStorage.setItem('visibleChartIds', JSON.stringify(selectedIds));
            
            alert('Dashboard updated! The page will now reload.');
            location.reload();
        });
    }
});
