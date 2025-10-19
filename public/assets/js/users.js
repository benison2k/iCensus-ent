document.addEventListener('DOMContentLoaded', () => {
    const userModal = document.getElementById('userModal');
    if (!userModal) return;

    const closeModalBtn = userModal.querySelector('.close');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalTitle');
    const userIdInput = document.getElementById('user_id');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');
    const passwordMatchMessage = document.getElementById('passwordMatchMessage');
    const saveUserBtn = document.getElementById('saveUserBtn');
    const passwordHelp = document.getElementById('passwordHelp');
    const basePath = '/iCensus-ent/public';

    // --- Filter and Search Elements ---
    const userSearchInput = document.getElementById('userSearchInput');
    const roleFilterSelect = document.getElementById('roleFilterSelect');
    const userTableBody = document.getElementById('userTableBody');
    const noResultsMessage = document.getElementById('noResultsMessage');
    
    // --- Pagination Elements ---
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const gotoPageInput = document.getElementById('gotoPage');
    const gotoPageBtn = document.getElementById('gotoPageBtn');

    // --- Password Confirmation Logic ---
    const checkPasswordMatch = () => {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        if (password !== confirmPassword) {
            passwordMatchMessage.textContent = 'Passwords do not match.';
            saveUserBtn.disabled = true;
        } else {
            passwordMatchMessage.textContent = '';
            saveUserBtn.disabled = false;
        }
    };

    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);

    // --- Modal Controls ---
    document.getElementById('addUserBtn')?.addEventListener('click', () => {
        userForm.reset();
        userIdInput.value = '';
        modalTitle.textContent = 'Add New User';
        passwordInput.setAttribute('required', 'required');
        confirmPasswordInput.setAttribute('required', 'required');
        confirmPasswordGroup.style.display = 'block';
        passwordHelp.textContent = "A password is required for new users.";
        passwordInput.placeholder = "Enter new password";
        saveUserBtn.disabled = true; // Disabled until passwords match
        userModal.style.display = 'block';
    });

    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const id = e.currentTarget.dataset.id;
            const response = await fetch(`${basePath}/sysadmin/users/get?user_id=${id}`);
            const result = await response.json();

            if (result.status === 'success') {
                const user = result.user;
                userForm.reset();
                userIdInput.value = user.id;
                document.getElementById('full_name').value = user.full_name;
                document.getElementById('username').value = user.username;
                document.getElementById('role_id').value = user.role_id;
                
                modalTitle.textContent = 'Edit User';
                passwordInput.removeAttribute('required');
                confirmPasswordInput.removeAttribute('required');
                confirmPasswordGroup.style.display = 'block';
                passwordHelp.textContent = "Leave blank to keep current password.";
                passwordInput.placeholder = "Enter new password (optional)";
                passwordMatchMessage.textContent = '';
                saveUserBtn.disabled = false;
                userModal.style.display = 'block';
            } else {
                alert('Error: Could not fetch user data.');
            }
        });
    });

    // --- AJAX DELETE ---
    document.querySelectorAll('.deleteBtn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const id = e.currentTarget.dataset.id;
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    const response = await fetch(`${basePath}/sysadmin/users/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ 'action': 'delete', 'user_id': id })
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        window.location.reload(); // Reload to update table and pagination
                    } else {
                        alert('Error: ' + (result.message || 'Could not delete user.'));
                    }
                } catch (error) {
                    alert('An unexpected error occurred.');
                }
            }
        });
    });
    
    // --- Close Modal Logic ---
    closeModalBtn.addEventListener('click', () => userModal.style.display = 'none');
    window.addEventListener('click', (event) => { if (event.target === userModal) userModal.style.display = 'none'; });

    // --- Client-side Search and Filter ---
    const filterUsers = () => {
        const searchText = userSearchInput.value.toLowerCase();
        const roleFilter = roleFilterSelect.value;
        let visibleCount = 0;

        userTableBody.querySelectorAll('tr').forEach(row => {
            const nameCell = row.querySelector('td[data-searchable]:nth-child(3)');
            const usernameCell = row.querySelector('td[data-searchable]:nth-child(2)');
            const roleCell = row.querySelector('td[data-role]');

            const nameMatch = nameCell.textContent.toLowerCase().includes(searchText);
            const usernameMatch = usernameCell.textContent.toLowerCase().includes(searchText);
            const roleMatch = !roleFilter || roleCell.dataset.role === roleFilter;
            
            if ((nameMatch || usernameMatch) && roleMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
    };

    userSearchInput.addEventListener('input', filterUsers);
    roleFilterSelect.addEventListener('change', filterUsers);

    // --- Pagination Logic ---
    const updateUrlParams = (key, value) => {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        if (key !== 'page') url.searchParams.set('page', 1);
        window.location.href = url.toString();
    };

    pageSizeSelect.addEventListener('change', () => updateUrlParams('pageSize', pageSizeSelect.value));
    prevPageBtn.addEventListener('click', () => {
        const url = new URL(window.location.href);
        const currentPage = parseInt(url.searchParams.get('page') || '1', 10);
        if (currentPage > 1) {
            url.searchParams.set('page', currentPage - 1);
            window.location.href = url.toString();
        }
    });
    nextPageBtn.addEventListener('click', () => {
        const url = new URL(window.location.href);
        const currentPage = parseInt(url.searchParams.get('page') || '1', 10);
        const totalPages = parseInt(document.getElementById('pageInfo').textContent.split(' of ')[1], 10);
        if (currentPage < totalPages) {
            url.searchParams.set('page', currentPage + 1);
            window.location.href = url.toString();
        }
    });
    gotoPageBtn.addEventListener('click', () => updateUrlParams('page', gotoPageInput.value));
    gotoPageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            updateUrlParams('page', gotoPageInput.value);
        }
    });

    // Update shown count on load
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCountEl');
    if (shownCountEl && totalCountEl) {
        const currentPage = parseInt(document.getElementById('pageInfo').textContent.split(' ')[1], 10);
        const pageSize = parseInt(pageSizeSelect.value, 10);
        const totalUsers = parseInt(totalCountEl.textContent, 10);
        const startItem = totalUsers === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const endItem = Math.min(currentPage * pageSize, totalUsers);
        shownCountEl.textContent = `${startItem}–${endItem}`;
    }
});