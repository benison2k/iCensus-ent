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

async function openModalForEdit(id, state, startInEditMode = false) {
    const form = document.getElementById('residentForm');
    const modal = document.getElementById('residentModal');
    const modalTitle = document.getElementById('modalTitle');
    const hiddenId = document.getElementById('resident_id');
    const approveBtn = document.getElementById('approveBtn');
    const declineBtn = document.getElementById('declineBtn');
    const editBtn = modal.querySelector('.editBtn');
    const deleteBtn = modal.querySelector('.deleteBtn');


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

        setFormEditable(startInEditMode, state);
        modalTitle.textContent = startInEditMode ? `Edit Resident Info` : `View Resident Info`;
        hiddenId.value = id;

        // Show/hide buttons based on approval status
        if (data.approval_status === 'pending') {
            approveBtn.style.display = 'inline-flex';
            declineBtn.style.display = 'inline-flex';
            approveBtn.href = `${basePath}/residents/approve?id=${id}`;
            declineBtn.href = `${basePath}/residents/reject?id=${id}`;
            editBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
        } else {
            approveBtn.style.display = 'none';
            declineBtn.style.display = 'none';
            // If starting in edit mode, hide the modal's own edit button
            editBtn.style.display = startInEditMode ? 'none' : 'inline-flex';
        }

        modal.style.display = 'flex';
        if (modal.updateProgress) {
            modal.updateProgress();
        }
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
    const approveBtn = document.getElementById('approveBtn');
    const declineBtn = document.getElementById('declineBtn');
    const deleteBtn = modal.querySelector('.deleteBtn');

    form.reset();
    hiddenId.value = '';
    setFormEditable(true, state);
    editBtn.style.display = 'none';
    approveBtn.style.display = 'none';
    declineBtn.style.display = 'none';
    deleteBtn.style.display = 'none';

    modalTitle.textContent = 'Add New Resident';
    modal.style.display = 'flex';
    if (modal.updateProgress) {
        modal.updateProgress();
    }
};

function initializeModal(state) {
    const modal = document.getElementById('residentModal');
    const closeModal = modal.querySelector('.close');
    const editBtn = modal.querySelector('.editBtn');
    const declineBtn = document.getElementById('declineBtn');

    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    editBtn.addEventListener('click', () => setFormEditable(true, state));
    if(declineBtn) {
        declineBtn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to reject this entry?')) {
                e.preventDefault();
            }
        });
    }
}

export { initializeModal, openModalForEdit, openModalForAdd };