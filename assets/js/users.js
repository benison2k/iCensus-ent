document.addEventListener('DOMContentLoaded', () => {
    const userModal = document.getElementById('userModal');
    if (!userModal) return;

    const closeModalBtn = userModal.querySelector('.close');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalTitle');
    const userIdInput = document.getElementById('user_id');
    const passwordInput = document.getElementById('password');

    // --- Open Modal for Adding ---
    document.getElementById('addUserBtn')?.addEventListener('click', () => {
        userForm.reset();
        userIdInput.value = '';
        modalTitle.textContent = 'Add New User';
        passwordInput.setAttribute('required', 'required');
        passwordInput.placeholder = "Enter new password (required)";
        userModal.style.display = 'block';
    });

    // --- Open Modal for Editing ---
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const id = e.currentTarget.dataset.id;
            const response = await fetch(`../core/users_process.php?action=get&user_id=${id}`);
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
                alert('Error: ' + result.message);
            }
        });
    });

    // --- Handle Deletion ---
    document.querySelectorAll('.deleteBtn').forEach(button => {
        button.addEventListener('click', (e) => {
            const id = e.currentTarget.dataset.id;
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../core/users_process.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // --- Close Modal Logic ---
    closeModalBtn.addEventListener('click', () => userModal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target === userModal) userModal.style.display = 'none';
    });
});