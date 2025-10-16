// public/assets/js/chart_builder.js

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('chartBuilderModal');
    if (!modal) return;

    const openBtn = document.getElementById('addChartBtn');
    const closeBtn = modal.querySelector('.close-btn');
    const addFilterBtn = document.getElementById('addFilterBtn');
    const filterContainer = document.getElementById('filterContainer');
    const form = document.getElementById('chartBuilderForm');
    const chartPreviewDiv = document.getElementById('chartPreview');
    const basePath = '/iCensus-ent/public';

    // --- Modal Controls ---
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            form.reset();
            // Clear any existing chart_id hidden input
            const existingIdInput = form.querySelector('#chart_id');
            if(existingIdInput) existingIdInput.remove();
            
            filterContainer.innerHTML = '';
            if (chartPreviewDiv) {
                chartPreviewDiv.innerHTML = '<div class="chart-placeholder">Adjust the settings on the left to see a preview.</div>';
            }
            modal.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => (modal.style.display = 'none'));
    }
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.style.display = 'none';
    });

    // --- Template Logic ---
    const templates = {
        gender_pie: { title: 'Population by Gender', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'gender' },
        purok_bar: { title: 'Population by Purok', chart_type: 'BarChart', aggregate_function: 'COUNT', group_by_column: 'purok' },
        age_brackets: { title: 'Population by Age Bracket', chart_type: 'ColumnChart', aggregate_function: 'COUNT', group_by_column: 'dob' },
        pwd_pie: { title: 'PWD Residents', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'is_pwd' },
        civil_status_pie: { title: 'Civil Status Distribution', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'civil_status' },
        four_ps_pie: { title: '4Ps Beneficiaries', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'is_4ps_member' },
        education_bar: { title: 'Educational Attainment', chart_type: 'BarChart', aggregate_function: 'COUNT', group_by_column: 'educational_attainment' },
        avg_age_kpi: { title: 'Average Age of Residents', chart_type: 'KPI', aggregate_function: 'AVG', group_by_column: '' },
        voter_pie: { title: 'Voter Population', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'is_registered_voter' },
        student_pie: { title: 'Student Population', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'occupation', filters: [{ column: 'occupation', operator: '=', value: 'Student' }] },
        solo_parent_pie: { title: 'Solo Parents', chart_type: 'PieChart', aggregate_function: 'COUNT', group_by_column: 'is_solo_parent' },
        occupation_bar: { title: 'Top 15 Occupations', chart_type: 'BarChart', aggregate_function: 'COUNT', group_by_column: 'occupation' },
    };

    const applyTemplate = (templateName) => {
        const template = templates[templateName];
        if (!template) return;
        form.querySelector('#chartTitle').value = template.title;
        form.querySelector('#chartType').value = template.chart_type;
        form.querySelector('#aggregateFunction').value = template.aggregate_function;
        form.querySelector('#groupByColumn').value = template.group_by_column;
        filterContainer.innerHTML = '';

        if (template.filters) {
            template.filters.forEach(filter => {
                createFilterRow(filter);
            });
        }
        debouncedUpdate();
    };

    modal.querySelectorAll('.btn-template').forEach(button => {
        button.addEventListener('click', (e) => applyTemplate(e.currentTarget.dataset.template));
    });

    // --- DYNAMIC FILTER LOGIC (MODIFIED) ---
    let filterCount = 0;

    const filterOptions = {
        gender: ['Male', 'Female'],
        civil_status: ['Single', 'Married', 'Widowed', 'Separated'],
        status: ['Active', 'Inactive', 'Moved', 'Deceased'],
        ownership_status: ['Owned', 'Rented', 'Living with Relatives'],
        employment_status: ['Employed', 'Unemployed'],
        educational_attainment: [
            'No Formal Education', 'Pre-school', 'Elementary Level', 'Elementary Graduate', 
            'High School Level', 'High School Graduate', 'Vocational Graduate', 
            'College Level', 'College Graduate', 'Doctorate Degree'
        ],
        blood_type: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        is_pwd: ['Yes', 'No'],
        is_student: ['Yes', 'No'],
        is_4ps_member: ['Yes', 'No'],
        is_registered_voter: ['Yes', 'No'],
        is_solo_parent: ['Yes', 'No'],
        is_indigent: ['Yes', 'No']
    };

    const updateValueInput = (columnSelect, valueContainer, preselectedValue = null) => {
        const selectedColumn = columnSelect.value;
        const options = filterOptions[selectedColumn];
        const filterName = valueContainer.dataset.name;

        valueContainer.innerHTML = '';

        if (options) {
            const select = document.createElement('select');
            select.name = filterName;
            options.forEach(opt => {
                const optionEl = document.createElement('option');
                if (selectedColumn.startsWith('is_') || selectedColumn === 'is_student') {
                    optionEl.value = (opt === 'Yes' ? '1' : '0');
                } else if (selectedColumn === 'employment_status') {
                    optionEl.value = opt.toLowerCase();
                } else {
                    optionEl.value = opt;
                }
                optionEl.textContent = opt;
                if (preselectedValue && optionEl.value === preselectedValue) {
                    optionEl.selected = true;
                }
                select.appendChild(optionEl);
            });
            valueContainer.appendChild(select);
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = filterName;
            input.placeholder = (selectedColumn === 'purok') ? 'e.g., 3' : 'Enter value';
            if (preselectedValue) {
                input.value = preselectedValue;
            }
            valueContainer.appendChild(input);
        }
    };

    const createFilterRow = (filterData = null) => {
        filterCount++;
        const row = document.createElement('div');
        row.classList.add('filter-row');

        const columnSelect = document.createElement('select');
        columnSelect.name = `filters[${filterCount}][column]`;
        columnSelect.required = true;
        columnSelect.innerHTML = `
            <option value="purok">Purok</option>
            <option value="gender">Gender</option>
            <option value="civil_status">Civil Status</option>
            <option value="employment_status">Employment Status</option>
            <option value="is_student">Is Student?</option>
            <option value="status">Resident Status</option>
            <option value="ownership_status">Ownership Status</option>
            <option value="educational_attainment">Educational Attainment</option>
            <option value="blood_type">Blood Type</option>
            <option value="is_pwd">Is PWD?</option>
            <option value="is_4ps_member">Is 4Ps Member?</option>
            <option value="is_registered_voter">Is Registered Voter?</option>
            <option value="is_solo_parent">Is Solo Parent?</option>
            <option value="is_indigent">Is Indigent?</option>
            <option value="occupation">Occupation</option>
            <option value="nationality">Nationality</option>
        `;
        
        const operatorSelect = document.createElement('select');
        operatorSelect.name = `filters[${filterCount}][operator]`;
        operatorSelect.required = true;
        operatorSelect.innerHTML = `
            <option value="=">is equal to</option>
            <option value="!=">is not equal to</option>
        `;

        const valueContainer = document.createElement('div');
        valueContainer.classList.add('value-container');
        valueContainer.dataset.name = `filters[${filterCount}][value]`;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.classList.add('btn-remove-filter');
        removeBtn.innerHTML = '&times;';
        
        row.append(columnSelect, operatorSelect, valueContainer, removeBtn);
        filterContainer.appendChild(row);

        if (filterData) {
            columnSelect.value = filterData.column;
            operatorSelect.value = filterData.operator;
            updateValueInput(columnSelect, valueContainer, filterData.value);
        } else {
            updateValueInput(columnSelect, valueContainer);
        }

        columnSelect.addEventListener('change', () => updateValueInput(columnSelect, valueContainer));

        removeBtn.addEventListener('click', () => {
            row.remove();
            debouncedUpdate();
        });

        row.addEventListener('change', debouncedUpdate);
        row.addEventListener('keyup', debouncedUpdate);
    };

    if (addFilterBtn) {
        addFilterBtn.addEventListener('click', () => createFilterRow());
    }
    
    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    const updateChartPreview = async () => {
        if (!chartPreviewDiv) return;
        chartPreviewDiv.innerHTML = '<div class="chart-placeholder">Generating preview...</div>';

        await window.googleChartsPromise;
        
        const formData = new FormData(form);
        const chartType = formData.get('chart_type');

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
            chartPreviewDiv.innerHTML = `<div class="chart-error">An error occurred.</div>`;
        }
    };
    
    const drawPreviewChart = (chartType, chartData) => {
        const options = {
            width: '100%', height: '100%', backgroundColor: 'transparent',
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
            case 'BarChart': chart = new google.visualization.BarChart(chartPreviewDiv); break;
            case 'ColumnChart': chart = new google.visualization.ColumnChart(chartPreviewDiv); break;
            case 'DonutChart': options.pieHole = 0.4; chart = new google.visualization.PieChart(chartPreviewDiv); break;
            default: chart = new google.visualization.PieChart(chartPreviewDiv); break;
        }
        chart.draw(dataTable, options);
    };
    
    const debouncedUpdate = debounce(updateChartPreview, 500);
    form.addEventListener('change', debouncedUpdate);
    form.addEventListener('keyup', debouncedUpdate);

    // --- AJAX FORM SUBMISSION ---
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const saveButton = document.getElementById('saveChartBtn');
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';
            
            const chartId = formData.get('chart_id');
            const isUpdate = !!chartId;
            const url = isUpdate ? `${basePath}/charts/update` : `${basePath}/charts/save`;

            try {
                const response = await fetch(url, { method: 'POST', body: formData });
                const result = await response.json();

                if (result.status === 'success') {
                    modal.style.display = 'none';
                    if (isUpdate) {
                        if (window.redrawChartInPlace) {
                            window.redrawChartInPlace(result.chart.id, result.chart);
                        }
                    } else {
                        if (formData.get('add_to_dashboard') && window.addChartToDashboard) {
                            const newChartDef = result.chart;
                            const visibleChartIds = JSON.parse(localStorage.getItem('visibleChartIds')) || [];
                            visibleChartIds.push(newChartDef.id.toString());
                            localStorage.setItem('visibleChartIds', JSON.stringify(visibleChartIds));
                            window.addChartToDashboard(newChartDef);
                        }
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