document.addEventListener('DOMContentLoaded', () => {
    const addChartBtn = document.getElementById('add-chart-btn');
    const chartModal = document.getElementById('chartModal');
    const chartForm = document.getElementById('chartForm');
    const closeBtn = chartModal.querySelector('.close');
    const chartGrid = document.getElementById('chart-grid');
    let chartCounter = 0;
    const existingCharts = {}; // Store chart instances

    // Chart.js configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            title: { display: true }
        }
    };

    // Open modal
    addChartBtn.addEventListener('click', () => {
        chartModal.style.display = 'flex';
    });

    // Close modal
    closeBtn.addEventListener('click', () => {
        chartModal.style.display = 'none';
        chartForm.reset();
    });

    window.addEventListener('click', (e) => {
        if (e.target === chartModal) {
            chartModal.style.display = 'none';
            chartForm.reset();
        }
    });

    // Handle form submission to create a new chart
    chartForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(chartForm);
        const dataSource = formData.get('data-source');
        const chartType = formData.get('chart-type');
        const chartSize = formData.get('chart-size');

        if (!dataSource || !chartType) {
            alert('Please select a data source and chart type.');
            return;
        }

        try {
            const res = await fetch(`../core/analytics_data.php?data_source=${dataSource}`);
            const result = await res.json();

            if (result.status === 'success') {
                const chartId = `chart-${chartCounter++}`;
                const title = chartForm.querySelector(`option[value="${dataSource}"]`).textContent;

                // Create a new chart card
                const card = document.createElement('div');
                card.className = `card chart-card size-${chartSize}`;
                card.id = chartId;
                card.innerHTML = `<canvas id="canvas-${chartId}"></canvas>
                                  <button class="delete-chart-btn material-icons">delete</button>`;

                chartGrid.insertBefore(card, addChartBtn.parentNode);

                const ctx = document.getElementById(`canvas-${chartId}`).getContext('2d');
                const newChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: result.labels,
                        datasets: [{
                            label: title,
                            data: result.data,
                            backgroundColor: ['#0d6efd', '#ffc107', '#20c997', '#dc3545', '#6c757d'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        ...chartOptions,
                        plugins: {
                            ...chartOptions.plugins,
                            title: { display: true, text: title }
                        }
                    }
                });
                existingCharts[chartId] = newChart;

                // Attach delete button handler
                card.querySelector('.delete-chart-btn').addEventListener('click', () => {
                    card.remove();
                    newChart.destroy();
                    delete existingCharts[chartId];
                });

                chartModal.style.display = 'none';
                chartForm.reset();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred while fetching chart data.');
        }
    });
});