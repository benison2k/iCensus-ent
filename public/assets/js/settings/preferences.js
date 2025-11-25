// public/assets/js/settings/preferences.js

export function initPreferencesTab(helpers) {
    const { BASE_URL } = helpers;

    // --- Existing Theme Logic ---
    const themeSwitch = document.getElementById('themeSwitch');
    if (themeSwitch) {
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

    // --- NEW: Sidebar Pin Logic ---
    const sidebarSwitch = document.getElementById('sidebarSwitch');
    if (sidebarSwitch) {
        sidebarSwitch.addEventListener('change', () => {
            const isPinned = sidebarSwitch.checked;
            
            // Toggle the class on body immediately for visual feedback
            document.body.classList.toggle('sidebar-pinned', isPinned);
            document.getElementById('sidebarLabel').textContent = isPinned ? 'Always Visible' : 'Collapsible';

            // Send to backend
            fetch(BASE_URL + '/settings/sidebar-mode', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ pinned: isPinned })
            });
        });
    }
}