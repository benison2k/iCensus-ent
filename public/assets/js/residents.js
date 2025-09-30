document.addEventListener('DOMContentLoaded', () => {
    // --- MODAL ELEMENTS ---
    const modal = document.getElementById('residentModal');
    const closeModal = modal ? modal.querySelector('.close') : null;
    const form = document.getElementById('residentForm');
    const modalTitle = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = modal ? modal.querySelector('.editBtn') : null;
    const deleteBtn = modal ? modal.querySelector('.deleteBtn') : null;
    const hiddenId = document.getElementById('resident_id');

    // --- TABLE & FILTER ELEMENTS ---
    const tableBody = document.getElementById('residentsTableBody');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const ageMin = document.getElementById('ageMin');
    const ageMax = document.getElementById('ageMax');
    const purokFilter = document.getElementById('purokFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');

    // --- PAGINATION ELEMENTS ---
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCount');
    const filteredResults = document.getElementById('filteredResults');
    const filteredCount = document.getElementById('filteredCount');
    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');

    // --- STATE VARIABLES ---
    let currentPage = 1;
    let pageSize = pageSizeSelect ? parseInt(pageSizeSelect.value, 10) : 10;
    let allResidents = []; // This will hold the master list of residents

    // --- MODAL FUNCTIONS ---
    const setFormEditable = (editable) => {
        if (!form) return;
        form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
        if (saveBtn) saveBtn.style.display = editable ? 'inline-flex' : 'none';
    };

    const openModal = async (id) => {
        try {
            // MVC ROUTE: Fetch a single resident's data
            const res = await fetch(`/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/residents/process?action=get&resident_id=${id}`);
            const result = await res.json();
            if (result.status !== 'success') return alert('Resident not found.');
            
            const data = result.resident;
            if (form) Object.keys(data).forEach(key => { if (form[key]) form[key].value = data[key]; });
            
            setFormEditable(false);
            if (modalTitle) modalTitle.textContent = `Resident Info - ${data.first_name} ${data.last_name}`;
            if (modal) modal.style.display = 'block';
            if (form) form.dataset.id = id;
            if (hiddenId) hiddenId.value = id;
        } catch (err) {
            console.error('Failed to fetch resident data:', err);
            alert('An error occurred while fetching resident data.');
        }
    };

    const attachRowEventListeners = () => {
        document.querySelectorAll('.moreBtn').forEach(btn => {
            btn.addEventListener('click', (e) => openModal(e.currentTarget.dataset.id));
        });
    };

    if (closeModal) closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    if (editBtn) editBtn.addEventListener('click', () => {
        setFormEditable(true);
        if (modalTitle) modalTitle.textContent = 'Edit Resident';
    });
    
    // --- DATA & TABLE FUNCTIONS ---
    const renderTable = (residentsToRender) => {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageSlice = residentsToRender.slice(start, end);

        if (pageSlice.length === 0 && residentsToRender.length > 0) {
             // If current page is empty but there's data, go to first page
            currentPage = 1;
            renderTable(residentsToRender);
            return;
        }
        
        pageSlice.forEach(r => {
            const middleInitial = r.middle_name ? `${r.middle_name.charAt(0).toUpperCase()}.` : '';
            const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}`;
            const safeStatus = (r.status || '').toLowerCase();

            tableBody.innerHTML += `
                <tr data-id="${r.id}">
                    <td>${fullName}</td>
                    <td>${r.age}</td>
                    <td>${r.gender}</td>
                    <td>${address}</td>
                    <td><span class="status-label status-${safeStatus}">${r.status}</span></td>
                    <td><button class="moreBtn material-icons" data-id="${r.id}" title="View Resident Info">more_vert</button></td>
                </tr>`;
        });

        updatePagination(residentsToRender.length);
        attachRowEventListeners();
    };

    const applyFilters = () => {
        const search = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const gender = genderFilter.value;
        const minAge = ageMin.value ? parseInt(ageMin.value) : null;
        const maxAge = ageMax.value ? parseInt(ageMax.value) : null;
        const purok = purokFilter.value;

        const filtered = allResidents.filter(r => {
            const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}`.toLowerCase();
            
            if (search && !fullName.includes(search) && !address.includes(search)) return false;
            if (status && r.status !== status) return false;
            if (gender && r.gender !== gender) return false;
            if (minAge !== null && r.age < minAge) return false;
            if (maxAge !== null && r.age > maxAge) return false;
            if (purok && r.purok !== purok) return false;
            return true;
        });

        const isFiltered = search || status || gender || minAge !== null || maxAge !== null || purok;
        filteredResults.style.display = isFiltered ? 'block' : 'none';
        filteredCount.textContent = filtered.length;

        currentPage = 1;
        renderTable(filtered);
    };

    const updatePagination = (totalFilteredItems) => {
        const totalPages = Math.ceil(totalFilteredItems / pageSize) || 1;
        const startItem = totalFilteredItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const endItem = Math.min(currentPage * pageSize, totalFilteredItems);

        if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        if (shownCountEl) shownCountEl.textContent = `${startItem}–${endItem}`;
        if (totalCountEl) totalCountEl.textContent = totalFilteredItems;
        if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
        if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;
    };
    
    // --- INITIALIZATION ---
    // Fetch all residents once on page load and store them.
    const initializePage = async () => {
        // The data is already embedded in the page via PHP, so we just parse it
        const residentRows = Array.from(document.querySelectorAll('#residentsTableBody tr'));
        // In a fully AJAX-driven app, you'd fetch here. Since PHP renders the initial list,
        // we can just use that to build our client-side data cache.
        // For simplicity, we'll continue with client-side filtering based on the initial load.
        allResidents = residentRows.map(row => {
            const cells = row.cells;
            return {
                id: row.dataset.id,
                first_name: cells[0].textContent.split(' ')[0],
                last_name: cells[0].textContent.split(' ').slice(-1)[0],
                age: parseInt(cells[1].textContent, 10),
                gender: cells[2].textContent,
                house_no: cells[3].textContent.split(' ')[0],
                street: cells[3].textContent.split(',')[0].split(' ').slice(1).join(' '),
                purok: cells[3].textContent.split('Purok ')[1],
                status: cells[4].textContent,
            };
        });
        
        applyFilters(); // Apply initial filters (if any) and render the table
    };

    // Attach all event listeners
    [searchInput, ageMin, ageMax].forEach(el => el?.addEventListener('input', applyFilters));
    [statusFilter, genderFilter, purokFilter].forEach(el => el?.addEventListener('change', applyFilters));
    
    pageSizeSelect?.addEventListener('change', (e) => {
        pageSize = parseInt(e.target.value, 10);
        applyFilters();
    });

    prevPageBtn?.addEventListener('click', () => { if(currentPage > 1) { currentPage--; applyFilters(); } });
    nextPageBtn?.addEventListener('click', () => {
        const totalPages = Math.ceil(parseInt(totalCountEl.textContent) / pageSize);
        if (currentPage < totalPages) { currentPage++; applyFilters(); }
    });

    clearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        statusFilter.value = '';
        genderFilter.value = '';
        ageMin.value = '';
        ageMax.value = '';
        purokFilter.value = '';
        applyFilters();
    });

    addResidentBtn?.addEventListener('click', () => {
        form.reset();
        hiddenId.value = '';
        setFormEditable(true);
        modalTitle.textContent = 'Add New Resident';
        modal.style.display = 'block';
    });

    // Run the initialization
    initializePage();
});