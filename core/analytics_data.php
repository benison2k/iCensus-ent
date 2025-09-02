<?php
session_start();
$config = require __DIR__ . '/../core/config.php';
require __DIR__ . '/../core/Database.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Connect to database
try {
    $db = new Database($config);
    $pdo = $db->getPdo();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$chart_type = $_GET['chart_type'] ?? '';
$data_source = $_GET['data_source'] ?? '';

$data = [];
$labels = [];
$status = 'error';
$message = 'Invalid data source.';

switch ($data_source) {
    case 'gender':
        $stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM residents GROUP BY gender");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $labels[] = $row['gender'];
            $data[] = $row['count'];
        }
        $status = 'success';
        $message = 'Data fetched successfully.';
        break;

    case 'age_group':
        // Age group analysis (example ranges: 0-17, 18-35, 36-60, 61+)
        $stmt = $pdo->query("SELECT dob FROM residents");
        $residents_dob = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $age_groups = ['0-17' => 0, '18-35' => 0, '36-60' => 0, '61+' => 0];
        $current_year = date('Y');

        foreach ($residents_dob as $dob) {
            $age = $current_year - (new DateTime($dob))->format('Y');
            if ($age >= 0 && $age <= 17) {
                $age_groups['0-17']++;
            } elseif ($age >= 18 && $age <= 35) {
                $age_groups['18-35']++;
            } elseif ($age >= 36 && $age <= 60) {
                $age_groups['36-60']++;
            } else {
                $age_groups['61+']++;
            }
        }
        $labels = array_keys($age_groups);
        $data = array_values($age_groups);
        $status = 'success';
        $message = 'Data fetched successfully.';
        break;

    case 'status':
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM residents GROUP BY status");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $labels[] = $row['status'];
            $data[] = $row['count'];
        }
        $status = 'success';
        $message = 'Data fetched successfully.';
        break;
}

echo json_encode([
    'status' => $status,
    'message' => $message,
    'labels' => $labels,
    'data' => $data
]);