document.addEventListener('DOMContentLoaded', () => {
    const analyticsGrid = document.getElementById('analyticsGrid');
    const saveLayoutBtn = document.getElementById('saveLayoutBtn');
    const resetLayoutBtn = document.getElementById('resetLayoutBtn');

    let initialLayoutOrder = [];

    fetch('../core/analytics_process.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;
                const layout = result.layout;
                const sizes = result.sizes;
                
                // Store the initial order of elements in the DOM
                initialLayoutOrder = Array.from(analyticsGrid.children).map(item => item.dataset.id);

                if (layout) {
                    reorderGrid(layout);
                }
                
                if(sizes) {
                    applyChartSizes(sizes);
                }

                populateStatCards(data);
                renderCharts(data);
                initializeSortable();
                setupChartSettings();
            } else {
                console.error('Failed to fetch analytics data:', result.message);
            }
        })
        .catch(error => console.error('Error fetching analytics data:', error));

    function reorderGrid(layout) {
        const items = Array.from(analyticsGrid.children);
        const itemMap = new Map(items.map(item => [item.dataset.id, item]));
        layout.forEach(id => {
            const item = itemMap.get(id);
            if (item) {
                analyticsGrid.appendChild(item);
            }
        });
    }

    function applyChartSizes(sizes) {
        for (const chartId in sizes) {
            const card = analyticsGrid.querySelector(`.chart-card[data-id="${chartId}"]`);
            if (card) {
                card.classList.remove('size-1', 'size-2', 'size-3');
                card.classList.add(`size-${sizes[chartId]}`);
            }
        }
    }

    function populateStatCards(data) {
        document.getElementById('totalResidents').textContent = data.totalResidents;
        document.getElementById('totalHouseholds').textContent = data.totalHouseholds;
        document.getElementById('maleCount').textContent = data.maleCount;
        document.getElementById('femaleCount').textContent = data.femaleCount;
        document.getElementById('seniorCount').textContent = data.seniorCount;
    }

    function renderCharts(data) {
        const isDarkMode = document.body.classList.contains('dark-mode');
        const textColor = isDarkMode ? 'white' : 'black';
        
        // Household Size Chart
        new Chart(document.getElementById('householdSizeChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.householdSizeDistribution),
                datasets: [{
                    label: 'Number of Households',
                    data: Object.values(data.householdSizeDistribution),
                    backgroundColor: '#9b59b6',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 } },
                    x: { ticks: { color: textColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });

        // Gender Chart
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.genderDistribution),
                datasets: [{
                    data: Object.values(data.genderDistribution),
                    backgroundColor: ['#3498db', '#e74c3c'],
                }]
            },
            options: { responsive: true, plugins: { legend: { labels: { color: textColor } } } }
        });

        // Age Chart
        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.ageDistribution),
                datasets: [{
                    label: 'Population',
                    data: Object.values(data.ageDistribution),
                    backgroundColor: '#2ecc71',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor } },
                    x: { ticks: { color: textColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });

        // Purok Chart
        new Chart(document.getElementById('purokChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.purokDistribution),
                datasets: [{
                    label: 'Population',
                    data: Object.values(data.purokDistribution),
                    backgroundColor: '#f1c40f',
                }]
            },
             options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor } },
                    x: { ticks: { color: textColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });
        
        // Barangay Chart
        new Chart(document.getElementById('barangayChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.barangayDistribution),
                datasets: [{
                    label: 'Population',
                    data: Object.values(data.barangayDistribution),
                    backgroundColor: '#e67e22',
                }]
            },
             options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor } },
                    x: { ticks: { color: textColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });
        
        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.statusDistribution),
                datasets: [{
                    data: Object.values(data.statusDistribution),
                    backgroundColor: ['#1abc9c', '#9b59b6', '#34495e', '#e74c3c'],
                }]
            },
            options: { responsive: true, plugins: { legend: { labels: { color: textColor } } } }
        });

        // Civil Status Chart
        new Chart(document.getElementById('civilStatusChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.civilStatusDistribution),
                datasets: [{
                    data: Object.values(data.civilStatusDistribution),
                    backgroundColor: ['#2980b9', '#27ae60', '#c0392b', '#f39c12', '#8e44ad'],
                }]
            },
            options: { responsive: true, plugins: { legend: { labels: { color: textColor } } } }
        });

        // Blood Type Chart
        new Chart(document.getElementById('bloodTypeChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.bloodTypeDistribution),
                datasets: [{
                    label: 'Count',
                    data: Object.values(data.bloodTypeDistribution),
                    backgroundColor: '#c0392b',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor } },
                    x: { ticks: { color: textColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });

        // Nationality Chart
        new Chart(document.getElementById('nationalityChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.nationalityDistribution),
                datasets: [{
                    data: Object.values(data.nationalityDistribution),
                    backgroundColor: ['#34495e', '#16a085', '#d35400', '#8e44ad'],
                }]
            },
            options: { responsive: true, plugins: { legend: { labels: { color: textColor } } } }
        });
    }

    function initializeSortable() {
        new Sortable(analyticsGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    }
    
    function setupChartSettings() {
        document.querySelectorAll('.chart-settings .size-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const card = e.target.closest('.chart-card');
                const chartId = card.dataset.id;
                const size = e.target.dataset.size;

                card.classList.remove('size-1', 'size-2', 'size-3');
                card.classList.add(`size-${size}`);

                saveChartSize(chartId, size);
            });
        });
    }

    function saveChartSize(chartId, size) {
        const formData = new URLSearchParams();
        formData.append('chart_id', chartId);
        formData.append('size', size);

        fetch('../core/save_chart_size.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.status !== 'success') {
                alert('Failed to save chart size: ' + result.message);
            }
        })
        .catch(err => console.error('Error saving chart size:', err));
    }

    saveLayoutBtn.addEventListener('click', () => {
        const layout = Array.from(analyticsGrid.children).map(item => item.dataset.id);
        const formData = new URLSearchParams();
        formData.append('layout', JSON.stringify(layout));

        fetch('../core/save_layout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert('Layout saved successfully!');
            } else {
                alert('Failed to save layout: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error saving layout:', error);
            alert('An error occurred while saving the layout.');
        });
    });

    resetLayoutBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to reset your layout and chart sizes to default?')) {
            fetch('../core/reset_layout.php', { method: 'POST' })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success') {
                    // Revert to initial DOM order without a full reload
                    reorderGrid(initialLayoutOrder);
                    
                    // Reset sizes visually
                    document.querySelectorAll('.chart-card').forEach(card => {
                        card.classList.remove('size-2', 'size-3');
                        card.classList.add('size-1');
                    });

                    alert('Layout reset successfully.');
                } else {
                    alert('Failed to reset layout: ' + result.message);
                }
            })
            .catch(err => console.error('Error resetting layout:', err));
        }
    });
});