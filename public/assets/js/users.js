// Replace the content of /public/assets/js/users.js with this

document.addEventListener('DOMContentLoaded', () => {
    const userModal = document.getElementById('userModal');
    if (!userModal) return;

    const closeModalBtn = userModal.querySelector('.close');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalTitle');
    const userIdInput = document.getElementById('user_id');
    const passwordInput = document.getElementById('password');
    const basePath = '/iCensus-ent/public';

    // Open Modal for Adding
    document.getElementById('addUserBtn')?.addEventListener('click', () => {
        userForm.reset();
        userIdInput.value = '';
        modalTitle.textContent = 'Add New User';
        passwordInput.setAttribute('required', 'required');
        passwordInput.placeholder = "Enter new password (required)";
        userModal.style.display = 'block';
    });

    // Open Modal for Editing
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const id = e.currentTarget.dataset.id;
            // --- URL UPDATED ---
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
                passwordInput.placeholder = "Leave blank to keep current password";
                userModal.style.display = 'block';
            } else {
                alert('Error: Could not fetch user data.');
            }
        });
    });

    // --- IMPROVED DELETE HANDLING WITH AJAX ---
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
                        body: new URLSearchParams({
                            'action': 'delete',
                            'user_id': id
                        })
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        // Reload the page to show the updated user list
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.message || 'Could not delete user.'));
                    }
                } catch (error) {
                    console.error('Deletion failed:', error);
                    alert('An unexpected error occurred. Please try again.');
                }
            }
        });
    });
    // --- END OF IMPROVEMENTS ---

    // Close Modal Logic
    closeModalBtn.addEventListener('click', () => userModal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target === userModal) userModal.style.display = 'none';
    });
});