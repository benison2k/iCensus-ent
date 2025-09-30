<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Residents</title>
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/style.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/residents.css">
<link rel="stylesheet" href="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : ''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Residents Management</h2></div>

    <main class="dashboard">
    <?php if ($modalMessage):
        $id = "resultModal"; $message = $modalMessage; $type = $modalType;
        include __DIR__ . '/../components/modal.php';
    endif; ?>

    <div style="padding:0 2rem; max-width:1400px; margin:auto;">
        
        <button id="addResidentBtn" class="settings-card" style="cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
            <span class="material-icons">person_add</span> Add Resident
        </button>

        <p style="margin: 1rem 0; font-weight: 500;">
            Total Residents in Database: <span id="totalCount"><?= count($residents); ?></span>
        </p>
        
        <div class="table-responsive">
            <table class="resident-table" id="residentsTable">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="residentsTableBody">
                    <?php foreach ($residents as $r): ?>
                        <tr data-id="<?= $r['id'] ?>">
                            <td><?= htmlspecialchars(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td>
                            <td><?= $r['age'] ?></td>
                            <td><?= htmlspecialchars($r['gender']) ?></td>
                            <td><?= htmlspecialchars(trim($r['house_no'] . ' ' . $r['street'] . ', Purok ' . $r['purok'])) ?></td>
                            <td><span class="status-label status-<?= strtolower(htmlspecialchars($r['status'])) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td><button class="moreBtn material-icons" data-id="<?= $r['id'] ?>" title="View Resident Info">more_vert</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../components/resident_modal2.php'; ?>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>

<script src="/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/assets/js/residents.js"></script>
</body>
</html>