import { fetchData } from './api.js';

const libraryModal = document.getElementById('widget-library-modal');
const builderModal = document.getElementById('chart-builder-modal');
const chartListContainer = document.getElementById('chart-list-container');
const form = document.getElementById('chart-builder-form');

function closeAllModals() {
    libraryModal.style.display = 'none';
    builderModal.style.display = 'none';
}

export function openChartBuilder(chartData = null) {
    form.reset();
    document.getElementById('chart_id_input').value = '';
    
    // Logic to pre-fill form for editing can be added here later
    
    libraryModal.style.display = 'none';
    builderModal.style.display = 'flex';
}

export async function fetchAndShowCharts() {
    const result = await fetchData('analytics/charts');
    chartListContainer.innerHTML = ''; // Clear previous list

    if (result.status === 'success' && result.charts.length > 0) {
        result.charts.forEach(chart => {
            const chartItem = document.createElement('div');
            chartItem.className = 'chart-list-item'; // You can style this class
            chartItem.innerHTML = `
                <span>${chart.title}</span>
                <button data-chart-id="${chart.id}" data-chart-title="${chart.title}">Add to Dashboard</button>
            `;
            chartListContainer.appendChild(chartItem);
        });
    } else {
        chartListContainer.innerHTML = '<p>No custom charts found. Create one!</p>';
    }
    
    libraryModal.style.display = 'flex';
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // We'll need a new backend endpoint to save this data
    // For now, let's just log it and close the modal
    console.log('Chart definition to save:', data);
    alert('Chart saving logic needs to be connected to a new backend endpoint.');
    
    closeAllModals();
    // After saving, you would typically reload the chart list or the dashboard
}

export function setupBuilderEventListeners() {
    libraryModal.querySelector('.close-btn').addEventListener('click', closeAllModals);
    builderModal.querySelector('.close-btn').addEventListener('click', closeAllModals);
    
    form.addEventListener('submit', handleFormSubmit);

    // Event delegation for the "Add to Dashboard" buttons
    chartListContainer.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON') {
            const chartId = e.target.dataset.chartId;
            const chartTitle = e.target.dataset.chartTitle;

            // Dispatch a custom event that the main script can listen for
            const addEvent = new CustomEvent('addChartToGrid', { 
                detail: { chartId, chartTitle } 
            });
            document.dispatchEvent(addEvent);
            
            closeAllModals();
        }
    });
}