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
            filterContainer.innerHTML = '';
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

    // --- EXPANDED PRE-MADE CHART TEMPLATE LOGIC ---
    const templates = {
        gender_pie: {
            title: 'Population by Gender',
            chart_type: 'PieChart',
            aggregate_function: 'COUNT',
            group_by_column: 'gender'
        },
        purok_bar: {
            title: 'Population by Purok',
            chart_type: 'BarChart',
            aggregate_function: 'COUNT',
            group_by_column: 'purok'
        },
        age_brackets: {
            title: 'Population by Age Bracket',
            chart_type: 'ColumnChart',
            aggregate_function: 'COUNT',
            group_by_column: 'dob'
        },
        pwd_pie: {
            title: 'PWD Residents',
            chart_type: 'PieChart',
            aggregate_function: 'COUNT',
            group_by_column: 'is_pwd'
        },
        civil_status_pie: {
            title: 'Civil Status Distribution',
            chart_type: 'PieChart',
            aggregate_function: 'COUNT',
            group_by_column: 'civil_status'
        },
        four_ps_pie: {
            title: '4Ps Beneficiaries',
            chart_type: 'PieChart',
            aggregate_function: 'COUNT',
            group_by_column: 'is_4ps_member'
        },
        education_bar: {
            title: 'Educational Attainment',
            chart_type: 'BarChart',
            aggregate_function: 'COUNT',
            group_by_column: 'educational_attainment'
        },
        avg_age_kpi: {
            title: 'Average Age of Residents',
            chart_type: 'KPI',
            aggregate_function: 'AVG',
            group_by_column: '' // No grouping for KPI
        }
    };

    const applyTemplate = (templateName) => {
        const template = templates[templateName];
        if (!template) return;

        form.querySelector('#chartTitle').value = template.title;
        form.querySelector('#chartType').value = template.chart_type;
        form.querySelector('#aggregateFunction').value = template.aggregate_function;
        form.querySelector('#groupByColumn').value = template.group_by_column;
        
        filterContainer.innerHTML = ''; // Clear custom filters
    };

    modal.querySelectorAll('.btn-template').forEach(button => {
        button.addEventListener('click', (e) => {
            applyTemplate(e.currentTarget.dataset.template);
        });
    });
    // --- END of TEMPLATE LOGIC ---


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
            <input type="text" name="filters[${filterCount}][value]" placeholder="Value (e.g., 1 for Yes)" required>
            <button type="button" class="btn-remove-filter">&times;</button>
        `;
        filterContainer.appendChild(row);
        row.querySelector('.btn-remove-filter').addEventListener('click', () => row.remove());
    };

    if (addFilterBtn) {
        addFilterBtn.addEventListener('click', createFilterRow);
    }

    // --- AJAX FORM SUBMISSION ---
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const saveButton = document.getElementById('saveChartBtn');
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';

            try {
                const response = await fetch(`${basePath}/charts/save`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    modal.style.display = 'none';

                    const newChartDef = {
                        id: result.chart_id,
                        title: formData.get('title'),
                        chart_type: formData.get('chart_type')
                    };

                    if (window.addChartToDashboard) {
                        window.addChartToDashboard(newChartDef);
                    } else {
                        alert('Chart saved successfully! Refreshing page to show new chart.');
                        location.reload();
                    }

                } else {
                    alert('Error: ' + (result.message || 'Could not save the chart.'));
                }
            } catch (error) {
                console.error('Submission failed:', error);
                alert('An unexpected error occurred. Please try again.');
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = 'Save Chart';
            }
        });
    }
});

