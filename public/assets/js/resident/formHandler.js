// benison2k/icensus-ent/iCensus-ent-development-branch-MVC-/public/assets/js/resident/formHandler.js

import { renderTable } from './tableManager.js';
const basePath = '/iCensus-ent/public';

// ✅ NEW: Modern toast notification function
function showAjaxResult(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    const icon = type === 'success' ? 'check_circle' : 'error';
    toast.innerHTML = `<span class="material-icons">${icon}</span><p>${message}</p>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
            // Reload the page after the notification has been shown
            window.location.reload();
        }, 500);
    }, 3000);
}


async function handleFormSubmit(form, state) {
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (response.ok && result.status === 'success') {
            document.getElementById('residentModal').style.display = 'none';
            // Use the new toast notification
            showAjaxResult(result.message || 'Resident saved successfully!', 'success');
        } else {
            alert(result.message || 'An error occurred.');
        }
    } catch (error) {
        alert('A network error occurred. Please try again.');
    }
}

function initializeForm(state) {
    const form = document.getElementById('residentForm');
    const deleteBtn = form.querySelector('.deleteBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this, state);
    });

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            const id = document.getElementById('resident_id').value;
            if (!id) return;
            if (!confirm('Are you sure you want to delete this resident? This action cannot be undone.')) return;
    
            const response = await fetch(`${basePath}/residents/process`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'delete', id: id })
            });
            const result = await response.json();
            if (result.status === 'success') {
                document.getElementById('residentModal').style.display = 'none';
                // Use the new toast notification
                showAjaxResult(result.message || 'Resident deleted successfully.', 'success');
            } else {
                alert(result.message || 'Failed to delete resident.');
            }
        });
    }
}

export { initializeForm };