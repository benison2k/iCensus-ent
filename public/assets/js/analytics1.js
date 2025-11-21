// /public/assets/js/analytics.js

import { initializeFilters, applyFilters } from './resident/filterManager.js';
import { renderCharts, initializeChartManager } from './chartManager.js';
import { initializeChartBuilder, initializeChartManagerModal } from './chart_builder.js';
// Ensure correct function import for the resident details modal from the primary modalManager
import { initializeModals as initializeAnalyticsModals, openResidentDetailsModal } from './modalManager.js'; 
import { initializeGrid } from './gridManager.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. State Initialization
    // Fetch global state variables from hidden elements or global scope
    const layoutElement = document.getElementById('current-layout');
    const roleElement = document.getElementById('user-role');

    let state = {
        currentLayout: layoutElement ? JSON.parse(layoutElement.textContent) : {},
        userRole: roleElement ? roleElement.textContent : '',
        filterParams: {} 
    };

    // 2. Component Initialization
    initializeFilters(state);
    initializeChartManager(state);
    initializeChartBuilder(state);
    initializeChartManagerModal(state);
    initializeAnalyticsModals(state); 
    
    // 3. Initial Render
    initializeGrid(state);
    renderCharts(state);

    // 4. Initial Filter Application (Reading values on load)
    const filterInputs = document.querySelectorAll('#analytics-filter-form input, #analytics-filter-form select');
    filterInputs.forEach(input => {
        if (input.id && input.value) {
            state.filterParams[input.id] = input.value;
        }
    });
    
    // FIX: Add delegated event listener to handle clicks on resident detail buttons.
    // The resident list is inside the 'filtered-residents-modal' (ID).
    const filteredResidentsModal = document.getElementById('filtered-residents-modal');
    if (filteredResidentsModal) {
        filteredResidentsModal.addEventListener('click', (e) => {
            // Find the closest button element with the target class
            const viewButton = e.target.closest('.analytics-view-btn');
            
            if (viewButton) {
                e.preventDefault();
                const residentId = viewButton.dataset.id;
                
                // Call the function to open the modal and fetch data
                if (residentId && typeof openResidentDetailsModal === 'function') {
                    openResidentDetailsModal(residentId);
                } else {
                    console.error('Failed to open resident details modal: ID or function missing.', { residentId: residentId });
                }
            }
        });
    }

    // --- Other necessary event listeners for filtering, saving layout, etc. ---
    // ...
});