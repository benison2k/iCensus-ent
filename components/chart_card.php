<?php
// This component displays a single chart card.
// It requires a unique $chartId and optional $config array.
$config = $config ?? [];
$chartType = $config['chartType'] ?? 'bar';
$dataType = $config['dataType'] ?? 'ageDistribution';
$chartSize = $config['chartSize'] ?? 'large';
$chartTitle = $config['chartTitle'] ?? '';
?>

<div id="<?= htmlspecialchars($chartId); ?>" class="chart-card card settings-card bg-white p-6 rounded-2xl shadow-inner md:w-full">
    <button class="remove-btn absolute top-4 right-4 text-red-500 hover:text-red-700" title="Remove Chart">
        <span class="material-icons">close</span>
    </button>
    <div class="flex flex-wrap items-center gap-4 mb-4">
        <div class="flex-1 min-w-[150px]">
            <label for="chartType-<?= htmlspecialchars($chartId); ?>" class="block text-sm font-medium text-gray-700">Chart Type</label>
            <select id="chartType-<?= htmlspecialchars($chartId); ?>" class="chart-type-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="bar" <?= $chartType === 'bar' ? 'selected' : ''; ?>>Bar Chart</option>
                <option value="pie" <?= $chartType === 'pie' ? 'selected' : ''; ?>>Pie Chart</option>
                <option value="line" <?= $chartType === 'line' ? 'selected' : ''; ?>>Line Chart</option>
            </select>
        </div>
        <div class="flex-1 min-w-[150px]">
            <label for="dataType-<?= htmlspecialchars($chartId); ?>" class="block text-sm font-medium text-gray-700">Data Set</label>
            <select id="dataType-<?= htmlspecialchars($chartId); ?>" class="data-type-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="ageDistribution" <?= $dataType === 'ageDistribution' ? 'selected' : ''; ?>>Population by Age Group</option>
                <option value="genderDistribution" <?= $dataType === 'genderDistribution' ? 'selected' : ''; ?>>Population by Gender</option>
                <option value="householdSize" <?= $dataType === 'householdSize' ? 'selected' : ''; ?>>Household Size Distribution</option>
                <option value="purokDistribution" <?= $dataType === 'purokDistribution' ? 'selected' : ''; ?>>Population by Purok</option>
                <option value="barangayDistribution" <?= $dataType === 'barangayDistribution' ? 'selected' : ''; ?>>Population by Barangay</option>
            </select>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label for="chartSize-<?= htmlspecialchars($chartId); ?>" class="block text-sm font-medium text-gray-700">Card Size</label>
            <select id="chartSize-<?= htmlspecialchars($chartId); ?>" class="chart-size-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="large" <?= $chartSize === 'large' ? 'selected' : ''; ?>>Large</option>
                <option value="medium" <?= $chartSize === 'medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="small" <?= $chartSize === 'small' ? 'selected' : ''; ?>>Small</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label for="chartTitle-<?= htmlspecialchars($chartId); ?>" class="block text-sm font-medium text-gray-700">Chart Title</label>
            <input type="text" id="chartTitle-<?= htmlspecialchars($chartId); ?>" class="chart-title-input mt-1 block w-full rounded-md border-gray-300 shadow-sm px-2 py-1" placeholder="Optional: Enter a custom title" value="<?= htmlspecialchars($chartTitle); ?>">
        </div>
        <button class="generate-btn flex-1 mt-6 px-4 py-2 bg-indigo-600 text-white font-medium rounded-md shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out" data-chart-id="<?= htmlspecialchars($chartId); ?>">
            Generate Chart
        </button>
    </div>
    <div class="relative w-full h-96 chart-canvas-container">
        <canvas id="myChart-<?= htmlspecialchars($chartId); ?>"></canvas>
    </div>
</div>
