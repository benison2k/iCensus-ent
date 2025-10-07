// public/assets/js/analytics.js

import { initializeModals } from './modalManager.js';
import { initializeGrid, loadLayout, saveLayout, resetLayout } from './gridManager.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeModals();

    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(() => {
        initializeGrid();
        loadLayout();
        
        // Attach event listeners for grid controls
        document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
        document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
    });
});