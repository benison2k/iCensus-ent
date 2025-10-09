import { fetchData } from './api.js';

const libraryModal = document.getElementById('widget-library-modal');
const builderModal = document.getElementById('chart-builder-modal');
const chartListContainer = document.getElementById('chart-list-container');
const form = document.getElementById('chart-builder-form');
const basePath = '/iCensus-ent/public';

function closeAllModals() {
    libraryModal.style.display = 'none';
    builderModal.style.display = 'none';
}

export function openChartBuilder(chartData = null) {
    form.reset();
    document.getElementById('chart_id_input').value = '';
    libraryModal.style.display = 'none';
    builderModal.style.display = 'flex';
}

export async function fetchAndShowCharts() {
    const result = await fetchData('analytics/charts');
    chartListContainer.innerHTML = ''; // Clear previous list

    if (result.status === 'success' && result.charts.length > 0) {
        result.charts.forEach(chart => {
            const chartItem = document.createElement('div');
            chartItem.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:0.75rem; border-bottom:1px solid #eee;';
            
            chartItem.innerHTML = `
                <span style="font-weight: 500;">${chart.title}</span>
                <button data-chart-id="${chart.id}" data-chart-title="${chart.title}" data-chart-type="${chart.chart_type}" style="padding: 0.4rem 0.8rem; border-radius: 6px; border: none; background: #2e7d32; color: white; cursor: pointer;">Add to Dashboard</button>
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

    try {
        const response = await fetch(`${basePath}/analytics/save-chart`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            alert(result.message);
            closeAllModals();
            
            const addEvent = new CustomEvent('addChartToGrid', { 
                detail: { 
                    chartId: result.chart_id, 
                    chartTitle: formData.get('title'),
                    chartType: formData.get('chart_type')
                } 
            });
            document.dispatchEvent(addEvent);

        } else {
            alert('Error: ' + (result.message || 'Could not save chart.'));
        }
    } catch (error) {
        console.error('Save chart failed:', error);
        alert('An unexpected network error occurred.');
    }
}

export function setupBuilderEventListeners() {
    if (!libraryModal || !builderModal) return;
    
    libraryModal.querySelector('.close-btn').addEventListener('click', closeAllModals);
    builderModal.querySelector('.close-btn').addEventListener('click', closeAllModals);
    
    form.addEventListener('submit', handleFormSubmit);

    chartListContainer.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' && e.target.dataset.chartId) {
            const { chartId, chartTitle, chartType } = e.target.dataset;
            
            const addEvent = new CustomEvent('addChartToGrid', { 
                detail: { chartId, chartTitle, chartType } 
            });
            document.dispatchEvent(addEvent);
            
            closeAllModals();
        }
    });
}
