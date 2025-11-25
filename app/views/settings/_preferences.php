<div id="tab-preferences" class="tab-pane">
    <h3>Preferences</h3>
    
    <div class="form-group">
        <label for="themeSwitch">Theme</label>
        <div style="display:flex; align-items:center; gap:1rem;">
            <label class="switch">
                <input type="checkbox" id="themeSwitch" <?= $theme === 'dark' ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
            <span id="themeLabel"><?= $theme === 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
        </div>
    </div>

    <div class="form-group">
        <label for="sidebarSwitch">Sidebar Layout</label>
        <div style="display:flex; align-items:center; gap:1rem;">
            <label class="switch">
                <input type="checkbox" id="sidebarSwitch" <?= ($_SESSION['user']['sidebar_pinned'] ?? 0) == 1 ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
            <span id="sidebarLabel"><?= ($_SESSION['user']['sidebar_pinned'] ?? 0) == 1 ? 'Always Visible' : 'Collapsible'; ?></span>
        </div>
    </div>
</div>