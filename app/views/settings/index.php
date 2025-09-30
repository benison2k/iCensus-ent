<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCensus - Settings</title>
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/settings.css">
    <link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : ''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Settings</h2></div>
    <main class="dashboard">

    <?php if ($modalMessage):
        $id = "resultModal"; $message = $modalMessage; $type = $modalType;
        include __DIR__ . '/../components/modal.php';
    endif; ?>

    <div class="settings-grid">
        <div class="card settings-card">
            <span class="material-icons card-icon">person</span>
            <h3 class="card-title">Account</h3>
            
            <form action="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/settings/process" method="POST">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
                <button type="submit" name="update_username"><span class="material-icons">save</span> Save Username</button>
            </form>
            <hr style="margin:1.5rem 0;">
            <form action="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/settings/process" method="POST">
                <label>New Password</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" name="update_password"><span class="material-icons">save</span> Save Password</button>
            </form>
        </div>

        <div class="card settings-card">
            <span class="material-icons card-icon">tune</span>
            <h3 class="card-title">Preferences</h3>
            <form>
                <label>Theme</label><br>
                <label class="switch">
                    <input type="checkbox" id="themeSwitch" <?= $theme === 'dark' ? 'checked' : ''; ?>>
                    <span class="slider round"></span>
                </label>
                <span id="themeLabel"><?= $theme === 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
            </form>
        </div>
    </div>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script>
    // Theme toggle logic
    const themeSwitch = document.getElementById('themeSwitch');
    themeSwitch.addEventListener('change', () => {
        const theme = themeSwitch.checked ? 'dark' : 'light';
        // Fetch URL updated
        fetch('/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/settings/theme', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ theme: theme })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.body.classList.toggle('dark-mode', data.theme === 'dark');
                document.getElementById('themeLabel').textContent = data.theme === 'dark' ? 'Dark Mode' : 'Light Mode';
            }
        });
    });
    </script>
</body>
</html>