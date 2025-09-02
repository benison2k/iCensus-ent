<?php
session_start();
// Include your core files
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Auth.php';

// Check for authentication
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database($config);
$user = $_SESSION['user'];
$userId = $user['id'];

// Fetch all residents from the database
$pdo = $db->getPdo();
$stmt = $pdo->query("SELECT * FROM residents");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch saved dashboard config
$dashboardConfig = [];
$stmt = $pdo->prepare("SELECT dashboard_config FROM user_dashboards WHERE user_id = ?");
$stmt->execute([$userId]);
$savedConfig = $stmt->fetchColumn();
if ($savedConfig) {
    $dashboardConfig = json_decode($savedConfig, true);
}

// Process the fetched data into chart-friendly formats
function processChartData($residents) {
    $ageGroups = ['0-10' => 0, '11-20' => 0, '21-30' => 0, '31-40' => 0, '41-50' => 0, '51-60' => 0, '60+' => 0];
    $genderDistribution = ['Male' => 0, 'Female' => 0, 'Others' => 0];
    $householdSize = ['1-2' => 0, '3-4' => 0, '5-6' => 0, '7+' => 0];
    $purokDistribution = [];
    $barangayDistribution = [];

    foreach ($residents as $resident) {
        // Calculate age and assign to age group
        $dob = new DateTime($resident['dob']);
        $now = new DateTime();
        $age = $dob->diff($now)->y;
        if ($age <= 10) $ageGroups['0-10']++;
        else if ($age <= 20) $ageGroups['11-20']++;
        else if ($age <= 30) $ageGroups['21-30']++;
        else if ($age <= 40) $ageGroups['31-40']++;
        else if ($age <= 50) $ageGroups['41-50']++;
        else if ($age <= 60) $ageGroups['51-60']++;
        else $ageGroups['60+']++;

        // Gender distribution
        $gender = ucwords(strtolower($resident['gender']));
        if (array_key_exists($gender, $genderDistribution)) {
            $genderDistribution[$gender]++;
        } else {
            $genderDistribution['Others']++;
        }

        // Household size
        $householdSizeValue = $resident['household_size'] ?? 1;
        if ($householdSizeValue <= 2) $householdSize['1-2']++;
        else if ($householdSizeValue <= 4) $householdSize['3-4']++;
        else if ($householdSizeValue <= 6) $householdSize['5-6']++;
        else $householdSize['7+']++;
        
        // Purok distribution
        if (!isset($purokDistribution[$resident['purok']])) {
            $purokDistribution[$resident['purok']] = 0;
        }
        $purokDistribution[$resident['purok']]++;

        // Barangay distribution
        if (!isset($barangayDistribution[$resident['barangay']])) {
            $barangayDistribution[$resident['barangay']] = 0;
        }
        $barangayDistribution[$resident['barangay']]++;
    }

    return [
        'ageDistribution' => ['labels' => array_keys($ageGroups), 'data' => array_values($ageGroups), 'defaultTitle' => 'Population Distribution by Age Group'],
        'genderDistribution' => ['labels' => array_keys($genderDistribution), 'data' => array_values($genderDistribution), 'defaultTitle' => 'Population Distribution by Gender'],
        'householdSize' => ['labels' => array_keys($householdSize), 'data' => array_values($householdSize), 'defaultTitle' => 'Household Size Distribution'],
        'purokDistribution' => ['labels' => array_keys($purokDistribution), 'data' => array_values($purokDistribution), 'defaultTitle' => 'Population by Purok'],
        'barangayDistribution' => ['labels' => array_keys($barangayDistribution), 'data' => array_values($barangayDistribution), 'defaultTitle' => 'Population by Barangay']
    ];
}

$chartData = processChartData($residents);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Analytics</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .drag-handle {
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            color: #9ca3af;
        }
    </style>
