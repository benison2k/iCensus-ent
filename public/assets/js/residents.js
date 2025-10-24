// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/residents.js

document.addEventListener('DOMContentLoaded', () => {
    // --- MODAL ELEMENTS ---
    const modal = document.getElementById('residentModal');
    const closeModal = modal.querySelector('.close');
    const form = document.getElementById('residentForm');
    const modalTitle = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = modal.querySelector('.editBtn');
    const deleteBtn = modal.querySelector('.deleteBtn');
    const hiddenId = document.getElementById('resident_id');
    const addResidentBtn = document.getElementById('addResidentBtn');

    // --- TABLE & FILTER ELEMENTS ---
    const residentsTable = document.getElementById('residentsTable');
    const tableBody = document.getElementById('residentsTableBody');
    const searchInput = document.getElementById('searchInput');
    const houseNoFilter = document.getElementById('houseNoFilter');
    const streetFilter = document.getElementById('streetFilter');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const ageMin = document.getElementById('ageMin');
    const ageMax = document.getElementById('ageMax');
    const purokFilter = document.getElementById('purokFilter');
    const householdFilter = document.getElementById('householdFilter');
    const civilStatusFilter = document.getElementById('civilStatusFilter');
    const bloodTypeFilter = document.getElementById('bloodTypeFilter');
    const residencyStatusFilter = document.getElementById('residencyStatusFilter');
    const relationshipFilter = document.getElementById('relationshipFilter');
    const isHeadFilter = document.getElementById('isHeadFilter');
    const birthMonthFilter = document.getElementById('birthMonthFilter');
    const dateAddedMin = document.getElementById('dateAddedMin');
    const dateAddedMax = document.getElementById('dateAddedMax');
    const emergencyContactFilter = document.getElementById('emergencyContactFilter');
    const demographicButtons = document.querySelectorAll('.demographic-btn');
    const isVoterFilter = document.getElementById('isVoterFilter');
    const educationFilter = document.getElementById('educationFilter');
    const occupationFilter = document.getElementById('occupationFilter');
    const employmentStatusFilter = document.getElementById('employmentStatusFilter');
    const isStudentFilter = document.getElementById('isStudentFilter');
    const isPwdFilter = document.getElementById('isPwdFilter');
    const isSoloParentFilter = document.getElementById('isSoloParentFilter');
    const is4psMemberFilter = document.getElementById('is4psMemberFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCountEl');
    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    const basePath = '/iCensus-ent/public';
    
    const filteredResultsDiv = document.getElementById('filteredResults');
    const filteredCountSpan = document.getElementById('filteredCount');
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const advancedFilters = document.getElementById('advanced-filters');
    const nameSortSelect = document.getElementById('nameSortSelect');

    // --- STATE INITIALIZATION (FIXED) ---
    // These variables must be declared here to be accessible by all inner functions (renderTable, applyFilters)
    let currentPage = 1;
    let pageSize = parseInt(pageSizeSelect.value, 10);
    let filteredResidents = [];
    let currentSort = { // FIX: Declaration ensured here
        column: 'last_name',
        order: 'asc'
    };
    
    // --- NEW ELEMENTS FOR TAGS ---
    const activeFiltersContainer = document.getElementById('activeFiltersContainer');

    // Map filter input IDs/names to display labels
    const filterLabels = {
        // Demographics
        genderFilter: 'Gender', civilStatusFilter: 'Civil Status', isHeadFilter: 'Is Head',
        ageMin: 'Age Min', ageMax: 'Age Max', birthMonthFilter: 'Birth Month',
        // Address
        purokFilter: 'Purok', streetFilter: 'Street', houseNoFilter: 'House No.',
        // Household
        householdFilter: 'Head of Household', relationshipFilter: 'Relationship',
        // Welfare & Education
        employmentStatusFilter: 'Employment', isStudentFilter: 'Student', educationFilter: 'Education',
        occupationFilter: 'Occupation', isPwdFilter: 'PWD', isSoloParentFilter: 'Solo Parent',
        is4psMemberFilter: '4Ps Member',
        // Administrative
        statusFilter: 'Status', residencyStatusFilter: 'Residency Type', bloodTypeFilter: 'Blood Type',
        emergencyContactFilter: 'Emergency Contact', dateAddedMin: 'Date Added From', dateAddedMax: 'Date Added To',
        isVoterFilter: 'Is Voter', // Checkbox
        searchInput: 'Search', // Main search bar
    };

    // Function to reset a specific filter element
    const resetFilterElement = (id) => {
        const el = document.getElementById(id);
        if (!el) return;

        if (el.type === 'checkbox') {
            el.checked = false;
        } else if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
            el.value = '';
        }
    };
    
    // Function to display active filters as tags
    const displayActiveFilterTags = () => {
        let tagsHtml = '';
        const activeFilters = [];
        const filterElements = [
            // Map elements to their IDs for easy iteration
            { id: 'genderFilter', type: 'select' }, { id: 'civilStatusFilter', type: 'select' }, { id: 'isHeadFilter', type: 'select' },
            { id: 'ageMin', type: 'input' }, { id: 'ageMax', type: 'input' }, { id: 'birthMonthFilter', type: 'select' },
            { id: 'purokFilter', type: 'select' }, { id: 'streetFilter', type: 'input' }, { id: 'houseNoFilter', type: 'input' },
            { id: 'householdFilter', type: 'select' }, { id: 'relationshipFilter', type: 'select' },
            { id: 'employmentStatusFilter', type: 'select' }, { id: 'isStudentFilter', type: 'select' }, { id: 'educationFilter', type: 'select' },
            { id: 'occupationFilter', type: 'select' }, { id: 'isPwdFilter', type: 'select' }, { id: 'isSoloParentFilter', type: 'select' },
            { id: 'is4psMemberFilter', type: 'select' }, { id: 'statusFilter', type: 'select' }, { id: 'residencyStatusFilter', type: 'select' },
            { id: 'bloodTypeFilter', type: 'select' }, { id: 'emergencyContactFilter', type: 'select' },
            { id: 'dateAddedMin', type: 'input' }, { id: 'dateAddedMax', type: 'input' },
            { id: 'isVoterFilter', type: 'checkbox' }, // Checkbox
            { id: 'searchInput', type: 'input' }, // Main search
        ];

        filterElements.forEach(item => {
            const el = document.getElementById(item.id);
            let value = null;
            let displayValue = null;

            if (!el) return;

            if (item.id === 'ageMin' && el.value && el.value.trim() !== '') {
                // Combine age ranges into a single tag
                if (!document.getElementById('ageMax').value) {
                    value = `${el.value}+`;
                } else if (parseInt(el.value) < parseInt(document.getElementById('ageMax').value)) {
                    value = `${el.value}-${document.getElementById('ageMax').value}`;
                }
                displayValue = value;
            } else if (item.id === 'ageMax' && el.value && el.value.trim() !== '') {
                // Handled by ageMin unless only max is set
                if (!document.getElementById('ageMin').value) {
                    value = `<${el.value}`;
                    displayValue = value;
                }
            } else if (item.id === 'dateAddedMin' && el.value && el.value.trim() !== '') {
                value = `${el.value} to ${document.getElementById('dateAddedMax').value || 'Now'}`;
                displayValue = value;
            } else if (item.id === 'dateAddedMax' && el.value && el.value.trim() !== '') {
                if (!document.getElementById('dateAddedMin').value) {
                    value = `Before ${el.value}`;
                    displayValue = value;
                }
            } else if (item.type === 'checkbox') {
                if (el.checked) {
                    value = '1';
                    displayValue = 'Yes';
                }
            } else if (el.value && el.value.trim() !== '') {
                value = el.value.trim();
                // Handle complex selectors for display value
                if (item.id === 'employmentStatusFilter') {
                    displayValue = value === 'employed' ? 'Employed' : 'Unemployed';
                } else if (item.id === 'isStudentFilter') {
                    displayValue = value === '1' ? 'Yes' : 'No';
                } else if (item.id.startsWith('is')) {
                    displayValue = value === '1' ? 'Yes' : 'No';
                } else if (item.id === 'birthMonthFilter') {
                    displayValue = el.options[el.selectedIndex].textContent;
                } else {
                    displayValue = value;
                }
            }

            // Push active filter if it has a calculated display value and hasn't been merged (like ageMax/dateAddedMax)
            const isHandledByMin = (item.id === 'ageMax' && document.getElementById('ageMin').value) ||
                                   (item.id === 'dateAddedMax' && document.getElementById('dateAddedMin').value);

            if (value !== null && !isHandledByMin) {
                activeFilters.push({ id: item.id, label: filterLabels[item.id], value: displayValue || value });
            }
        });

        if (activeFilters.length > 0) {
            activeFiltersContainer.style.display = 'flex';
            tagsHtml = activeFilters.map(filter => `
                <span class="filter-tag" data-filter-id="${filter.id}">
                    ${filter.label}: ${filter.value}
                    <span class="material-icons remove-filter-tag">close</span>
                </span>
            `).join('');
            activeFiltersContainer.innerHTML = '<span class="active-filters-label">Active Filters:</span>' + tagsHtml;
        } else {
            activeFiltersContainer.style.display = 'none';
            activeFiltersContainer.innerHTML = '';
        }
    };
    
    // --- MODAL & FORM FUNCTIONS (UNCHANGED) ---
    const setFormEditable = (editable) => {
        form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
        saveBtn.style.display = editable ? 'inline-flex' : 'none';
        editBtn.style.display = editable ? 'none' : 'inline-flex';

        if (editable || userRole === 'Encoder') {
            deleteBtn.style.display = 'none';
        } else {
            deleteBtn.style.display = 'inline-flex';
        }
    };

    const openModalForEdit = async (id) => {
        form.reset();
        try {
            const res = await fetch(`${basePath}/residents/process?action=get&resident_id=${id}`);
            const result = await res.json();
            if (result.status !== 'success') return alert('Resident not found.');

            const data = result.resident;
            Object.keys(data).forEach(key => {
                const el = form[key];
                if (el) {
                    if (el.type === 'checkbox') {
                        el.checked = (data[key] == 1);
                    } else {
                        el.value = data[key];
                    }
                }
            });

            setFormEditable(false);
            modalTitle.textContent = `View Resident Info`;
            hiddenId.value = id;
            modal.style.display = 'block';
        } catch (err) {
            console.error('Failed to fetch resident data:', err);
        }
    };

    const openModalForAdd = () => {
        form.reset();
        hiddenId.value = '';
        setFormEditable(true);
        editBtn.style.display = 'none';
        modalTitle.textContent = 'Add New Resident';
        modal.style.display = 'block';
    };

    // --- TABLE & PAGINATION FUNCTIONS (MODIFIED) ---
    const attachEventListenersToRows = () => {
        document.querySelectorAll('.moreBtn').forEach(btn => {
            btn.addEventListener('click', (e) => openModalForEdit(e.currentTarget.dataset.id));
        });
    };

    const renderTable = () => {
        // IMPORTANT: Ensure sorting and slicing are done on the filtered list
        filteredResidents.sort((a, b) => {
            const valA = (a[currentSort.column] || '').toString().toLowerCase();
            const valB = (b[currentSort.column] || '').toString().toLowerCase();
            
            let comparison = valA.localeCompare(valB, undefined, {numeric: true});

            return currentSort.order === 'desc' ? comparison * -1 : comparison;
        });

        tableBody.innerHTML = '';
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageSlice = filteredResidents.slice(start, end); // CORRECTLY uses filteredResidents
        let rowsHtml = '';

        if (filteredResidents.length === 0) {
            rowsHtml = '<tr><td colspan="6" style="text-align: center; height: 380px; vertical-align: middle;">No residents found matching the criteria.</td></tr>';
        } else {
            pageSlice.forEach(r => {
                const middleInitial = r.middle_name ? `${r.middle_name.charAt(0).toUpperCase()}.` : '';
                const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
                const address = `${r.house_no} ${r.street}, Purok ${r.purok}`;
                const safeStatus = (r.status || '').toLowerCase();
                rowsHtml += `
                    <tr data-id="${r.id}">
                        <td>${fullName}</td>
                        <td>${r.age}</td>
                        <td>${r.gender}</td>
                        <td>${address}</td>
                        <td><span class="status-label status-${safeStatus}">${r.status}</span></td>
                        <td><button class="moreBtn material-icons" data-id="${r.id}" title="View Resident Info">more_vert</button></td>
                    </tr>`;
            });
        }

        tableBody.innerHTML = rowsHtml;

        if (filteredResidents.length > 0) {
            const placeholdersNeeded = pageSize - pageSlice.length;
            for (let i = 0; i < placeholdersNeeded; i++) {
                tableBody.innerHTML += '<tr><td colspan="6">&nbsp;</td></tr>';
            }
        }

        updatePagination();
        updateSortIcons();
        attachEventListenersToRows();
    };

    const applyFilters = () => {
        const searchTerm = searchInput.value.toLowerCase();
        const houseNo = houseNoFilter.value.toLowerCase();
        const street = streetFilter.value.toLowerCase();
        const status = statusFilter.value;
        const gender = genderFilter.value;
        // Parse numbers from filter inputs, default to null if empty
        const minAge = ageMin.value ? parseInt(ageMin.value, 10) : null;
        const maxAge = ageMax.value ? parseInt(ageMax.value, 10) : null;

        const purok = purokFilter.value;
        const household = householdFilter.value;
        const civilStatus = civilStatusFilter.value;
        const bloodType = bloodTypeFilter.value;
        const residencyStatus = residencyStatusFilter.value;
        const relationship = relationshipFilter.value;
        const isHead = isHeadFilter.value;
        const birthMonth = birthMonthFilter.value;
        const minDateAdded = dateAddedMin.value;
        const maxDateAdded = dateAddedMax.value;
        const hasEmergency = emergencyContactFilter.value;
        const isVoter = isVoterFilter.checked;
        const education = educationFilter.value;
        const occupation = occupationFilter.value;
        const employmentStatus = employmentStatusFilter.value;
        const isStudent = isStudentFilter.value;
        const isPwd = isPwdFilter.value;
        const isSoloParent = isSoloParentFilter.value;
        const is4ps = is4psMemberFilter.value;

        filteredResidents = allResidentsData.filter(r => {
            const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
            const residentOccupation = (r.occupation || '').trim().toLowerCase();

            // 1. General Search/Text Filters
            if (searchTerm && !fullName.includes(searchTerm)) return false;
            // Ensure resident field exists AND matches filter
            if (houseNo && (!r.house_no || !r.house_no.toString().toLowerCase().includes(houseNo))) return false;
            if (street && (!r.street || !r.street.toLowerCase().includes(street))) return false;
            
            // 2. Age Filters
            if (minAge !== null && (r.age === null || r.age < minAge)) return false;
            if (maxAge !== null && (r.age === null || r.age > maxAge)) return false;

            // 3. Select/Dropdown Filters (Ensures resident value is not null/empty if filter value is set)
            if (status && r.status !== status) return false;
            if (gender && r.gender !== gender) return false;
            if (residencyStatus && r.residency_status !== residencyStatus) return false;

            // Strict checks for fields that might be empty or null in data
            if (purok) {
                if (r.purok === null || r.purok === undefined || r.purok.toString() !== purok) return false;
            }
            if (household) {
                 if (r.head_of_household === null || r.head_of_household === undefined || r.head_of_household !== household) return false;
            }
            if (civilStatus) {
                 if (r.civil_status === null || r.civil_status === undefined || r.civil_status !== civilStatus) return false;
            }
            if (bloodType) {
                 if (r.blood_type === null || r.blood_type === undefined || r.blood_type !== bloodType) return false;
            }
            if (relationship) {
                 if (r.relationship === null || r.relationship === undefined || r.relationship !== relationship) return false;
            }
            if (education) {
                 if (r.educational_attainment === null || r.educational_attainment === undefined || r.educational_attainment !== education) return false;
            }
            if (occupation) {
                 if (r.occupation === null || r.occupation === undefined || residentOccupation !== occupation.toLowerCase()) return false;
            }
            
            // 4. Checkbox/Binary/Complex Filters
            if (isHead === 'Yes' && r.relationship !== 'Self') return false;
            if (isHead === 'No' && r.relationship === 'Self') return false;
            if (birthMonth && (r.dob === null || new Date(r.dob).getMonth() + 1 != birthMonth)) return false;
            
            if (employmentStatus) {
                const isConsideredUnemployed = residentOccupation === '' || residentOccupation === 'unemployed' || residentOccupation === 'n/a' || residentOccupation === 'student';
                if (employmentStatus === 'employed' && isConsideredUnemployed) return false;
                if (employmentStatus === 'unemployed' && !isConsideredUnemployed) return false;
            }

            if (isStudent !== '' && (isStudent === '1' ? residentOccupation !== 'student' : residentOccupation === 'student')) return false;

            if (minDateAdded && r.date_added && r.date_added < minDateAdded) return false;
            if (maxDateAdded && r.date_added && r.date_added.split(' ')[0] > maxDateAdded) return false;
            
            if (hasEmergency === 'Yes' && !r.emergency_name) return false;
            if (hasEmergency === 'No' && r.emergency_name) return false;
            if (isVoter && !r.is_registered_voter) return false;
            if (isPwd !== "" && r.is_pwd != isPwd) return false;
            if (isSoloParent !== "" && r.is_solo_parent != isSoloParent) return false;
            if (is4ps !== "" && r.is_4ps_member != is4ps) return false;

            return true;
        });

        const totalResidents = allResidentsData.length;
        const filteredCount = filteredResidents.length;

        // Display active filters (NEW)
        displayActiveFilterTags();
        
        // Update filtered count indicator
        if (filteredCount < totalResidents) {
            filteredCountSpan.textContent = filteredCount;
            filteredResultsDiv.style.display = 'block';
        } else {
            filteredResultsDiv.style.display = 'none';
        }

        currentPage = 1;
        renderTable();
    };

    const updatePagination = () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize) || 1;
        const startItem = filteredResidents.length === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const endItem = Math.min(currentPage * pageSize, filteredResidents.length);
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        shownCountEl.textContent = `${startItem}–${endItem}`;
        totalCountEl.textContent = filteredResidents.length;
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    };

    const updateSortIcons = () => {
        document.querySelectorAll('.sort-icon').forEach(icon => icon.innerHTML = '');
        
        let activeHeader;
        if (currentSort.column === 'first_name') {
            activeHeader = document.querySelector('th[data-sort="last_name"]');
        } else {
            activeHeader = document.querySelector(`th[data-sort="${currentSort.column}"]`);
        }

        if (activeHeader) {
            let indicator = '';
            if (currentSort.column === 'age') {
                indicator = `<span class="material-icons">${currentSort.order === 'asc' ? 'arrow_upward' : 'arrow_downward'}</span>`;
            } else if (currentSort.column === 'last_name' || currentSort.column === 'first_name') {
                const sortOrderText = currentSort.order === 'asc' ? '(A-Z)' : '(Z-A)';
                const sortColumnText = `(by ${currentSort.column === 'first_name' ? 'First' : 'Last'})`;
                indicator = `<span class="sort-text">${sortColumnText} ${sortOrderText}</span>`;
            }
            activeHeader.querySelector('.sort-icon').innerHTML = indicator;
        }
        
        // Sync dropdown with current sort state
        if (nameSortSelect) {
            nameSortSelect.value = `${currentSort.column}-${currentSort.order}`;
        }
    };

    const jumpToPage = () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize) || 1;
        const page = parseInt(gotoPageInput.value, 10);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        } else {
            alert(`Please enter a page number between 1 and ${totalPages}.`);
        }
        gotoPageInput.value = '';
    };

    // --- ALL EVENT LISTENERS ---
    addResidentBtn.addEventListener('click', openModalForAdd);
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    editBtn.addEventListener('click', () => setFormEditable(true));

    const ajaxModal = document.getElementById('ajaxResultModal');
    const ajaxMessage = document.getElementById('ajaxResultMessage');
    const ajaxModalContent = ajaxModal.querySelector('.modal-content');
    const ajaxCloseBtn = ajaxModal.querySelector('.close');

    if (ajaxCloseBtn) {
        ajaxCloseBtn.onclick = () => ajaxModal.style.display = "none";
    }
    window.onclick = (event) => { if (event.target === ajaxModal) ajaxModal.style.display = "none"; };

    function showAjaxResult(message, type = 'success') {
        ajaxMessage.textContent = message;
        ajaxModalContent.className = 'modal-content ' + type;
        ajaxModal.style.display = 'block';
        setTimeout(() => {
            ajaxModal.style.display = "none";
            window.location.reload();
        }, 2000);
    }

    async function handleFormSubmit(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (response.ok && result.status === 'success') {
                modal.style.display = 'none';
                showAjaxResult(result.message || 'Resident saved successfully!', 'success');
            } else {
                alert(result.message || 'An error occurred.');
            }
        } catch (error) {
            alert('A network error occurred. Please try again.');
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this);
    });

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            const id = hiddenId.value;
            if (!id) return;
            if (!confirm('Are you sure you want to delete this resident? This action cannot be undone.')) return;
    
            const response = await fetch(`${basePath}/residents/process`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'delete', id: id })
            });
            const result = await response.json();
            if (result.status === 'success') {
                modal.style.display = 'none';
                showAjaxResult(result.message || 'Resident deleted successfully.', 'success');
            } else {
                alert(result.message || 'Failed to delete resident.');
            }
        });
    }

    toggleFiltersBtn.addEventListener('click', () => {
        const isExpanded = advancedFilters.style.display === 'grid';
        advancedFilters.style.display = isExpanded ? 'none' : 'grid';
        toggleFiltersBtn.classList.toggle('expanded', !isExpanded);
    });

    window.addEventListener('click', (e) => {
        const filterWrapper = document.querySelector('.filter-wrapper');
        if (filterWrapper && !filterWrapper.contains(e.target) && advancedFilters.style.display === 'grid') {
            advancedFilters.style.display = 'none';
            toggleFiltersBtn.classList.remove('expanded');
        }
    });

    // Consolidate input/change listeners to trigger filtering and tag display
    const filterInputs = [
        searchInput, houseNoFilter, streetFilter, ageMin, ageMax, dateAddedMin, dateAddedMax,
        statusFilter, genderFilter, purokFilter, householdFilter, civilStatusFilter, bloodTypeFilter, 
        residencyStatusFilter, relationshipFilter, isHeadFilter, birthMonthFilter, emergencyContactFilter, 
        isVoterFilter, educationFilter, occupationFilter, employmentStatusFilter, isStudentFilter, 
        isPwdFilter, isSoloParentFilter, is4psMemberFilter
    ];
    
    filterInputs.forEach(el => {
        if(el) {
            const eventType = (el.tagName === 'INPUT' && el.type === 'text' || el.type === 'search' || el.type === 'number') ? 'input' : 'change';
            el.addEventListener(eventType, applyFilters);
        }
    });

    demographicButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            ageMin.value = btn.dataset.min || '';
            ageMax.value = btn.dataset.max || '';
            applyFilters();
        });
    });

    if(houseNoFilter) houseNoFilter.addEventListener('input', function() { this.value = this.value.replace(/\D/g, ''); });
    if(streetFilter) streetFilter.addEventListener('input', function() { this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); });

    pageSizeSelect.addEventListener('change', (e) => {
        pageSize = parseInt(e.target.value, 10);
        currentPage = 1;
        renderTable();
    });
    prevPageBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTable(); } });
    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredResidents.length / pageSize);
        if (currentPage < totalPages) { currentPage++; renderTable(); }
    });
    gotoPageBtn.addEventListener('click', jumpToPage);
    gotoPageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') jumpToPage(); });
    
    clearBtn.addEventListener('click', () => {
        // Clear all elements using resetFilterElement
        filterInputs.forEach(el => { if(el) resetFilterElement(el.id); });
        applyFilters();
    });
    
    // NEW: Add delegated event listener to the container to handle tag removal
    activeFiltersContainer.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-filter-tag');
        if (removeBtn) {
            const tag = removeBtn.closest('.filter-tag');
            const filterId = tag.dataset.filterId;
            
            // 1. Reset the corresponding filter element(s)
            if (filterId === 'ageMin' || filterId === 'ageMax' || filterId === 'dateAddedMin' || filterId === 'dateAddedMax') {
                 // Clear both linked range fields
                 resetFilterElement('ageMin');
                 resetFilterElement('ageMax');
                 resetFilterElement('dateAddedMin');
                 resetFilterElement('dateAddedMax');
            } else {
                 resetFilterElement(filterId);
            }

            // 2. Re-run filters and update tags
            applyFilters();
        }
    });

    if (residentsTable) {
        residentsTable.querySelector('thead').addEventListener('click', (e) => {
            const header = e.target.closest('.sortable');
            // If the user clicks the dropdown overlay, don't trigger the cycle sort
            if (header && !e.target.matches('.sort-select-overlay')) {
                const sortColumn = header.dataset.sort;

                if (sortColumn === 'last_name') {
                    if (currentSort.column === 'last_name') {
                        if (currentSort.order === 'asc') {
                            currentSort.order = 'desc';
                        } else {
                            currentSort.column = 'first_name';
                            currentSort.order = 'asc';
                        }
                    } else if (currentSort.column === 'first_name') {
                        if (currentSort.order === 'asc') {
                            currentSort.order = 'desc';
                        } else {
                            currentSort.column = 'last_name';
                            currentSort.order = 'asc';
                        }
                    } else {
                        currentSort.column = 'last_name';
                        currentSort.order = 'asc';
                    }
                } else {
                    if (currentSort.column === sortColumn) {
                        currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = sortColumn;
                        currentSort.order = 'asc';
                    }
                }
                
                renderTable();
            }
        });
    }

    if (nameSortSelect) {
        nameSortSelect.addEventListener('change', (e) => {
            const [column, order] = e.target.value.split('-');
            currentSort.column = column;
            currentSort.order = order;
            renderTable();
        });
    }

    // --- INITIALIZATION (FINAL ROBUST FIX) ---
    // The core initialization block to guarantee the table is populated correctly on page load
    if (typeof allResidentsData !== 'undefined' && allResidentsData.length >= 0) {
        // 1. Initialize filteredResidents with the full dataset first.
        filteredResidents = allResidentsData; 

        if (!isPendingView) {
            // 2. Approved View: Run filters (which runs renderTable inside)
            applyFilters(); 
        } else {
            // 3. Pending View: Directly render the full list of pending entries.
            renderTable(); 
        }
    } else if (typeof allResidentsData !== 'undefined' && allResidentsData.length === 0) {
        // Handle case where no residents exist at all
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No residents found in this view.</td></tr>';
        updatePagination();
    }
    
    // --- NEW: Accordion Logic for Advanced Filters ---
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        header.addEventListener('click', () => {
            const currentlyActive = document.querySelector('.accordion-item.active');
            if (currentlyActive && currentlyActive !== item) {
                currentlyActive.classList.remove('active');
                currentlyActive.querySelector('.accordion-content').style.maxHeight = 0;
            }
    
            item.classList.toggle('active');
            const content = item.querySelector('.accordion-content');
            if (item.classList.contains('active')) {
                content.style.maxHeight = content.scrollHeight + "px";
            } else {
                content.style.maxHeight = 0;
            }
        });
    });
});