<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - System Logs</title>
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/users.css">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="welcome"><h2>System Logs</h2></div>

<main class="dashboard">
<div class="user-management-container">
    <div class="table-responsive">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Timestamp</th><th>Level</th><th>User</th><th>Action</th><th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align: center;">No logs found.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['timestamp']) ?></td>
                        <td><?= htmlspecialchars($log['level']) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>