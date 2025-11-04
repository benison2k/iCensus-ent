document.addEventListener('DOMContentLoaded', () => {
    // --- STATE ---
    const state = {
        currentPage: 1,
        pageSize: 10,
        filteredUsers: [],
        currentSort: {
            column: 'id',
            order: 'asc'
        },
        allUsers: typeof allUsersData !== 'undefined' ? allUsersData : [],
        userRole: typeof userRole !== 'undefined' ? userRole : '',
        assignableRoles: typeof assignableRoles !== 'undefined' ? assignableRoles : []
    };

    // --- SELECTORS ---
    const modal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalTitle');
    const userIdInput = document.getElementById('user_id');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');
    const passwordMatchMessage = document.getElementById('passwordMatchMessage');
    const saveUserBtn = document.getElementById('saveUserBtn');
    const passwordHelp = document.getElementById('passwordHelp');
    const closeModalBtn = modal.querySelector('.close');
    const addUserBtn = document.getElementById('addUserBtn');

    const searchInput = document.getElementById('searchInput');
    const roleFilterSelect = document.getElementById('roleFilterSelect');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const activeFiltersContainer = document.getElementById('activeFiltersContainer');

    const tableBody = document.getElementById('userTableBody');
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCountEl');
    const filteredResultsDiv = document.getElementById('filteredResults');
    const filteredCountSpan = document.getElementById('filteredCount');

    const ajaxToast = document.getElementById('ajaxToast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');

    const basePath = '/iCensus-ent/public';

    // --- MODAL & FORM LOGIC ---

    function showToast(message, type = 'success') {
        toastMessage.textContent = message;
        ajaxToast.className = `toast-notification ${type}`;
        toastIcon.textContent = type === 'success' ? 'check_circle' : 'error';
        
        ajaxToast.classList.add('show');
        setTimeout(() => {
            ajaxToast.classList.remove('show');
        }, 3000);
    }

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password || confirmPassword) {
             if (password !== confirmPassword) {
                passwordMatchMessage.textContent = 'Passwords do not match.';
                saveUserBtn.disabled = true;
            } else {
                passwordMatchMessage.textContent = '';
                saveUserBtn.disabled = false;
            }
        } else {
             passwordMatchMessage.textContent = '';
             saveUserBtn.disabled = false;
        }
       
        // Also check required fields for new user
        if (userIdInput.value === '') { // New user
            if (!password || !confirmPassword) {
                saveUserBtn.disabled = true;
            } else if (password !== confirmPassword) {
                saveUserBtn.disabled = true;
            } else {
                saveUserBtn.disabled = false;
            }
        }
    }

    function openModalForAdd() {
        userForm.reset();
        userIdInput.value = '';
        modalTitle.textContent = 'Add New User';
        passwordInput.setAttribute('required', 'required');
        confirmPasswordInput.setAttribute('required', 'required');
        confirmPasswordGroup.style.display = 'block';
        passwordHelp.textContent = "A password is required for new users.";
        passwordInput.placeholder = "Enter new password";
        saveUserBtn.disabled = true;
        modal.style.display = 'flex';
        checkPasswordMatch();
    }

    async function openModalForEdit(id) {
        try {
            const res = await fetch(`${basePath}/sysadmin/users/get?user_id=${id}`);
            const result = await res.json();
            if (result.status !== 'success') return alert('User not found.');

            const data = result.user;
            userForm.reset();
            userIdInput.value = data.id;
            modalTitle.textContent = `Edit User: ${data.username}`;
            
            Object.keys(data).forEach(key => {
                const el = userForm.elements[key];
                if (el) el.value = data[key];
            });

            passwordInput.removeAttribute('required');
            confirmPasswordInput.removeAttribute('required');
            passwordHelp.textContent = "Leave blank to keep current password.";
            passwordInput.placeholder = "Leave blank to keep current";
            passwordMatchMessage.textContent = '';
            saveUserBtn.disabled = false;
            modal.style.display = 'flex';
        } catch (err) {
            console.error('Failed to fetch user data:', err);
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        saveUserBtn.disabled = true;
        saveUserBtn.querySelector('.material-icons').textContent = 'loop';

        try {
            const formData = new FormData(userForm);
            const response = await fetch(userForm.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                modal.style.display = 'none';
                showToast(result.message, 'success');
                
                // Update local state instead of reloading
                if (result.is_new) {
                    state.allUsers.push(result.user);
                } else {
                    const index = state.allUsers.findIndex(u => u.id == result.user.id);
                    if (index > -1) {
                        state.allUsers[index] = result.user;
                    }
                }
                applyFilters(); // Re-filter and render the table
            } else {
                alert(result.message || 'An error occurred.');
            }
        } catch (error) {
            alert('A network error occurred. Please try again.');
        } finally {
            saveUserBtn.disabled = false;
            saveUserBtn.querySelector('.material-icons').textContent = 'save';
        }
    }

    async function handleDelete(id, username) {
        if (!id) return;
        if (!confirm(`Are you sure you want to delete this user: ${username}? This action cannot be undone.`)) return;

        try {
            const response = await fetch(`${basePath}/sysadmin/users/process`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: new URLSearchParams({ action: 'delete', 'user_id': id })
            });
            const result = await response.json();
            if (result.status === 'success') {
                showToast(result.message || 'User deleted successfully.', 'success');
                // Remove from local state
                state.allUsers = state.allUsers.filter(u => u.id != id);
                applyFilters(); // Re-filter and render
            } else {
                alert(result.message || 'Failed to delete user.');
            }
        } catch (error) {
             alert('A network error occurred. Please try again.');
        }
    }

    // --- FILTERING LOGIC ---

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const role = roleFilterSelect.value;

        state.filteredUsers = state.allUsers.filter(user => {
            const nameMatch = user.full_name.toLowerCase().includes(searchTerm);
            const usernameMatch = user.username.toLowerCase().includes(searchTerm);
            const roleMatch = !role || user.role_name === role;
            
            return (nameMatch || usernameMatch) && roleMatch;
        });

        displayActiveFilterTags();
        
        const totalUsers = state.allUsers.length;
        const filteredCount = state.filteredUsers.length;

        if (filteredCount < totalUsers) {
            filteredCountSpan.textContent = filteredCount;
            filteredResultsDiv.style.display = 'block';
        } else {
            filteredResultsDiv.style.display = 'none';
        }

        state.currentPage = 1;
        renderTable();
    }

    function resetFilterElement(id) {
        const el = document.getElementById(id);
        if (el) el.value = '';
    }

    function displayActiveFilterTags() {
        let tagsHtml = '';
        const filters = [
            { id: 'searchInput', label: 'Search', value: searchInput.value },
            { id: 'roleFilterSelect', label: 'Role', value: roleFilterSelect.value },
        ];

        filters.forEach(filter => {
            if (filter.value) {
                tagsHtml += `
                    <span class="filter-tag" data-filter-id="${filter.id}">
                        ${filter.label}: ${filter.value}
                        <span class="material-icons remove-filter-tag">close</span>
                    </span>`;
            }
        });

        if (tagsHtml) {
            activeFiltersContainer.style.display = 'flex';
            activeFiltersContainer.innerHTML = '<span class="active-filters-label">Active Filters:</span>' + tagsHtml;
        } else {
            activeFiltersContainer.style.display = 'none';
            activeFiltersContainer.innerHTML = '';
        }
    }

    // --- TABLE & PAGINATION LOGIC ---

    function renderTable() {
        state.filteredUsers.sort((a, b) => {
            const valA = a[state.currentSort.column];
            const valB = b[state.currentSort.column];
            
            let comparison = 0;
            if (typeof valA === 'string') {
                comparison = valA.toLowerCase().localeCompare(valB.toLowerCase());
            } else {
                comparison = valA > valB ? 1 : (valA < valB ? -1 : 0);
            }
            
            return state.currentSort.order === 'desc' ? comparison * -1 : comparison;
        });

        tableBody.innerHTML = '';
        const start = (state.currentPage - 1) * state.pageSize;
        const end = start + state.pageSize;
        const pageSlice = state.filteredUsers.slice(start, end);
        let rowsHtml = '';

        if (state.filteredUsers.length === 0) {
            rowsHtml = '<tr><td colspan="5" style="text-align: center; height: 380px; vertical-align: middle;">No users found matching the criteria.</td></tr>';
        } else {
            pageSlice.forEach(u => {
                const roleClass = `role-${u.role_name.toLowerCase().replace(' ', '')}`;
                rowsHtml += `
                    <tr data-user-id="${u.id}">
                        <td>${u.id}</td>
                        <td>${u.username}</td>
                        <td>${u.full_name}</td>
                        <td><span class="role-label ${roleClass}">${u.role_name}</span></td>
                        <td>
                            <div class="actions-column">
                                <button class="action-btn btn-edit editBtn" data-id="${u.id}" title="Edit User"><span class="material-icons">edit</span></button>
                                <button class="action-btn btn-delete deleteBtn" data-id="${u.id}" data-username="${u.username}" title="Delete User"><span class="material-icons">delete</span></button>
                            </div>
                        </td>
                    </tr>`;
            });
        }

        tableBody.innerHTML = rowsHtml;
        updatePagination();
        updateSortIcons();
    }

    function updatePagination() {
        const totalUsers = state.filteredUsers.length;
        const totalPages = Math.ceil(totalUsers / state.pageSize) || 1;
        const startItem = totalUsers === 0 ? 0 : (state.currentPage - 1) * state.pageSize + 1;
        const endItem = Math.min(state.currentPage * state.pageSize, totalUsers);

        pageInfo.textContent = `Page ${state.currentPage} of ${totalPages}`;
        shownCountEl.textContent = `${startItem}–${endItem}`;
        totalCountEl.textContent = totalUsers;
        prevPageBtn.disabled = state.currentPage === 1;
        nextPageBtn.disabled = state.currentPage === totalPages;
        gotoPageInput.max = totalPages;
    }

    function updateSortIcons() {
        document.querySelectorAll('th.sortable .sort-icon').forEach(icon => icon.innerHTML = '');
        
        const activeHeader = document.querySelector(`th[data-sort="${state.currentSort.column}"]`);
        if (activeHeader) {
            const icon = state.currentSort.order === 'asc' ? 'arrow_upward' : 'arrow_downward';
            activeHeader.querySelector('.sort-icon').innerHTML = `<span class="material-icons">${icon}</span>`;
        }
    }

    function jumpToPage() {
        const totalPages = Math.ceil(state.filteredUsers.length / state.pageSize) || 1;
        const page = parseInt(gotoPageInput.value, 10);
        if (page >= 1 && page <= totalPages) {
            state.currentPage = page;
            renderTable();
        } else {
            alert(`Please enter a page number between 1 and ${totalPages}.`);
        }
        gotoPageInput.value = '';
    }

    // --- INITIALIZE EVENT LISTENERS ---

    // Modals
    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    addUserBtn.addEventListener('click', openModalForAdd);
    closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (event) => { if (event.target === modal) modal.style.display = 'none'; });
    userForm.addEventListener('submit', handleFormSubmit);

    // Filters
    searchInput.addEventListener('input', applyFilters);
    roleFilterSelect.addEventListener('change', applyFilters);
    clearFiltersBtn.addEventListener('click', () => {
        resetFilterElement('searchInput');
        resetFilterElement('roleFilterSelect');
        applyFilters();
    });
    activeFiltersContainer.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-filter-tag');
        if (removeBtn) {
            const filterId = removeBtn.parentElement.dataset.filterId;
            resetFilterElement(filterId);
            applyFilters();
        }
    });

    // Table & Pagination
    pageSizeSelect.addEventListener('change', (e) => {
        state.pageSize = parseInt(e.target.value, 10);
        state.currentPage = 1;
        renderTable();
    });
    prevPageBtn.addEventListener('click', () => { if (state.currentPage > 1) { state.currentPage--; renderTable(); } });
    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(state.filteredUsers.length / state.pageSize);
        if (state.currentPage < totalPages) { state.currentPage++; renderTable(); }
    });
    gotoPageBtn.addEventListener('click', jumpToPage);
    gotoPageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') jumpToPage(); });

    // Table Clicks (Sorting & Actions)
    document.getElementById('usersTable').querySelector('thead').addEventListener('click', (e) => {
        const header = e.target.closest('.sortable');
        if (header) {
            const sortColumn = header.dataset.sort;
            if (state.currentSort.column === sortColumn) {
                state.currentSort.order = state.currentSort.order === 'asc' ? 'desc' : 'asc';
            } else {
                state.currentSort.column = sortColumn;
                state.currentSort.order = 'asc';
            }
            renderTable();
        }
    });

    tableBody.addEventListener('click', (e) => {
        const editButton = e.target.closest('.editBtn');
        if (editButton) {
            openModalForEdit(editButton.dataset.id);
        }
        const deleteButton = e.target.closest('.deleteBtn');
        if (deleteButton) {
            handleDelete(deleteButton.dataset.id, deleteButton.dataset.username);
        }
    });

    // --- INITIAL RENDER ---
    state.filteredUsers = state.allUsers;
    renderTable();
});