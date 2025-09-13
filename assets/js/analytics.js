document.addEventListener('DOMContentLoaded', () => {
    fetch('../core/analytics_process.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;
                populateStatCards(data);
                renderCharts(data);
            } else {
                console.error('Failed to fetch analytics data:', result.message);
            }
        })
        .catch(error => console.error('Error fetching analytics data:', error));

    function populateStatCards(data) {
        document.getElementById('totalResidents').textContent = data.totalResidents;
        document.getElementById('maleCount').textContent = data.maleCount;
        document.getElementById('femaleCount').textContent = data.femaleCount;
        document.getElementById('seniorCount').textContent = data.seniorCount;
    }

    function renderCharts(data) {
        const isDarkMode = document.body.classList.contains('dark-mode');
        const textColor = isDarkMode ? 'white' : 'black';

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
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
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
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor }
                    },
                    x: {
                        ticks: { color: textColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
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
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor }
                    },
                    x: {
                        ticks: { color: textColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
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
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor }
                    },
                    x: {
                        ticks: { color: textColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
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
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
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
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    }
});