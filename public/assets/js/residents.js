// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/residents.js

import { initializeModal, openModalForEdit, openModalForAdd } from './resident/modalManager.js';
import { initializeForm } from './resident/formHandler.js';
import { initializeTable, renderTable } from './resident/tableManager.js';
import { initializeFilters, applyFilters } from './resident/filterManager.js';

document.addEventListener('DOMContentLoaded', () => {
    // --- STATE INITIALIZATION ---
    let state = {
        currentPage: 1,
        pageSize: 10,
        filteredResidents: [],
        currentSort: {
            column: 'last_name',
            order: 'asc'
        },
        allResidents: typeof allResidentsData !== 'undefined' ? allResidentsData : [],
        isPendingView: typeof isPendingView !== 'undefined' ? isPendingView : false
    };

    // --- INITIALIZATION ---
    initializeModal(state);
    initializeForm(state);
    initializeTable(state);
    initializeFilters(state);

    // --- INITIAL RENDER ---
    if (state.allResidents.length >= 0) {
        state.filteredResidents = state.allResidents;
        if (!state.isPendingView) {
            applyFilters(state);
        } else {
            renderTable(state);
        }
    } else {
        const tableBody = document.getElementById('residentsTableBody');
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No residents found in this view.</td></tr>';
    }

    // --- EVENT LISTENERS ---
    document.getElementById('addResidentBtn').addEventListener('click', () => openModalForAdd(state));
});