</head>
<body class="<?= htmlspecialchars($user['theme'] ?? 'light') === 'dark' ? 'dark-mode' : ''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Barangay Data Analytics</h2></div>

    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl p-8 mt-8">
        
        <header class="mb-8 border-b-2 border-gray-200 pb-4">
            <p class="mt-1 text-gray-600">Visualize and analyze census data for your barangay.</p>
        </header>

        <!-- Main Controls to add new charts -->
        <section class="flex flex-wrap justify-end gap-4 mb-8 p-6 bg-gray-50 rounded-xl shadow-inner">
            <button id="addNewChartBtn" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-md shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                Add New Chart
            </button>
        </section>

        <!-- Chart Dashboard Grid -->
        <div id="chartsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // If saved config exists, render the charts
            if (!empty($dashboardConfig)):
                foreach ($dashboardConfig as $id => $config):
                    $chartId = htmlspecialchars($id);
                    $cardClasses = '';
                    if ($config['chartSize'] === 'large') {
                        $cardClasses = 'md:col-span-3 lg:col-span-3';
                    } else if ($config['chartSize'] === 'medium') {
                        $cardClasses = 'md:col-span-2 lg:col-span-2';
                    } else {
                        $cardClasses = 'md:col-span-1 lg:col-span-1';
                    }
                    ?>
                    <div id="<?= $chartId ?>" class="chart-card-container <?= $cardClasses; ?>">
                        <?php 
                            // Pass the chartId and config to the component
                            $chartId = $id;
                            $config = $config;
                            include __DIR__ . '/../components/chart_card.php'; 
                        ?>
                    </div>
                <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script>
        const chartsContainer = document.getElementById('chartsContainer');
        const addNewChartBtn = document.getElementById('addNewChartBtn');
        const chartInstances = {};
        
        // Dynamically load data from PHP
        const dataSets = <?php echo json_encode($chartData); ?>;
        let dashboardConfig = <?php echo json_encode($dashboardConfig); ?>;
        
        // Function to save dashboard state to the backend
        const saveDashboard = () => {
            fetch('../core/save_dashboard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dashboard_config: dashboardConfig })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to save dashboard:', data.message);
                }
            })
            .catch(error => console.error('Error saving dashboard:', error));
        };

        const generateChart = (chartId) => {
            const container = document.getElementById(chartId);
            if (!container) {
                console.error(`Container with ID ${chartId} not found.`);
                return;
            }
            const chartCard = container.querySelector('.chart-card');
            const chartCanvasContainer = chartCard.querySelector('.chart-canvas-container');
            const chartCanvas = chartCard.querySelector('canvas');
            const chartTypeSelect = chartCard.querySelector('.chart-type-select');
            const dataTypeSelect = chartCard.querySelector('.data-type-select');
            const chartTitleInput = chartCard.querySelector('.chart-title-input');
            const chartSizeSelect = chartCard.querySelector('.chart-size-select');
            
            // Destroy old chart instance if it exists
            if (chartInstances[chartId]) {
                chartInstances[chartId].destroy();
            }

            const selectedChartType = chartTypeSelect.value;
            const selectedDataType = dataTypeSelect.value;
            const selectedSize = chartSizeSelect.value;
            const data = dataSets[selectedDataType];
            const title = chartTitleInput.value || data.defaultTitle;
            
            // Define canvas height classes for different sizes
            const sizeClasses = {
                small: 'h-64',
                medium: 'h-96',
                large: 'h-[500px]'
            };

            // Remove all existing size classes before applying the new one
            Object.values(sizeClasses).forEach(cls => chartCanvasContainer.classList.remove(cls));
            chartCanvasContainer.classList.add(sizeClasses[selectedSize]);
            
            const backgroundColors = data.labels.map((_, index) => `hsl(${index * 50}, 70%, 50%)`);
            const borderColors = backgroundColors.map(color => color.replace('50%', '30%'));
            
            const chartData = {
                labels: data.labels,
                datasets: [{
                    label: title,
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: selectedChartType === 'pie' ? 0 : 1
                }]
            };

            const chartConfig = {
                type: selectedChartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            display: selectedChartType !== 'pie'
                        },
                        x: {
                            display: selectedChartType !== 'pie'
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: title,
                            font: {
                                size: 18,
                                weight: 'bold'
                            },
                        },
                        legend: {
                            display: selectedChartType === 'pie',
                            position: 'bottom',
                        },
                    }
                }
            };
            
            // Create a new chart instance and store it
            chartInstances[chartId] = new Chart(chartCanvas, chartConfig);

            // Update dashboard config in memory
            dashboardConfig[chartId] = {
                chartType: selectedChartType,
                dataType: selectedDataType,
                chartSize: selectedSize,
                chartTitle: chartTitleInput.value
            };
            
            saveDashboard();
        };

        const updateCardSize = (container, newSizeClass) => {
            // Remove all previous size classes
            ['md:col-span-1', 'md:col-span-2', 'md:col-span-3', 'lg:col-span-1', 'lg:col-span-2', 'lg:col-span-3'].forEach(cls => {
                container.classList.remove(cls);
            });
            // Add the new size class based on the selected size
            if (newSizeClass === 'small') {
                container.classList.add('md:col-span-1', 'lg:col-span-1');
            } else if (newSizeClass === 'medium') {
                container.classList.add('md:col-span-2', 'lg:col-span-2');
            } else if (newSizeClass === 'large') {
                container.classList.add('md:col-span-3', 'lg:col-span-3');
            }
            saveDashboard();
        };
        
        const addNewChart = () => {
            const newChartId = `chart-${Date.now()}`;
            const chartHtml = `
                <div id="${newChartId}" class="chart-card-container md:col-span-3 lg:col-span-3">
                    <div class="chart-card card settings-card bg-white p-6 rounded-2xl shadow-inner relative">
                        <div class="flex justify-between items-center mb-4">
                            <span class="material-icons drag-handle">drag_indicator</span>
                            <button class="remove-btn absolute top-4 right-4 text-red-500 hover:text-red-700" title="Remove Chart">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mb-4">
                            <div class="flex-1 min-w-[150px]">
                                <label for="chartType-${newChartId}" class="block text-sm font-medium text-gray-700">Chart Type</label>
                                <select id="chartType-${newChartId}" class="chart-type-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="bar">Bar Chart</option>
                                    <option value="pie">Pie Chart</option>
                                    <option value="line">Line Chart</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label for="dataType-${newChartId}" class="block text-sm font-medium text-gray-700">Data Set</label>
                                <select id="dataType-${newChartId}" class="data-type-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="ageDistribution">Population by Age Group</option>
                                    <option value="genderDistribution">Population by Gender</option>
                                    <option value="householdSize">Household Size Distribution</option>
                                    <option value="purokDistribution">Population by Purok</option>
                                    <option value="barangayDistribution">Population by Barangay</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[120px]">
                                <label for="chartSize-${newChartId}" class="block text-sm font-medium text-gray-700">Card Size</label>
                                <select id="chartSize-${newChartId}" class="chart-size-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="large">Large</option>
                                    <option value="medium">Medium</option>
                                    <option value="small">Small</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <label for="chartTitle-${newChartId}" class="block text-sm font-medium text-gray-700">Chart Title</label>
                                <input type="text" id="chartTitle-${newChartId}" class="chart-title-input mt-1 block w-full rounded-md border-gray-300 shadow-sm px-2 py-1" placeholder="Optional: Enter a custom title" value="">
                            </div>
                            <button class="generate-btn flex-1 mt-6 px-4 py-2 bg-indigo-600 text-white font-medium rounded-md shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out" data-chart-id="${newChartId}">
                                Generate Chart
                            </button>
                        </div>
                        <div class="relative w-full h-96 chart-canvas-container">
                            <canvas id="myChart-${newChartId}"></canvas>
                        </div>
                    </div>
                </div>
            `;
            chartsContainer.insertAdjacentHTML('beforeend', chartHtml);

            // Update dashboard config in memory with default values
            dashboardConfig[newChartId] = {
                chartType: 'bar',
                dataType: 'ageDistribution',
                chartSize: 'large',
                chartTitle: dataSets['ageDistribution'].defaultTitle
            };

            generateChart(newChartId);
        };
        
        // Event delegation for dynamically added buttons and selectors
        chartsContainer.addEventListener('click', (event) => {
            if (event.target.closest('.generate-btn')) {
                const btn = event.target.closest('.generate-btn');
                const chartId = btn.dataset.chartId;
                generateChart(chartId);
            } else if (event.target.closest('.remove-btn')) {
                const btn = event.target.closest('.remove-btn');
                const chartContainer = btn.closest('.chart-card-container');
                const chartId = chartContainer.id;
                delete dashboardConfig[chartId];
                if (chartInstances[chartId]) {
                  chartInstances[chartId].destroy();
                }
                chartContainer.remove();
                saveDashboard();
            }
        });
        
        chartsContainer.addEventListener('change', (event) => {
            const target = event.target;
            const chartCard = target.closest('.chart-card');
            if (!chartCard) return;

            const chartId = chartCard.parentElement.id;
            const container = document.getElementById(chartId);

            if (target.classList.contains('chart-size-select')) {
                const newSize = target.value;
                updateCardSize(container, newSize);
                dashboardConfig[chartId].chartSize = newSize;
                saveDashboard();
            } else if (target.classList.contains('chart-type-select')) {
                const newType = target.value;
                dashboardConfig[chartId].chartType = newType;
                generateChart(chartId); // Regenerate chart with new type
                saveDashboard();
            } else if (target.classList.contains('data-type-select')) {
                const newDataType = target.value;
                dashboardConfig[chartId].dataType = newDataType;
                generateChart(chartId); // Regenerate chart with new data
                saveDashboard();
            }
        });
        
        chartsContainer.addEventListener('input', (event) => {
            const target = event.target;
            const chartCard = target.closest('.chart-card');
            if (!chartCard) return;

            const chartId = chartCard.parentElement.id;
            
            if (target.classList.contains('chart-title-input')) {
                dashboardConfig[chartId].chartTitle = target.value;
                // Since the title is an input, we want to save on every change
                // Or you could debounce this if performance is an issue
                saveDashboard();
            }
        });
        
        addNewChartBtn.addEventListener('click', () => {
            addNewChart();
        });
        
        // Initialize Sortable.js
        const sortable = Sortable.create(chartsContainer, {
            animation: 150,
            ghostClass: 'bg-gray-200',
            handle: '.drag-handle',
            onEnd: function (evt) {
                // Re-save the order of the charts
                const newOrder = sortable.toArray();
                const newConfig = {};
                newOrder.forEach(id => {
                    if (dashboardConfig[id]) {
                        newConfig[id] = dashboardConfig[id];
                    }
                });
                dashboardConfig = newConfig;
                saveDashboard();
            },
        });

        // Initialize charts on page load from saved config
        document.addEventListener('DOMContentLoaded', () => {
            // Wait for the DOM to be fully loaded, especially the PHP includes
            setTimeout(() => {
                const chartCards = chartsContainer.querySelectorAll('.chart-card-container');
                if (chartCards.length > 0) {
                    chartCards.forEach(card => {
                        const chartId = card.id;
                        const config = dashboardConfig[chartId];
                        if (config) {
                            // Set the selects to match the saved config
                            const chartTypeSelect = card.querySelector('.chart-type-select');
                            const dataTypeSelect = card.querySelector('.data-type-select');
                            const chartSizeSelect = card.querySelector('.chart-size-select');
                            const chartTitleInput = card.querySelector('.chart-title-input');

                            chartTypeSelect.value = config.chartType;
                            dataTypeSelect.value = config.dataType;
                            chartSizeSelect.value = config.chartSize;
                            chartTitleInput.value = config.chartTitle;

                            generateChart(chartId);
                            updateCardSize(card, config.chartSize);
                        } else {
                            // If for some reason a card exists in DOM but not in config, remove it
                            card.remove();
                        }
                    });
                } else {
                    // If no saved config, add a default chart
                    addNewChart();
                }
            }, 100);
        });

    </script>
</body>
</html>
