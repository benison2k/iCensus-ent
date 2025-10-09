// public/assets/js/analytics1.js

import { initializeModals } from './modalManager.js';
import { initializeGrid, loadLayout, saveLayout, resetLayout } from './gridManager.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Set up all modal listeners from the start.
    initializeModals();

    // 2. Load the necessary Google Charts packages.
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });

    // 3. Once Google Charts is ready, initialize the dashboard.
    google.charts.setOnLoadCallback(() => {
        initializeGrid();
        loadLayout(); // This will fetch layout and trigger chart drawing.
        
        // 4. Attach event listeners to the main layout and filter control buttons.
        document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
        document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
        
        // --- NEW: Event listeners for date filters ---
        document.getElementById('filter-btn').addEventListener('click', loadLayout); // Re-use loadLayout to refresh charts
        document.getElementById('clear-filter-btn').addEventListener('click', () => {
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            loadLayout(); // Reload with empty dates
        });
    });
});