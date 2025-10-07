// public/assets/js/modalManager.js

import { fetchData } from './api.js';
import { getChartInfo } from './chartConfig.js';

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

    document.body.addEventListener('click', function(e) {
        const viewButton = e.target.closest('.analytics-view-btn');
        if (viewButton) {
            const residentId = viewButton.dataset.id;
            openResidentDetailsModal(residentId);
        }
    });
}

/**
 * Creates a descriptive title for the filtered residents modal.
 * @param {string} metric - The ID of the chart (e.g., 'senior_citizens_by_purok').
 * @param {string} category - The primary category clicked (e.g., 'Purok 2').
 * @param {string|null} series - The series clicked in a grouped chart (e.g., 'Male').
 * @param {number} count - The number of residents found.
 * @returns {string} A descriptive title.
 */
function getDetailedTitle(metric, category, series, count) {
    const chartTitle = getChartInfo(metric).title;
    const cleanCategory = category.split(' = ')[0];

    switch (metric) {
        case 'senior_citizens_by_purok':
            // --- THIS IS THE FIX ---
            return `Number of Senior Citizens in Purok ${cleanCategory}: ${count}`;
        case 'voter_population_by_purok':
            return `Voters in Purok ${cleanCategory}: ${count}`;
        case 'school_age_population_by_purok':
            return `School-Age Population in Purok ${cleanCategory} (${series}): ${count}`;
        case 'population_pyramid':
            return `${series}s in Age Bracket ${cleanCategory}: ${count}`;
        case 'civil_status_distribution_by_gender':
            return `${series}s with civil status "${cleanCategory}": ${count}`;
        case 'heads_of_household_by_gender':
            return `Heads of Household who are ${cleanCategory}: ${count}`;
        case 'purok':
            return `Population in Purok ${cleanCategory}: ${count}`;
        case 'residents_per_street':
            return `Residents on ${cleanCategory} street: ${count}`;
        default:
            if (series) {
                 return `Residents - ${chartTitle} (${series}: ${cleanCategory}): ${count}`;
            }
            return `Residents - ${chartTitle} (${cleanCategory}): ${count}`;
    }
}

/**
 * Fetches filtered resident data and displays it in a modal.
 * @param {string} metric - The ID of the chart being clicked.
 * @param {URLSearchParams} params - The query parameters for the API call.
 * @param {string} category - The raw category label from the chart.
 * @param {string|null} series - The series from the chart, if applicable.
 */
export async function showFilteredResidentsModal(metric, params, category, series = null) {
    const modal = document.getElementById('filtered-residents-modal');
    const titleEl = document.getElementById('filtered-title');
    const tableBody = modal.querySelector('tbody');

    tableBody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
    modal.style.display = 'flex';
    titleEl.textContent = 'Loading...';

    const result = await fetchData('analytics/filtered-residents', Object.fromEntries(params));

    const count = result.residents ? result.residents.length : 0;
    
    titleEl.textContent = getDetailedTitle(metric, category, series, count);

    if (result.status === 'success' && result.residents.length > 0) {
        tableBody.innerHTML = '';
        result.residents.forEach(r => {
            const row = `<tr>
                <td>${r.first_name} ${r.last_name}</td>
                <td>${r.age}</td>
                <td>${r.gender}</td>
                <td>${r.house_no} ${r.street}, Purok ${r.purok}</td>
                <td><span class="status-label status-${(r.status || '').toLowerCase()}">${r.status}</span></td>
                <td>
                    <button class="action-btn analytics-view-btn material-icons" data-id="${r.id}" title="View More Details">more_vert</button>
                </td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    } else {
        tableBody.innerHTML = '<tr><td colspan="6">No residents found for this selection.</td></tr>';
    }
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