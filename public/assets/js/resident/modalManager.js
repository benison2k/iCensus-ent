// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/resident/modalManager.js

const basePath = '/iCensus-ent/public';

function setFormEditable(editable, state) {
    const form = document.getElementById('residentForm');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = form.querySelector('.editBtn');
    const deleteBtn = form.querySelector('.deleteBtn');

    form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
    saveBtn.style.display = editable ? 'inline-flex' : 'none';
    editBtn.style.display = editable ? 'none' : 'inline-flex';

    if (editable || state.userRole === 'Encoder') {
        deleteBtn.style.display = 'none';
    } else {
        deleteBtn.style.display = 'inline-flex';
    }
};

async function openModalForEdit(id, state) {
    const form = document.getElementById('residentForm');
    const modal = document.getElementById('residentModal');
    const modalTitle = document.getElementById('modalTitle');
    const hiddenId = document.getElementById('resident_id');

    form.reset();
    try {
        const res = await fetch(`${basePath}/residents/process?action=get&resident_id=${id}`);
        const result = await res.json();
        if (result.status !== 'success') return alert('Resident not found.');

        const data = result.resident;
        Object.keys(data).forEach(key => {
            const el = form.elements[key];
            if (el) {
                if (el.type === 'checkbox') {
                    el.checked = (data[key] == 1);
                } else {
                    el.value = data[key];
                }
            }
        });

        setFormEditable(false, state);
        modalTitle.textContent = `View Resident Info`;
        hiddenId.value = id;
        // ✅ FIX: Changed to 'flex' to enable centering
        modal.style.display = 'flex';
    } catch (err) {
        console.error('Failed to fetch resident data:', err);
    }
};

function openModalForAdd(state) {
    const form = document.getElementById('residentForm');
    const modal = document.getElementById('residentModal');
    const modalTitle = document.getElementById('modalTitle');
    const hiddenId = document.getElementById('resident_id');
    const editBtn = modal.querySelector('.editBtn');

    form.reset();
    hiddenId.value = '';
    setFormEditable(true, state);
    editBtn.style.display = 'none';
    modalTitle.textContent = 'Add New Resident';
    // ✅ FIX: Changed to 'flex' to enable centering
    modal.style.display = 'flex';
};

function initializeModal(state) {
    const modal = document.getElementById('residentModal');
    const closeModal = modal.querySelector('.close');
    const editBtn = modal.querySelector('.editBtn');

    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    editBtn.addEventListener('click', () => setFormEditable(true, state));
}

export { initializeModal, openModalForEdit, openModalForAdd };