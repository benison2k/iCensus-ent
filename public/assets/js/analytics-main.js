import { initializeGrid, loadLayout, saveLayout, resetLayout, addWidgetToGrid } from './gridManager.js';
import { openChartBuilder, setupBuilderEventListeners, fetchAndShowCharts } from './builderManager.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Setup listeners for the main page buttons
    document.getElementById('add-widget-btn').addEventListener('click', fetchAndShowCharts);
    document.getElementById('create-new-chart-btn').addEventListener('click', () => openChartBuilder());
    
    // 2. Setup listeners for the chart builder form and modals
    setupBuilderEventListeners();

    // 3. Load Google Charts and then initialize the dashboard grid
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(() => {
        initializeGrid();
        loadLayout(); // This will fetch the user's layout and trigger chart drawing.
        
        // 4. Attach event listeners to the layout control buttons
        document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
        document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
    });

    // 5. Listen for the custom event to add a new widget to the grid from the library
    document.addEventListener('addChartToGrid', (e) => {
        const { chartId, chartTitle, chartType } = e.detail;
        addWidgetToGrid(chartId, chartTitle, chartType);
    });
});
