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

    // --- STATE ---
    let currentPage = 1;
    let pageSize = parseInt(pageSizeSelect.value, 10);
    let filteredResidents = [];

    // --- MODAL & FORM FUNCTIONS ---
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

    // --- TABLE & PAGINATION FUNCTIONS ---
    const attachEventListenersToRows = () => {
        document.querySelectorAll('.moreBtn').forEach(btn => {
            btn.addEventListener('click', (e) => openModalForEdit(e.currentTarget.dataset.id));
        });
    };

    const renderTable = () => {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageSlice = filteredResidents.slice(start, end);
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
                        <td>${fullName}</td><td>${r.age}</td><td>${r.gender}</td>
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
        attachEventListenersToRows();
    };    

    const applyFilters = () => {
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
        const isHead = isHeadFilter.value;
        const birthMonth = birthMonthFilter.value;
        const minDateAdded = dateAddedMin.value;
        const maxDateAdded = dateAddedMax.value;
        const hasEmergency = emergencyContactFilter.value;
        const isVoter = isVoterFilter.checked;
        const education = educationFilter.value;
        const occupation = occupationFilter.value;
        const employmentStatus = employmentStatusFilter.value;
        const isPwd = isPwdFilter.value;
        const isSoloParent = isSoloParentFilter.value;
        const is4ps = is4psMemberFilter.value;

        filteredResidents = allResidentsData.filter(r => {
            const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();

            if (searchTerm && !fullName.includes(searchTerm)) return false;
            if (houseNo && r.house_no && !r.house_no.toString().toLowerCase().includes(houseNo)) return false;
            if (street && r.street && !r.street.toLowerCase().includes(street)) return false;
            if (status && r.status !== status) return false;
            if (gender && r.gender !== gender) return false;
            if (minAge !== null && r.age < minAge) return false;
            if (maxAge !== null && r.age > maxAge) return false;
            if (purok && r.purok != purok) return false;
            if (household && r.head_of_household !== household) return false;
            if (civilStatus && r.civil_status !== civilStatus) return false;
            if (bloodType && r.blood_type !== bloodType) return false;
            if (residencyStatus && r.residency_status !== residencyStatus) return false;
            if (relationship && r.relationship !== relationship) return false;
            if (isHead === 'Yes' && r.relationship !== 'Self') return false;
            if (isHead === 'No' && r.relationship === 'Self') return false;
            if (birthMonth && r.dob && new Date(r.dob).getMonth() + 1 != birthMonth) return false;
            if (minDateAdded && r.date_added && r.date_added < minDateAdded) return false;
            if (maxDateAdded && r.date_added && r.date_added.split(' ')[0] > maxDateAdded) return false;
            if (hasEmergency === 'Yes' && !r.emergency_name) return false;
            if (hasEmergency === 'No' && r.emergency_name) return false;
            if (isVoter && !r.is_registered_voter) return false;
            if (education && r.educational_attainment !== education) return false;
            if (occupation && r.occupation !== occupation) return false;
            if (employmentStatus === 'employed' && (!r.occupation || r.occupation.trim() === '')) return false;
            if (employmentStatus === 'unemployed' && r.occupation && r.occupation.trim() !== '') return false;
            if (isPwd !== "" && r.is_pwd != isPwd) return false;
            if (isSoloParent !== "" && r.is_solo_parent != isSoloParent) return false;
            if (is4ps !== "" && r.is_4ps_member != is4ps) return false;
            return true;
        });

        const totalResidents = allResidentsData.length;
        const filteredCount = filteredResidents.length;

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

    // --- START: AJAX FORM SUBMISSION LOGIC ---
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
        // Reload the page after showing the message
        setTimeout(() => {
            ajaxModal.style.display = "none";
            window.location.reload();
        }, 2000); // Reload after 2 seconds
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
                modal.style.display = 'none'; // Close the resident form modal
                showAjaxResult(result.message || 'Resident saved successfully!', 'success');
            } else {
                alert(result.message || 'An error occurred.'); // Show an alert for immediate error feedback
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
    // --- END: AJAX FORM SUBMISSION LOGIC ---

    toggleFiltersBtn.addEventListener('click', () => {
        const isExpanded = advancedFilters.style.display === 'grid';
        advancedFilters.style.display = isExpanded ? 'none' : 'grid';
        toggleFiltersBtn.classList.toggle('expanded', !isExpanded);
    });

    // --- NEW: Click outside to close advanced filters ---
    window.addEventListener('click', (e) => {
        const filterWrapper = document.querySelector('.filter-wrapper');
        if (filterWrapper && !filterWrapper.contains(e.target) && advancedFilters.style.display === 'grid') {
            advancedFilters.style.display = 'none';
            toggleFiltersBtn.classList.remove('expanded');
        }
    });
    // --- END NEW ---

    [searchInput, houseNoFilter, streetFilter, ageMin, ageMax, dateAddedMin, dateAddedMax].forEach(el => {
        if(el) el.addEventListener('input', applyFilters);
    });

    [statusFilter, genderFilter, purokFilter, householdFilter, civilStatusFilter, bloodTypeFilter, residencyStatusFilter, relationshipFilter, isHeadFilter, birthMonthFilter, emergencyContactFilter, isVoterFilter, educationFilter, occupationFilter, employmentStatusFilter, isPwdFilter, isSoloParentFilter, is4psMemberFilter].forEach(el => {
        if(el) el.addEventListener('change', applyFilters);
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
        searchInput.value = ''; houseNoFilter.value = ''; streetFilter.value = '';
        statusFilter.value = ''; genderFilter.value = ''; ageMin.value = '';
        ageMax.value = ''; purokFilter.value = ''; householdFilter.value = '';
        civilStatusFilter.value = ''; bloodTypeFilter.value = ''; residencyStatusFilter.value = '';
        relationshipFilter.value = ''; isHeadFilter.value = ''; birthMonthFilter.value = '';
        dateAddedMin.value = ''; dateAddedMax.value = ''; emergencyContactFilter.value = '';
        isVoterFilter.checked = false;
        educationFilter.value = ''; occupationFilter.value = ''; employmentStatusFilter.value = ''; isPwdFilter.value = '';
        isSoloParentFilter.value = ''; is4psMemberFilter.value = '';
        applyFilters();
    });

    // --- INITIALIZATION ---
    if (typeof allResidentsData !== 'undefined' && !isPendingView) {
        applyFilters();
    }
});