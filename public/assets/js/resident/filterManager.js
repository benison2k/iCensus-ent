// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/resident/filterManager.js

import { renderTable } from './tableManager.js';

function applyFilters(state) {
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
    const isVoterFilter = document.getElementById('isVoterFilter');
    const educationFilter = document.getElementById('educationFilter');
    const occupationFilter = document.getElementById('occupationFilter');
    const employmentStatusFilter = document.getElementById('employmentStatusFilter');
    const isStudentFilter = document.getElementById('isStudentFilter');
    const isPwdFilter = document.getElementById('isPwdFilter');
    const isSoloParentFilter = document.getElementById('isSoloParentFilter');
    const is4psMemberFilter = document.getElementById('is4psMemberFilter');
    const isIndigentFilter = document.getElementById('isIndigentFilter');
    const nationalityFilter = document.getElementById('nationalityFilter');
    const ownershipStatusFilter = document.getElementById('ownershipStatusFilter');


    const searchTerm = searchInput.value.toLowerCase();
    const houseNo = houseNoFilter.value.toLowerCase();
    const street = streetFilter.value.toLowerCase();
    const status = statusFilter.value;
    const gender = genderFilter.value;
    const minAge = ageMin.value ? parseInt(ageMin.value, 10) : null;
    const maxAge = ageMax.value ? parseInt(ageMax.value, 10) : null;
    const purok = purokFilter.value;
    const household = householdFilter.value;
    const civilStatus = civilStatusFilter.value;
    const bloodType = bloodTypeFilter.value;
    const residencyStatus = residencyStatusFilter.value;
    const relationship = relationshipFilter.value;
    const birthMonth = birthMonthFilter.value;
    const minDateAdded = dateAddedMin.value;
    const maxDateAdded = dateAddedMax.value;
    const education = educationFilter.value;
    const occupation = occupationFilter.value;
    const employmentStatus = employmentStatusFilter.value;
    const nationality = nationalityFilter.value;
    const ownershipStatus = ownershipStatusFilter.value;

    // Read boolean values from checkboxes
    const isHead = isHeadFilter.checked;
    const hasEmergency = emergencyContactFilter.checked;
    const isVoter = isVoterFilter.checked;
    const isStudent = isStudentFilter.checked;
    const isPwd = isPwdFilter.checked;
    const isSoloParent = isSoloParentFilter.checked;
    const is4ps = is4psMemberFilter.checked;
    const isIndigent = isIndigentFilter.checked;


    state.filteredResidents = state.allResidents.filter(r => {
        const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
        const residentOccupation = (r.occupation || '').trim().toLowerCase();

        // Text & Dropdown filters
        if (searchTerm && !fullName.includes(searchTerm)) return false;
        if (houseNo && (!r.house_no || !r.house_no.toString().toLowerCase().includes(houseNo))) return false;
        if (street && (!r.street || !r.street.toLowerCase().includes(street))) return false;
        if (minAge !== null && (r.age === null || r.age < minAge)) return false;
        if (maxAge !== null && (r.age === null || r.age > maxAge)) return false;
        if (status && r.status !== status) return false;
        if (gender && r.gender !== gender) return false;
        if (residencyStatus && r.residency_status !== residencyStatus) return false;
        if (purok && (r.purok === null || r.purok === undefined || r.purok.toString() !== purok)) return false;
        if (household && (r.head_of_household === null || r.head_of_household === undefined || r.head_of_household !== household)) return false;
        if (civilStatus && (r.civil_status === null || r.civil_status === undefined || r.civil_status !== civilStatus)) return false;
        if (bloodType && (r.blood_type === null || r.blood_type === undefined || r.blood_type !== bloodType)) return false;
        if (relationship && (r.relationship === null || r.relationship === undefined || r.relationship !== relationship)) return false;
        if (education && (r.educational_attainment === null || r.educational_attainment === undefined || r.educational_attainment !== education)) return false;
        if (occupation && (r.occupation === null || r.occupation === undefined || residentOccupation !== occupation.toLowerCase())) return false;
        if (birthMonth && (r.dob === null || new Date(r.dob).getMonth() + 1 != birthMonth)) return false;
        if (minDateAdded && r.date_added && r.date_added < minDateAdded) return false;
        if (maxDateAdded && r.date_added && r.date_added.split(' ')[0] > maxDateAdded) return false;
        if (nationality && r.nationality !== nationality) return false;
        if (ownershipStatus && r.ownership_status !== ownershipStatus) return false;
        if (employmentStatus) {
            const isConsideredUnemployed = residentOccupation === '' || residentOccupation === 'unemployed' || residentOccupation === 'n/a' || residentOccupation === 'student';
            if (employmentStatus === 'employed' && isConsideredUnemployed) return false;
            if (employmentStatus === 'unemployed' && !isConsideredUnemployed) return false;
        }

        // Checkbox/Switch filters (only apply if checked)
        if (isHead && r.relationship !== 'Self') return false;
        if (hasEmergency && !r.emergency_name) return false;
        if (isVoter && (r.is_registered_voter == 0 || r.is_registered_voter == null)) return false;
        if (isStudent && residentOccupation !== 'student') return false;
        if (isPwd && (r.is_pwd == 0 || r.is_pwd == null)) return false;
        if (isSoloParent && (r.is_solo_parent == 0 || r.is_solo_parent == null)) return false;
        if (is4ps && (r.is_4ps_member == 0 || r.is_4ps_member == null)) return false;
        if (isIndigent && (r.is_indigent == 0 || r.is_indigent == null)) return false;

        return true;
    });

    const filteredResultsDiv = document.getElementById('filteredResults');
    const filteredCountSpan = document.getElementById('filteredCount');
    const totalResidents = state.allResidents.length;
    const filteredCount = state.filteredResidents.length;

    displayActiveFilterTags();
    
    if (filteredCount < totalResidents) {
        filteredCountSpan.textContent = filteredCount;
        filteredResultsDiv.style.display = 'block';
    } else {
        filteredResultsDiv.style.display = 'none';
    }

    state.currentPage = 1;
    renderTable(state);
};

