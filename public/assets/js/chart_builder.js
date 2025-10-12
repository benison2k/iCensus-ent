// public/assets/js/chart_builder.js

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('chartBuilderModal');
    if (!modal) return;

    const openBtn = document.getElementById('addChartBtn');
    const closeBtn = modal.querySelector('.close-btn');
    const addFilterBtn = document.getElementById('addFilterBtn');
    const filterContainer = document.getElementById('filterContainer');
    const form = document.getElementById('chartBuilderForm');
    const basePath = '/iCensus-ent/public';

    // --- Modal Controls ---
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            form.reset();
            filterContainer.innerHTML = ''; // Clear old filters
            modal.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // --- Dynamic Filter Logic ---
    let filterCount = 0;

    const createFilterRow = () => {
        filterCount++;
        const row = document.createElement('div');
        row.classList.add('filter-row');
        row.innerHTML = `
            <select name="filters[${filterCount}][column]" required>
                <option value="purok">Purok</option>
                <option value="gender">Gender</option>
                <option value="civil_status">Civil Status</option>
                <option value="is_pwd">Is PWD?</option>
                 <option value="is_4ps_member">Is 4Ps Member?</option>
            </select>
            <select name="filters[${filterCount}][operator]" required>
                <option value="=">is equal to</option>
                <option value="!=">is not equal to</option>
            </select>
            <input type="text" name="filters[${filterCount}][value]" placeholder="Value (e.g., 1)" required>
            <button type="button" class="btn-remove-filter">&times;</button>
        `;

        filterContainer.appendChild(row);

        row.querySelector('.btn-remove-filter').addEventListener('click', () => {
            row.remove();
        });
    };

    if (addFilterBtn) {
        addFilterBtn.addEventListener('click', createFilterRow);
    }

    // --- AJAX FORM SUBMISSION ---
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch(`${basePath}/charts/save`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert(result.message);
                    modal.style.display = 'none';
                    // In a real application, you would now dynamically add the new chart to the dashboard.
                    // For now, we'll just log it and recommend a refresh.
                    console.log('New chart saved with ID:', result.chart_id);
                    // location.reload(); // Optional: uncomment to refresh the page to show new chart
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Submission failed:', error);
                alert('An unexpected error occurred. Please try again.');
            }
        });
    }
});