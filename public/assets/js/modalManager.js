// public/assets/js/modalManager.js

import { fetchData } from './api.js';

function setupModal(modalId, openTriggerId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const closeBtn = modal.querySelector('.close-btn');

    if (openTriggerId) {
        const openBtn = document.getElementById(openTriggerId);
        if (openBtn) openBtn.addEventListener('click', () => modal.style.display = 'flex');
    }

    if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.style.display = 'none';
    });
    return modal;
}

export function initializeModals() {
    setupModal('report-modal', 'generate-report-btn');
    setupModal('chart-detail-modal');
    setupModal('filtered-residents-modal');
    setupModal('analytics-resident-detail-modal');

    // Add event delegation for dynamically created resident view buttons
    document.body.addEventListener('click', function(e) {
        const viewButton = e.target.closest('.analytics-view-btn');
        if (viewButton) {
            const residentId = viewButton.dataset.id;
            openResidentDetailsModal(residentId);
        }
    });
}

export async function openResidentDetailsModal(residentId) {
    const modal = document.getElementById('analytics-resident-detail-modal');
    const modalTitle = document.getElementById('detail-modal-title');
    const modalContent = document.getElementById('detail-modal-content');
    
    modalContent.innerHTML = '<p>Loading...</p>';
    modal.style.display = 'flex';

    const result = await fetchData('residents/process', { action: 'get', resident_id: residentId });

    if (result.status !== 'success' || !result.resident) {
        modalContent.innerHTML = '<p>Error: Could not fetch resident details.</p>';
        return;
    }

    const r = result.resident;
    modalTitle.textContent = `Details for ${r.first_name} ${r.last_name}`;
    const booleanCheck = (value) => value == 1 ? 'Yes' : 'No';

    modalContent.innerHTML = `
        <div class="detail-group">
            <h4><span class="material-icons">person</span>Personal Info</h4>
            <div class="detail-item"><strong>Full Name:</strong> <span>${r.first_name || ''} ${r.middle_name || ''} ${r.last_name || ''} ${r.suffix || ''}</span></div>
            <div class="detail-item"><strong>Date of Birth:</strong> <span>${r.dob}</span></div>
            <div class="detail-item"><strong>Gender:</strong> <span>${r.gender}</span></div>
            <div class="detail-item"><strong>Civil Status:</strong> <span>${r.civil_status || 'N/A'}</span></div>
        </div>
        <div class="detail-group">
            <h4><span class="material-icons">home</span>Address & Household</h4>
            <div class="detail-item"><strong>Address:</strong> <span>${r.house_no || ''} ${r.street || ''}, Purok ${r.purok || ''}</span></div>
            <div class="detail-item"><strong>Head of Household:</strong> <span>${r.head_of_household || 'N/A'}</span></div>
            <div class="detail-item"><strong>Relationship:</strong> <span>${r.relationship || 'N/A'}</span></div>
        </div>
        <div class="detail-group">
            <h4><span class="material-icons">contact_phone</span>Contact & Health</h4>
            <div class="detail-item"><strong>Contact No:</strong> <span>${r.contact_number || 'N/A'}</span></div>
            <div class="detail-item"><strong>Blood Type:</strong> <span>${r.blood_type || 'N/A'}</span></div>
        </div>
        <div class="detail-group">
            <h4><span class="material-icons">admin_panel_settings</span>Administrative</h4>
            <div class="detail-item"><strong>Resident Status:</strong> <span>${r.status}</span></div>
            <div class="detail-item"><strong>Registered Voter:</strong> <span>${booleanCheck(r.is_registered_voter)}</span></div>
        </div>
    `;
}