function initializeFilters(state) {
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
    const isVoterFilter = document.getElementById('isVoterFilter');
    const educationFilter = document.getElementById('educationFilter');
    const occupationFilter = document.getElementById('occupationFilter');
    const employmentStatusFilter = document.getElementById('employmentStatusFilter');
    const isStudentFilter = document.getElementById('isStudentFilter');
    const isPwdFilter = document.getElementById('isPwdFilter');
    const isSoloParentFilter = document.getElementById('isSoloParentFilter');
    const is4psMemberFilter = document.getElementById('is4psMemberFilter');
    const isIndigentFilter = document.getElementById('isIndigentFilter');
    const nationalityFilter = document.getElementById('nationalityFilter');
    const ownershipStatusFilter = document.getElementById('ownershipStatusFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const demographicButtons = document.querySelectorAll('.demographic-btn');
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const advancedFilters = document.getElementById('advanced-filters');
    const activeFiltersContainer = document.getElementById('activeFiltersContainer');

    const filterInputs = [
        searchInput, houseNoFilter, streetFilter, ageMin, ageMax, dateAddedMin, dateAddedMax,
        statusFilter, genderFilter, purokFilter, householdFilter, civilStatusFilter, bloodTypeFilter, 
        residencyStatusFilter, relationshipFilter, isHeadFilter, birthMonthFilter, emergencyContactFilter, 
        isVoterFilter, educationFilter, occupationFilter, employmentStatusFilter, isStudentFilter, 
        isPwdFilter, isSoloParentFilter, is4psMemberFilter, isIndigentFilter, nationalityFilter, ownershipStatusFilter
    ];
    
    filterInputs.forEach(el => {
        if(el) {
            const eventType = (el.tagName === 'INPUT' && ['text', 'search', 'number', 'date'].includes(el.type)) ? 'input' : 'change';
            el.addEventListener(eventType, () => applyFilters(state));
        }
    });

    demographicButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            ageMin.value = btn.dataset.min || '';
            ageMax.value = btn.dataset.max || '';
            applyFilters(state);
        });
    });

    if(houseNoFilter) houseNoFilter.addEventListener('input', function() { this.value = this.value.replace(/\D/g, ''); });
    if(streetFilter) streetFilter.addEventListener('input', function() { this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); });

    clearBtn.addEventListener('click', () => {
        filterInputs.forEach(el => { if(el) resetFilterElement(el.id); });
        applyFilters(state);
    });

    const closeAdvancedFilters = () => {
        if (advancedFilters.style.display !== 'grid') return;
        advancedFilters.classList.add('fade-out');
        toggleFiltersBtn.classList.remove('expanded');
        setTimeout(() => {
            advancedFilters.style.display = 'none';
            advancedFilters.classList.remove('fade-out');
        }, 300);
    };

    const openAdvancedFilters = () => {
        advancedFilters.style.display = 'grid';
        toggleFiltersBtn.classList.add('expanded');
    };

    toggleFiltersBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isExpanded = advancedFilters.style.display === 'grid';
        if (isExpanded) {
            closeAdvancedFilters();
        } else {
            openAdvancedFilters();
        }
    });

    window.addEventListener('click', (e) => {
        const filterWrapper = document.querySelector('.filter-wrapper');
        if (filterWrapper && !filterWrapper.contains(e.target)) {
            closeAdvancedFilters();
        }
    });

    activeFiltersContainer.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-filter-tag');
        if (removeBtn) {
            const tag = removeBtn.closest('.filter-tag');
            const filterId = tag.dataset.filterId;
            
            if (['ageMin', 'ageMax', 'dateAddedMin', 'dateAddedMax'].includes(filterId)) {
                 resetFilterElement('ageMin');
                 resetFilterElement('ageMax');
                 resetFilterElement('dateAddedMin');
                 resetFilterElement('dateAddedMax');
            } else {
                 resetFilterElement(filterId);
            }
            applyFilters(state);
        }
    });

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
            content.style.maxHeight = item.classList.contains('active') ? content.scrollHeight + "px" : 0;
        });
    });
}

