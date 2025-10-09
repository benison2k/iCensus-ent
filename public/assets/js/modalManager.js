// public/assets/js/modalManager.js

import { fetchData } from './api.js';
import { getChartInfo } from './chartConfig.js';
import { getFilterParamForMetric } from './chartManager.js';

// --- (All other functions like setupModal, initializeModals, getDetailedTitle, etc. remain the same) ---
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

function getDetailedTitle(metric, category, series, count) {
    const chartTitle = getChartInfo(metric).title;
    const cleanCategory = category.split(' = ')[0];

    switch (metric) {
        case 'senior_citizens_by_purok':
            return `Number of Senior Citizens in Purok ${cleanCategory}: ${count}`;
        case 'voter_population_by_purok':
            return `Voter Population in Purok ${cleanCategory}: ${count}`;
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
            return `Residents on ${cleanCategory} Street: ${count}`;
        case 'gender':
            return `Number of ${cleanCategory} Residents: ${count}`;
        case 'civil_status':
            return `Residents with Civil Status '${cleanCategory}': ${count}`;
        case 'blood_type':
            return `Residents with Blood Type '${cleanCategory}': ${count}`;
        case 'nationality':
            return `Residents with Nationality '${cleanCategory}': ${count}`;
        case 'relationship':
            return `Relationship to Head - ${cleanCategory}: ${count}`;
        case 'resident_status_overview':
            return `Residents with Status '${cleanCategory}': ${count}`;
        case 'educational_attainment':
            return `Highest Educational Attainment - ${cleanCategory}: ${count}`;
        case 'occupation':
            return `Residents with Occupation '${cleanCategory}': ${count}`;
        case 'ownership_status':
            return `Residents with Housing Status '${cleanCategory}': ${count}`;
        case 'pwd_distribution':
            return `Number of PWD Residents (${cleanCategory}): ${count}`;
        case 'solo_parent_distribution':
            return `Number of Solo Parents (${cleanCategory}): ${count}`;
        case '4ps_distribution':
            return `Number of 4Ps Beneficiaries (${cleanCategory}): ${count}`;
        case 'age':
            return `Residents in Age Group ${cleanCategory}: ${count}`;
        case 'detailed_age_brackets':
            return `Residents in Age Bracket ${cleanCategory}: ${count}`;
        case 'generation_breakdown':
            return `Residents in the ${cleanCategory} Generation: ${count}`;
        case 'sex_ratio':
             return `Number of ${cleanCategory} Residents: ${count}`;
        case 'household_size_distribution':
            return `Number of Households with ${cleanCategory}: ${count}`;
        case 'profile_completeness':
            return `Profiles with ${cleanCategory} Information: ${count}`;
        case 'emergency_contact_coverage':
            return `Residents who have an Emergency Contact listed (${cleanCategory}): ${count}`;
        default:
            if (series) {
                 return `${chartTitle} (${series}: ${cleanCategory}): ${count}`;
            }
            return `${chartTitle} (${cleanCategory}): ${count}`;
    }
}

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

/**
 * FIX: This function now reads dates from the main page to pass to the filter builder.
 */
export function showDetailModal(metric) {
    const originalChartDiv = document.getElementById(`${metric}_chart_div`);
    if (!originalChartDiv || !originalChartDiv.chartData) {
        return;
    }

    const modal = document.getElementById('chart-detail-modal');
    const chartContentDiv = document.getElementById('chart-detail-content');
    const titleEl = document.getElementById('chart-detail-title');
    const explanationEl = document.getElementById('chart-detail-explanation');
    const chartInfo = getChartInfo(metric);

    chartContentDiv.innerHTML = '';
    titleEl.textContent = chartInfo.title;
    explanationEl.textContent = chartInfo.explanation;

    const modalOptions = JSON.parse(JSON.stringify(originalChartDiv.chartOptions));
    modalOptions.height = '100%';
    modalOptions.width = '100%';
    modalOptions.chartArea = { 'width': '80%', 'height': '80%' };
    modalOptions.legend.position = 'right';

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';
    modalOptions.legend.textStyle = { color: fontColor };
    if (modalOptions.hAxis) modalOptions.hAxis.textStyle = { color: fontColor };
    if (modalOptions.vAxis) modalOptions.vAxis.textStyle = { color: fontColor };

    let chart;
    const chartType = chartInfo.type;
    if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') chart = new google.charts.Bar(chartContentDiv);
    else if (chartType === 'ColumnChart') chart = new google.visualization.ColumnChart(chartContentDiv);
    else if (chartType === 'BarChart') chart = new google.visualization.BarChart(chartContentDiv);
    else chart = new google.visualization.PieChart(chartContentDiv);
    
    google.visualization.events.addListener(chart, 'select', () => {
        const selection = chart.getSelection();
        if (selection.length > 0) {
            const { row, column } = selection[0];
            const dataTable = originalChartDiv.chartData;
            const category = dataTable.getValue(row, 0);
            let series = null;

            if (chartType === 'PopulationPyramid') {
                if (column === 1) series = 'Male';
                if (column === 3) series = 'Female';
            } else if (chartType === 'GroupedBar' && column > 0) {
                series = dataTable.getColumnLabel(column);
            }
            
            // Read the dates from the main page's input fields
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            // Use the value if it exists, otherwise pass null to trigger the fallback in chartManager
            const startDate = startDateInput && startDateInput.value ? startDateInput.value : null;
            const endDate = endDateInput && endDateInput.value ? endDateInput.value : null;
            
            // Pass the dates to the filter parameter builder.
            const filterParams = getFilterParamForMetric(metric, category, series, startDate, endDate);
            
            if (filterParams) {
                showFilteredResidentsModal(metric, filterParams, category, series);
            }
        }
    });

    modal.style.display = 'flex';
    setTimeout(() => {
        if (chartType === 'PopulationPyramid' || chartType === 'GroupedBar') {
            chart.draw(originalChartDiv.chartData, google.charts.Bar.convertOptions(modalOptions));
        } else {
            chart.draw(originalChartDiv.chartData, modalOptions);
        }
    }, 50);
}