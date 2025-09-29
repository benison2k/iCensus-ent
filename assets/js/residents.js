document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('residentModal');
    const closeModal = modal ? modal.querySelector('.close') : null;
    const form = document.getElementById('residentForm');
    const modalTitle = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = modal ? modal.querySelector('.editBtn') : null;
    const deleteBtn = modal ? modal.querySelector('.deleteBtn') : null;
    const hiddenId = document.getElementById('resident_id');

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
    const totalCountEl = document.getElementById('totalCount');
    const filteredResults = document.getElementById('filteredResults');
    const filteredCount = document.getElementById('filteredCount');

    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');

    let currentPage = 1;
    let pageSize = pageSizeSelect ? parseInt(pageSizeSelect.value, 10) : 10;
    let currentResidents = [];

    const setFormEditable = (editable) => {
        if (!form) return;
        form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
        if (saveBtn) saveBtn.style.display = editable ? 'inline-flex' : 'none';
    };

    const openModal = async (id) => {
        try {
            const res = await fetch(`../core/residents_process.php?action=get&resident_id=${id}`);
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
            console.error(err);
            alert('Failed to fetch resident data.');
        }
    };

    const attachModalButtons = () => {
        document.querySelectorAll('.moreBtn').forEach(btn => {
            if (btn._handler) btn.removeEventListener('click', btn._handler);
            const handler = () => openModal(btn.dataset.id);
            btn.addEventListener('click', handler);
            btn._handler = handler;
        });
    };

    if (closeModal) {
        closeModal.addEventListener('click', () => {
            if (modal) modal.style.display = 'none';
            setFormEditable(false);
            if (form) form.reset();
            if (hiddenId) hiddenId.value = '';
        });
    }
    window.addEventListener('click', (e) => {
        if (modal && e.target === modal) {
            modal.style.display = 'none';
            setFormEditable(false);
            if (form) form.reset();
            if (hiddenId) hiddenId.value = '';
        }
    });

    if (editBtn) {
        editBtn.addEventListener('click', () => { setFormEditable(true); if (modalTitle) modalTitle.textContent = 'Edit Resident'; });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            const id = form ? form.dataset.id : null;
            if (!id) return;
            if (!confirm('Are you sure you want to delete this resident?')) return;
            try {
                const res = await fetch('../core/residents_process.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    // Re-fetch all residents after a successful delete
                    fetchFilteredResidents();
                } else {
                    alert('Delete failed');
                }
            } catch (err) {
                console.error(err);
                alert('Delete failed');
            }
        });
    }

    const addResidentBtn = document.getElementById('addResidentBtn');
    if (addResidentBtn) {
        addResidentBtn.addEventListener('click', () => {
            if (form) form.reset();
            if (form) delete form.dataset.id;
            if (hiddenId) hiddenId.value = '';
            setFormEditable(true);
            if (modalTitle) modalTitle.textContent = 'Add New Resident';
            if (modal) modal.style.display = 'block';
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (genderFilter) genderFilter.value = '';
            if (ageMin) ageMin.value = '';
            if (ageMax) ageMax.value = '';
            if (purokFilter) purokFilter.value = '';
            fetchFilteredResidents();
        });
    }

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', () => {
            pageSize = parseInt(pageSizeSelect.value, 10);
            currentPage = 1;
            renderTable();
        });
    }

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(currentResidents.length / pageSize);
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
    }

    if (gotoPageBtn && gotoPageInput) {
        const doGoto = () => {
            const totalPages = Math.max(1, Math.ceil(currentResidents.length / pageSize));
            const pageNumber = parseInt(gotoPageInput.value, 10);
            if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= totalPages) {
                currentPage = pageNumber;
                renderTable();
            } else {
                alert(`Please enter a valid page between 1 and ${totalPages}`);
            }
            gotoPageInput.value = '';
        };
        gotoPageBtn.addEventListener('click', doGoto);
        gotoPageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') doGoto();
        });
    }

    const renderTable = () => {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const slice = currentResidents.slice(start, end);

        slice.forEach(r => {
            const middleInitial = r.middle_name ? r.middle_name[0].toUpperCase() + '.' : '';
            const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
            const address = `${r.house_no} ${r.street}, Purok ${r.purok}, ${r.barangay}`;
            const safeStatus = (r.status || '').toLowerCase();
            tableBody.innerHTML += `<tr data-id="${r.id}" data-status="${r.status}" data-gender="${r.gender}" data-age="${r.age}" data-purok="${r.purok}" data-barangay="${r.barangay}">
                <td>${fullName}</td>
                <td>${r.age}</td>
                <td>${r.gender}</td>
                <td>${address}</td>
                <td><span class="status-label status-${safeStatus}">${r.status}</span></td>
                <td><button class="moreBtn material-icons" data-id="${r.id}" title="View Resident Info">more_vert</button></td>
            </tr>`;
        });

        attachModalButtons();

        const totalPages = Math.ceil(currentResidents.length / pageSize) || 1;
        if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

        if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
        if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;

        const startNum = currentResidents.length === 0 ? 0 : start + 1;
        const endNum = Math.min(end, currentResidents.length);
        if (shownCountEl) shownCountEl.textContent = `${startNum}–${endNum}`;
        if (totalCountEl) totalCountEl.textContent = `${currentResidents.length}`;
    };

    const fetchFilteredResidents = async () => {
        const params = new URLSearchParams({
            search: searchInput ? searchInput.value : '',
            status: statusFilter ? statusFilter.value : '',
            gender: genderFilter ? genderFilter.value : '',
            age_min: ageMin ? ageMin.value : '',
            age_max: ageMax ? ageMax.value : '',
            purok: purokFilter ? purokFilter.value : ''
        });
        try {
            const res = await fetch(`../core/residents_process.php?action=filter&${params.toString()}`);
            const result = await res.json();
            if (result.status !== 'success') return;

            // The 'age' is now calculated from DOB on the server-side for consistency, but
            // this fallback is kept in case the server doesn't provide it.
            currentResidents = result.residents.map(r => ({
                ...r,
                age: r.age || (r.dob ? (new Date().getFullYear() - new Date(r.dob).getFullYear()) : 0)
            }));

            // Update filtered counts
            if (filteredCount) filteredCount.textContent = currentResidents.length;
            if (filteredResults) {
                const isFiltered = searchInput.value || statusFilter.value || genderFilter.value || ageMin.value || ageMax.value || purokFilter.value;
                filteredResults.style.display = isFiltered ? 'block' : 'none';
            }

            currentPage = 1;
            renderTable();
        } catch (err) {
            console.error(err);
        }
    };

    if (searchInput) searchInput.addEventListener('input', fetchFilteredResidents);
    if (statusFilter) statusFilter.addEventListener('change', fetchFilteredResidents);
    if (genderFilter) genderFilter.addEventListener('change', fetchFilteredResidents);
    if (ageMin) ageMin.addEventListener('input', fetchFilteredResidents);
    if (ageMax) ageMax.addEventListener('input', fetchFilteredResidents);
    if (purokFilter) purokFilter.addEventListener('change', fetchFilteredResidents);

    fetchFilteredResidents();
});