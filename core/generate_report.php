<?php
session_start();

// --- Bouncer ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] != 'Barangay Admin') {
    http_response_code(403);
    die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>");
}
// --- End Bouncer ---

// Include config + core
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

$db = new Database($config);
$pdo = $db->getPdo();

$report_type = $_POST['report_type'] ?? 'all_residents';
$purok = $_POST['purok'] ?? '';

$sql = "SELECT * FROM residents";
$params = [];

if ($report_type === 'by_purok' && !empty($purok)) {
    $sql .= " WHERE purok = ?";
    $params[] = $purok;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resident Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .no-print { 
            position: fixed; 
            top: 10px; 
            right: 10px; 
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print Report</button>
    </div>
    <h1>Resident Report</h1>
    <p>Report generated on: <?= date('Y-m-d H:i:s') ?></p>
    <?php if ($report_type === 'by_purok'): ?>
        <p>Purok: <?= htmlspecialchars($purok) ?></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Gender</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($residents as $resident): ?>
                <tr>
                    <td><?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></td>
                    <td><?= htmlspecialchars($resident['house_no'] . ' ' . $resident['street'] . ', ' . $resident['purok'] . ', ' . $resident['barangay']) ?></td>
                    <td><?= htmlspecialchars($resident['gender']) ?></td>
                    <td><?= htmlspecialchars($resident['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>