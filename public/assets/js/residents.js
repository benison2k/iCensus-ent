document.addEventListener('DOMContentLoaded', () => {
    // --- MODAL ELEMENTS ---
    const modal = document.getElementById('residentModal');
    const closeModal = modal.querySelector('.close');
    const form = document.getElementById('residentForm');
    const modalTitle = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = modal.querySelector('.editBtn');
    const deleteBtn = modal.querySelector('.deleteBtn');
    const hiddenId = document.getElementById('resident_id');
    const addResidentBtn = document.getElementById('addResidentBtn');

    // --- TABLE & FILTER ELEMENTS ---
    const tableBody = document.getElementById('residentsTableBody');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const ageMin = document.getElementById('ageMin');
    const ageMax = document.getElementById('ageMax');
    const purokFilter = document.getElementById('purokFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCountEl');
    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    const basePath = '/iCensus-ent/public';

    // --- STATE ---
    let currentPage = 1;
    let pageSize = parseInt(pageSizeSelect.value, 10);
    let filteredResidents = [];

    // --- MODAL & FORM FUNCTIONS ---
    const setFormEditable = (editable) => {
        form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
        saveBtn.style.display = editable ? 'inline-flex' : 'none';
        editBtn.style.display = editable ? 'none' : 'inline-flex';
        deleteBtn.style.display = editable ? 'none' : 'inline-flex';
    };

    const openModalForEdit = async (id) => {
        form.reset();
        try {
            const res = await fetch(`${basePath}/residents/process?action=get&resident_id=${id}`);
            const result = await res.json();
            if (result.status !== 'success') return alert('Resident not found.');
            
            const data = result.resident;
            Object.keys(data).forEach(key => { if (form[key]) form[key].value = data[key]; });
            
            setFormEditable(false);
            modalTitle.textContent = `View Resident Info`;
            hiddenId.value = id;
            modal.style.display = 'block';
        } catch (err) {
            console.error('Failed to fetch resident data:', err);
        }
    };

    const openModalForAdd = () => {
        form.reset();
        hiddenId.value = '';
        setFormEditable(true);
        editBtn.style.display = 'none';
        deleteBtn.style.display = 'none';
        modalTitle.textContent = 'Add New Resident';
        modal.style.display = 'block';
    };

    // --- TABLE & PAGINATION FUNCTIONS ---
    const attachEventListenersToRows = () => {
        document.querySelectorAll('.moreBtn').forEach(btn => {
            btn.addEventListener('click', (e) => openModalForEdit(e.currentTarget.dataset.id));
        });
    };

    const renderTable = () => {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageSlice = filteredResidents.slice(start, end);

        pageSlice.forEach(r => {
            const middleInitial = r.middle_name ? `${r.middle_name.charAt(0).toUpperCase()}.` : '';
            const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}`;
            const safeStatus = (r.status || '').toLowerCase();
            tableBody.innerHTML += `
                <tr data-id="${r.id}">
                    <td>${fullName}</td><td>${r.age}</td><td>${r.gender}</td>
                    <td>${address}</td>
                    <td><span class="status-label status-${safeStatus}">${r.status}</span></td>
                    <td><button class="moreBtn material-icons" data-id="${r.id}" title="View Resident Info">more_vert</button></td>
                </tr>`;
        });
        updatePagination();
        attachEventListenersToRows();
    };

    const applyFilters = () => {
        const searchTerm = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const gender = genderFilter.value;
        const minAge = ageMin.value ? parseInt(ageMin.value, 10) : null;
        const maxAge = ageMax.value ? parseInt(ageMax.value, 10) : null;
        const purok = purokFilter.value;
        
        filteredResidents = allResidentsData.filter(r => {
            const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}`.toLowerCase();
            
            if (searchTerm && !fullName.includes(searchTerm) && !address.includes(searchTerm)) return false;
            if (status && r.status !== status) return false;
            if (gender && r.gender !== gender) return false;
            if (minAge !== null && r.age < minAge) return false;
            if (maxAge !== null && r.age > maxAge) return false;
            if (purok && r.purok !== purok) return false;
            return true;
        });
        currentPage = 1;
        renderTable();
    };

    const updatePagination = () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize) || 1;
        const startItem = filteredResidents.length === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const endItem = Math.min(currentPage * pageSize, filteredResidents.length);
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        shownCountEl.textContent = `${startItem}–${endItem}`;
        totalCountEl.textContent = filteredResidents.length;
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    };

    const jumpToPage = () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize) || 1;
        const page = parseInt(gotoPageInput.value, 10);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        } else {
            alert(`Please enter a page number between 1 and ${totalPages}.`);
        }
        gotoPageInput.value = '';
    };

    // --- ALL EVENT LISTENERS ---
    addResidentBtn.addEventListener('click', openModalForAdd);
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    editBtn.addEventListener('click', () => setFormEditable(true));
    
    deleteBtn.addEventListener('click', async () => {
        const id = hiddenId.value;
        if (!id) return;
        if (!confirm('Are you sure you want to delete this resident? This action cannot be undone.')) return;
        
        const response = await fetch(`${basePath}/residents/process`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ action: 'delete', id: id })
        });
        const result = await response.json();
        if (result.status === 'success') {
            window.location.reload();
        } else {
            alert('Failed to delete resident.');
        }
    });

    // THIS IS THE NEWLY ADDED FIX
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            window.location.reload();
        } else {
            alert('An error occurred while saving the resident.');
        }
    });

    [searchInput, ageMin, ageMax].forEach(el => el.addEventListener('input', applyFilters));
    [statusFilter, genderFilter, purokFilter].forEach(el => el.addEventListener('change', applyFilters));
    pageSizeSelect.addEventListener('change', (e) => {
        pageSize = parseInt(e.target.value, 10);
        currentPage = 1;
        renderTable();
    });
    prevPageBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTable(); } });
    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize);
        if (currentPage < totalPages) { currentPage++; renderTable(); }
    });
    gotoPageBtn.addEventListener('click', jumpToPage);
    gotoPageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') jumpToPage(); });
    clearBtn.addEventListener('click', () => {
        searchInput.value = ''; statusFilter.value = ''; genderFilter.value = '';
        ageMin.value = ''; ageMax.value = ''; purokFilter.value = '';
        applyFilters();
    });

    // --- INITIALIZATION ---
    applyFilters();
});