function resetFilterElement(id) {
    const el = document.getElementById(id);
    if (!el) return;

    if (el.type === 'checkbox') {
        el.checked = false;
    } else if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
        el.value = '';
    }
};

function displayActiveFilterTags() {
    const activeFiltersContainer = document.getElementById('activeFiltersContainer');
    const filterLabels = {
        genderFilter: 'Gender', civilStatusFilter: 'Civil Status', isHeadFilter: 'Is Head',
        ageMin: 'Age Min', ageMax: 'Age Max', birthMonthFilter: 'Birth Month',
        purokFilter: 'Purok', streetFilter: 'Street', houseNoFilter: 'House No.',
        householdFilter: 'Head of Household', relationshipFilter: 'Relationship',
        employmentStatusFilter: 'Employment', isStudentFilter: 'Student', educationFilter: 'Education',
        occupationFilter: 'Occupation', isPwdFilter: 'PWD', isSoloParentFilter: 'Solo Parent',
        is4psMemberFilter: '4Ps Member', isIndigentFilter: 'Indigent',
        statusFilter: 'Status', residencyStatusFilter: 'Residency Type', bloodTypeFilter: 'Blood Type',
        emergencyContactFilter: 'Has Emergency Contact', dateAddedMin: 'Date Added From', dateAddedMax: 'Date Added To',
        isVoterFilter: 'Is Voter', nationalityFilter: 'Nationality', ownershipStatusFilter: 'Ownership Status',
        searchInput: 'Search',
    };

    let tagsHtml = '';
    const activeFilters = [];
    const filterElements = [
        { id: 'genderFilter', type: 'select' }, { id: 'civilStatusFilter', type: 'select' }, 
        { id: 'ageMin', type: 'input' }, { id: 'ageMax', type: 'input' }, { id: 'birthMonthFilter', type: 'select' },
        { id: 'purokFilter', type: 'select' }, { id: 'streetFilter', type: 'input' }, { id: 'houseNoFilter', type: 'input' },
        { id: 'householdFilter', type: 'select' }, { id: 'relationshipFilter', type: 'select' },
        { id: 'employmentStatusFilter', type: 'select' }, { id: 'educationFilter', type: 'select' },
        { id: 'occupationFilter', type: 'select' }, 
        { id: 'statusFilter', type: 'select' }, { id: 'residencyStatusFilter', type: 'select' },
        { id: 'bloodTypeFilter', type: 'select' }, 
        { id: 'dateAddedMin', type: 'input' }, { id: 'dateAddedMax', type: 'input' },
        { id: 'searchInput', type: 'input' }, { id: 'nationalityFilter', type: 'select' }, { id: 'ownershipStatusFilter', type: 'select' },
        // Checkboxes
        { id: 'isHeadFilter', type: 'checkbox' }, { id: 'isStudentFilter', type: 'checkbox' },
        { id: 'isPwdFilter', type: 'checkbox' }, { id: 'isSoloParentFilter', type: 'checkbox' },
        { id: 'is4psMemberFilter', type: 'checkbox' }, { id: 'isIndigentFilter', type: 'checkbox' },
        { id: 'isVoterFilter', type: 'checkbox' }, { id: 'emergencyContactFilter', type: 'checkbox' }
    ];

    filterElements.forEach(item => {
        const el = document.getElementById(item.id);
        let value = null;
        let displayValue = null;

        if (!el) return;

        if (item.type === 'checkbox') {
            if (el.checked) {
                value = '1'; 
                displayValue = 'Yes';
            }
        } else if (['ageMin', 'ageMax', 'dateAddedMin', 'dateAddedMax'].includes(item.id)) {
            // Range logic remains the same
             if (item.id === 'ageMin' && el.value && el.value.trim() !== '') {
                if (!document.getElementById('ageMax').value) { value = `${el.value}+`; } 
                else if (parseInt(el.value) < parseInt(document.getElementById('ageMax').value)) { value = `${el.value}-${document.getElementById('ageMax').value}`; }
                displayValue = value;
            } else if (item.id === 'ageMax' && el.value && el.value.trim() !== '' && !document.getElementById('ageMin').value) {
                value = `<${el.value}`; displayValue = value;
            } else if (item.id === 'dateAddedMin' && el.value && el.value.trim() !== '') {
                value = `${el.value} to ${document.getElementById('dateAddedMax').value || 'Now'}`; displayValue = value;
            } else if (item.id === 'dateAddedMax' && el.value && el.value.trim() !== '' && !document.getElementById('dateAddedMin').value) {
                value = `Before ${el.value}`; displayValue = value;
            }
        } else if (el.value && el.value.trim() !== '') {
            value = el.value.trim();
            if (item.id === 'birthMonthFilter') {
                displayValue = el.options[el.selectedIndex].textContent;
            } else {
                displayValue = value;
            }
        }

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

export { initializeFilters, applyFilters };