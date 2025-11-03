// Handles the Account tab logic (e.g., username)
export function initAccountForm(helpers) {
    const { showAjaxResult, BASE_URL } = helpers;

    const usernameForm = document.getElementById('usernameForm');
    if (!usernameForm) return;

    usernameForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;

        try {
            const formData = new URLSearchParams(new FormData(this));
            const response = await fetch(BASE_URL + '/settings/username', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (response.ok) {
                showAjaxResult(result.message, 'success');
                // Optionally update username in header, or just reload
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAjaxResult(result.message || 'An error occurred.', 'error');
            }
        } catch (error) {
            showAjaxResult('A network error occurred. Please try again.', 'error');
        } finally {
            btn.disabled = false;
        }
    });
}