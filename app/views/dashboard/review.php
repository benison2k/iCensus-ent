<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iCensus - Review Entries</title>
<?php $base_url = '/iCensus-ent/public'; ?>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/users.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/residents_table.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/modal.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="<?= $theme==='dark'?'dark-mode':''; ?>">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="welcome"><h2>Entries for Review</h2></div>

    <main class="dashboard">
    <?php if ($modalMessage):
        $id="resultModal"; $message=$modalMessage; $type=$modalType;
        include __DIR__ . '/../components/modal.php';
    endif; ?>

    <div class="user-management-container">
        <?php if (empty($pending_residents)): ?>
            <div class="settings-card" style="text-align: center;">
                <span class="material-icons card-icon" style="font-size: 3rem; color: #4caf50;">check_circle</span>
                <h3 class="card-title">All Clear!</h3>
                <p>There are no pending resident entries to review.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="user-table resident-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Address</th>
                                <th>Date Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_residents as $resident): ?>
                            <tr>
                                <td><?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></td>
                                <td><?= htmlspecialchars($resident['age']) ?></td>
                                <td><?= htmlspecialchars($resident['gender']) ?></td>
                                <td><?= htmlspecialchars($resident['house_no'] . ' ' . $resident['street'] . ', Purok ' . $resident['purok']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($resident['created_at'])) ?></td>
                                <td>
                                    <a href="<?= $base_url ?>/residents/approve?id=<?= $resident['id'] ?>" class="action-btn" title="Approve">
                                        <span class="material-icons" style="color: green;">check</span>
                                    </a>
                                    <a href="<?= $base_url ?>/residents/reject?id=<?= $resident['id'] ?>" class="action-btn" title="Reject" onclick="return confirm('Are you sure you want to reject and delete this entry?');">
                                        <span class="material-icons" style="color: red;">close</span>
                                    </a>
                                    </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script src="<?= $base_url ?>/assets/js/modal.js"></script>

</body>
</html>