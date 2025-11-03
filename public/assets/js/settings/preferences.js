// Handles the Preferences tab logic (e.g., theme)
export function initPreferencesTab(helpers) {
    const { BASE_URL } = helpers;

    const themeSwitch = document.getElementById('themeSwitch');
    if (!themeSwitch) return;

    themeSwitch.addEventListener('change', () => {
        const theme = themeSwitch.checked ? 'dark' : 'light';
        document.body.classList.toggle('dark-mode', theme === 'dark');
        document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';

        fetch(BASE_URL + '/settings/theme', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ theme: theme })
        });
    });
}