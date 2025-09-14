<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

$metric = $_GET['metric'] ?? '';

if (empty($metric)) {
    echo json_encode(['error' => 'Metric not specified']);
    http_response_code(400);
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();

function calculateAge($dob) {
    if (!$dob || $dob === '0000-00-00') return null;
    try {
        $dobDate = new DateTime($dob);
        $today = new DateTime('today');
        return $dobDate->diff($today)->y;
    } catch (Exception $e) {
        return null;
    }
}

$stmt = $pdo->query("SELECT dob, gender, status, purok, barangay, civil_status, blood_type, residency_status, nationality, relationship FROM residents");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];

foreach ($residents as $resident) {
    $age = calculateAge($resident['dob']);

    switch($metric) {
        // --- Existing Metrics ---
        case 'gender':
            $gender = !empty($resident['gender']) ? ucfirst(strtolower($resident['gender'])) : 'Other';
            $data[$gender] = ($data[$gender] ?? 0) + 1;
            break;
        case 'age':
            if ($age !== null) {
                if ($age <= 17) $ageGroups['0-17']++;
                elseif ($age <= 35) $ageGroups['18-35']++;
                elseif ($age <= 59) $ageGroups['36-59']++;
                else $ageGroups['60+']++;
            }
            break;
        case 'status':
            $status = !empty($resident['status']) ? $resident['status'] : 'Unknown';
            $data[$status] = ($data[$status] ?? 0) + 1;
            break;
        case 'purok':
            $purok = !empty($resident['purok']) ? $resident['purok'] : 'Unknown';
            $data[$purok] = ($data[$purok] ?? 0) + 1;
            break;
        case 'barangay':
            $barangay = !empty($resident['barangay']) ? $resident['barangay'] : 'Unknown';
            $data[$barangay] = ($data[$barangay] ?? 0) + 1;
            break;
        case 'civil_status':
             $civilStatus = !empty($resident['civil_status']) ? $resident['civil_status'] : 'Unknown';
             $data[$civilStatus] = ($data[$civilStatus] ?? 0) + 1;
             break;
        case 'blood_type':
            $bloodType = !empty($resident['blood_type']) ? $resident['blood_type'] : 'Unknown';
            $data[$bloodType] = ($data[$bloodType] ?? 0) + 1;
            break;
        case 'residency_status':
            $residencyStatus = !empty($resident['residency_status']) ? $resident['residency_status'] : 'Unknown';
            $data[$residencyStatus] = ($data[$residencyStatus] ?? 0) + 1;
            break;

        // --- NEW METRICS ---
        case 'nationality':
            $nationality = !empty($resident['nationality']) ? $resident['nationality'] : 'Unknown';
            $data[$nationality] = ($data[$nationality] ?? 0) + 1;
            break;
        case 'relationship':
            $relationship = !empty($resident['relationship']) ? $resident['relationship'] : 'Unknown';
            $data[$relationship] = ($data[$relationship] ?? 0) + 1;
            break;
        case 'voter_status':
            if ($age !== null) {
                $status = ($age >= 18) ? 'Voter' : 'Non-Voter';
                $data[$status] = ($data[$status] ?? 0) + 1;
            }
            break;
        case 'senior_citizens':
            if ($age !== null) {
                $status = ($age >= 60) ? 'Senior (60+)' : 'Non-Senior';
                $data[$status] = ($data[$status] ?? 0) + 1;
            }
            break;
        case 'youth_bracket':
            if ($age !== null) {
                $status = ($age >= 15 && $age <= 24) ? 'Youth (15-24)' : 'Other';
                $data[$status] = ($data[$status] ?? 0) + 1;
            }
            break;
        case 'toddlers':
            if ($age !== null) {
                $status = ($age >= 0 && $age <= 4) ? 'Toddler (0-4)' : 'Other';
                $data[$status] = ($data[$status] ?? 0) + 1;
            }
            break;
    }
}

if ($metric === 'age') {
    echo json_encode($ageGroups);
} else {
    echo json_encode($data);
}