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
            // --- URL UPDATED HERE ---
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
                // --- URL UPDATED HERE ---
                const res = await fetch('/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/residents/process?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    // This will now reload the page to show the change
                    window.location.reload();
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

    // Since the view now loads the data initially, we don't need fetchFilteredResidents
    // The filtering will be done client-side for now to simplify the transition.
    
    // Attach the modal buttons to the initially loaded rows
    attachModalButtons();
});