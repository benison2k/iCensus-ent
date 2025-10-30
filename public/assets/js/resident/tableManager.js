// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/resident/tableManager.js

import { openModalForEdit } from './modalManager.js';
import { handleDelete } from './formHandler.js';

function renderTable(state) {
    const tableBody = document.getElementById('residentsTableBody');

    state.filteredResidents.sort((a, b) => {
        const valA = (a[state.currentSort.column] || '').toString().toLowerCase();
        const valB = (b[state.currentSort.column] || '').toString().toLowerCase();
        
        let comparison = valA.localeCompare(valB, undefined, {numeric: true});

        return state.currentSort.order === 'desc' ? comparison * -1 : comparison;
    });

    tableBody.innerHTML = '';
    const start = (state.currentPage - 1) * state.pageSize;
    const end = start + state.pageSize;
    const pageSlice = state.filteredResidents.slice(start, end);
    let rowsHtml = '';

    if (state.filteredResidents.length === 0) {
        rowsHtml = '<tr><td colspan="6" style="text-align: center; height: 380px; vertical-align: middle;">No residents found matching the criteria.</td></tr>';
    } else {
        pageSlice.forEach(r => {
            const middleInitial = r.middle_name ? `${r.middle_name.charAt(0).toUpperCase()}.` : '';
            const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}`;
            const safeStatus = (r.status || '').toLowerCase();
            rowsHtml += `
                <tr data-id="${r.id}">
                    <td>${fullName}</td>
                    <td>${r.age}</td>
                    <td>${r.gender}</td>
                    <td>${address}</td>
                    <td><span class="status-label status-${safeStatus}">${r.status}</span></td>
                    <td>
                        <div class="actions-column">
                            <button class="action-btn btn-view moreBtn" data-id="${r.id}" title="View Details"><span class="material-icons">visibility</span></button>
                            <button class="action-btn btn-edit" data-id="${r.id}" title="Edit Details"><span class="material-icons">edit</span></button>
                            <button class="action-btn btn-delete" data-id="${r.id}" title="Delete Resident"><span class="material-icons">delete</span></button>
                        </div>
                    </td>
                </tr>`;
        });
    }

    tableBody.innerHTML = rowsHtml;

    if (state.filteredResidents.length > 0) {
        const placeholdersNeeded = state.pageSize - pageSlice.length;
        for (let i = 0; i < placeholdersNeeded; i++) {
            tableBody.innerHTML += '<tr><td colspan="6">&nbsp;</td></tr>';
        }
    }

    updatePagination(state);
    updateSortIcons(state);
};

function updatePagination(state) {
    const pageInfo = document.getElementById('pageInfo');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCountEl');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');

    if (!pageInfo) return;

    const totalPages = Math.ceil(state.filteredResidents.length / state.pageSize) || 1;
    const startItem = state.filteredResidents.length === 0 ? 0 : (state.currentPage - 1) * state.pageSize + 1;
    const endItem = Math.min(state.currentPage * state.pageSize, state.filteredResidents.length);
    pageInfo.textContent = `Page ${state.currentPage} of ${totalPages}`;
    shownCountEl.textContent = `${startItem}–${endItem}`;
    totalCountEl.textContent = state.filteredResidents.length;
    prevPageBtn.disabled = state.currentPage === 1;
    nextPageBtn.disabled = state.currentPage === totalPages;
};

function updateSortIcons(state) {
    const nameSortSelect = document.getElementById('nameSortSelect');

    document.querySelectorAll('.sort-icon').forEach(icon => icon.innerHTML = '');
    
    let activeHeader;
    if (state.currentSort.column === 'first_name') {
        activeHeader = document.querySelector('th[data-sort="last_name"]');
    } else {
        activeHeader = document.querySelector(`th[data-sort="${state.currentSort.column}"]`);
    }

    if (activeHeader) {
        let indicator = '';
        if (state.currentSort.column === 'age') {
            indicator = `<span class="material-icons">${state.currentSort.order === 'asc' ? 'arrow_upward' : 'arrow_downward'}</span>`;
        } else if (state.currentSort.column === 'last_name' || state.currentSort.column === 'first_name') {
            const sortOrderText = state.currentSort.order === 'asc' ? '(A-Z)' : '(Z-A)';
            const sortColumnText = `(by ${state.currentSort.column === 'first_name' ? 'First' : 'Last'})`;
            indicator = `<span class="sort-text">${sortColumnText} ${sortOrderText}</span>`;
        }
        activeHeader.querySelector('.sort-icon').innerHTML = indicator;
    }
    
    if (nameSortSelect) {
        nameSortSelect.value = `${state.currentSort.column}-${state.currentSort.order}`;
    }
};

function jumpToPage(state) {
    const gotoPageInput = document.getElementById('gotoPage');
    if (!gotoPageInput) return;
    const totalPages = Math.ceil(state.filteredResidents.length / state.pageSize) || 1;
    const page = parseInt(gotoPageInput.value, 10);
    if (page >= 1 && page <= totalPages) {
        state.currentPage = page;
        renderTable(state);
    } else {
        alert(`Please enter a page number between 1 and ${totalPages}.`);
    }
    gotoPageInput.value = '';
};

function initializeTable(state) {
    const residentsTable = document.getElementById('residentsTable');
    const tableBody = document.getElementById('residentsTableBody');
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    const gotoPageInput = document.getElementById('gotoPage');
    const nameSortSelect = document.getElementById('nameSortSelect');

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', (e) => {
            state.pageSize = parseInt(e.target.value, 10);
            state.currentPage = 1;
            renderTable(state);
        });
    }
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => { if (state.currentPage > 1) { state.currentPage--; renderTable(state); } });
    }
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(state.filteredResidents.length / state.pageSize);
            if (state.currentPage < totalPages) { state.currentPage++; renderTable(state); }
        });
    }
    if (gotoPageBtn) {
        gotoPageBtn.addEventListener('click', () => jumpToPage(state));
    }
    if (gotoPageInput) {
        gotoPageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') jumpToPage(state); });
    }

    if (residentsTable) {
        const thead = residentsTable.querySelector('thead');
        if (thead) {
            thead.addEventListener('click', (e) => {
                const header = e.target.closest('.sortable');
                if (header && !e.target.matches('.sort-select-overlay')) {
                    const sortColumn = header.dataset.sort;

                    if (sortColumn === 'last_name') {
                        if (state.currentSort.column === 'last_name') {
                            if (state.currentSort.order === 'asc') {
                                state.currentSort.order = 'desc';
                            } else {
                                state.currentSort.column = 'first_name';
                                state.currentSort.order = 'asc';
                            }
                        } else if (state.currentSort.column === 'first_name') {
                            if (state.currentSort.order === 'asc') {
                                state.currentSort.order = 'desc';
                            } else {
                                state.currentSort.column = 'last_name';
                                state.currentSort.order = 'asc';
                            }
                        } else {
                            state.currentSort.column = 'last_name';
                            state.currentSort.order = 'asc';
                        }
                    } else {
                        if (state.currentSort.column === sortColumn) {
                            state.currentSort.order = state.currentSort.order === 'asc' ? 'desc' : 'asc';
                        } else {
                            state.currentSort.column = sortColumn;
                            state.currentSort.order = 'asc';
                        }
                    }
                    
                    renderTable(state);
                }
            });
        }
    }

    if (nameSortSelect) {
        nameSortSelect.addEventListener('change', (e) => {
            const [column, order] = e.target.value.split('-');
            state.currentSort.column = column;
            state.currentSort.order = order;
            renderTable(state);
        });
    }

    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const moreButton = e.target.closest('.moreBtn');
            if (moreButton) {
                openModalForEdit(moreButton.dataset.id, state, false); // Open in view mode
            }
            const editButton = e.target.closest('.btn-edit');
            if (editButton) {
                openModalForEdit(editButton.dataset.id, state, true); // Open in edit mode
            }
            const deleteButton = e.target.closest('.btn-delete');
            if (deleteButton) {
                handleDelete(deleteButton.dataset.id);
            }
        });
    }
}

export { initializeTable, renderTable };