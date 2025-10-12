// public/assets/js/chart_builder.js

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('chartBuilderModal');
    if (!modal) return;

    const openBtn = document.getElementById('addChartBtn');
    const closeBtn = modal.querySelector('.close-btn');
    const addFilterBtn = document.getElementById('addFilterBtn');
    const filterContainer = document.getElementById('filterContainer');
    const form = document.getElementById('chartBuilderForm');
    const chartPreviewDiv = document.getElementById('chartPreview'); // New element for the preview
    const basePath = '/iCensus-ent/public';

    // --- Modal Controls ---
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            form.reset();
            filterContainer.innerHTML = '';
            // Reset the preview area when opening the modal
            if(chartPreviewDiv) {
                chartPreviewDiv.innerHTML = '<div class="chart-placeholder">Adjust the settings on the left to see a preview.</div>';
            }
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
        updateChartPreview(); // Trigger preview update after applying template
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
                <option value="educational_attainment">Educational Attainment</option>
                <option value="occupation">Occupation</option>
                <option value="ownership_status">Ownership Status</option>
                <option value="blood_type">Blood Type</option>
                <option value="nationality">Nationality</option>
                <option value="relationship">Relationship to Head</option>
                <option value="residency_status">Residency Status</option>
                <option value="status">Resident Status</option>
                <option value="is_pwd">Is PWD?</option>
                <option value="is_4ps_member">Is 4Ps Member?</option>
                <option value="is_registered_voter">Is Registered Voter?</option>
                <option value="is_solo_parent">Is Solo Parent?</option>
                <option value="is_indigent">Is Indigent?</option>
            </select>
            <select name="filters[${filterCount}][operator]" required>
                <option value="=">is equal to</option>
                <option value="!=">is not equal to</option>
                <option value=">">is greater than</option>
                <option value="<">is less than</option>
                <option value=">=">is greater than or equal to</option>
                <option value="<=">is less than or equal to</option>
            </select>
            <input type="text" name="filters[${filterCount}][value]" placeholder="Value (e.g., 1 for Yes)" required>
            <button type="button" class="btn-remove-filter">&times;</button>
        `;
        filterContainer.appendChild(row);

        // Add event listeners to new elements to trigger preview
        row.querySelector('select').addEventListener('change', debouncedUpdate);
        row.querySelector('input').addEventListener('keyup', debouncedUpdate);
        
        row.querySelector('.btn-remove-filter').addEventListener('click', () => {
            row.remove();
            debouncedUpdate(); // Update preview after removing a filter
        });
    };

    if (addFilterBtn) {
        addFilterBtn.addEventListener('click', createFilterRow);
    }

    // --- START: LIVE PREVIEW LOGIC ---
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    async function updateChartPreview() {
        if (!chartPreviewDiv) return;

        const formData = new FormData(form);
        const chartType = formData.get('chart_type');

        // Show a loading state
        chartPreviewDiv.innerHTML = '<div class="chart-placeholder">Generating preview...</div>';

        try {
            const response = await fetch(`${basePath}/charts/preview`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                drawPreviewChart(chartType, result.data);
            } else {
                chartPreviewDiv.innerHTML = `<div class="chart-error">${result.message || 'Could not load preview.'}</div>`;
            }
        } catch (error) {
            console.error("Preview failed:", error);
            chartPreviewDiv.innerHTML = `<div class="chart-error">An error occurred while generating the preview.</div>`;
        }
    }

    function drawPreviewChart(chartType, chartData) {
        if (!google || !google.visualization) {
            console.error("Google Charts not loaded yet.");
            chartPreviewDiv.innerHTML = `<div class="chart-error">Google Charts library not ready.</div>`;
            return;
        }

        const options = {
            width: '100%',
            height: '100%',
            backgroundColor: 'transparent',
            chartArea: { 'width': '85%', 'height': '70%' },
            legend: { position: 'bottom' }
        };

        if (chartType === 'KPI') {
            chartPreviewDiv.innerHTML = `<div class="kpi-preview-content"><div class="kpi-preview-value">${chartData.value || 0}</div></div>`;
            return;
        }
        
        const dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Category');
        dataTable.addColumn('number', 'Value');

        if (chartData && Object.keys(chartData).length > 0) {
            const rows = Object.entries(chartData).map(([key, value]) => [key, value]);
            dataTable.addRows(rows);
        } else {
            chartPreviewDiv.innerHTML = `<div class="chart-placeholder">No data for the selected criteria.</div>`;
            return;
        }

        let chart;
        switch (chartType) {
            case 'BarChart':
                chart = new google.visualization.BarChart(chartPreviewDiv);
                break;
            case 'ColumnChart':
                chart = new google.visualization.ColumnChart(chartPreviewDiv);
                break;
            case 'DonutChart':
                options.pieHole = 0.4;
                chart = new google.visualization.PieChart(chartPreviewDiv);
                break;
            case 'PieChart':
            default:
                chart = new google.visualization.PieChart(chartPreviewDiv);
                break;
        }
        chart.draw(dataTable, options);
    }
    
    // Create a debounced version of the update function
    const debouncedUpdate = debounce(updateChartPreview, 500);

    // Attach listener to the form for any changes
    form.addEventListener('change', debouncedUpdate);
    form.addEventListener('keyup', debouncedUpdate);
    // --- END: LIVE PREVIEW LOGIC ---

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