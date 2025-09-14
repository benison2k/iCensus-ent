<?php
header('Content-Type: application/json');
require __DIR__ . '/Database.php';

$config = require __DIR__ . '/config.php';
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

$stmt = $pdo->query("SELECT gender, dob, status, purok, barangay, civil_status, blood_type, residency_status FROM residents");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$genderData = ['Male' => 0, 'Female' => 0, 'Other' => 0];
$ageGroups = [
    '0-17' => 0,
    '18-35' => 0,
    '36-59' => 0,
    '60+' => 0,
];
$statusData = [];
$purokData = [];
$barangayData = [];
$civilStatusData = [];
$bloodTypeData = [];
$residencyStatusData = [];


foreach ($residents as $resident) {
    // Gender
    $gender = !empty($resident['gender']) ? ucfirst(strtolower($resident['gender'])) : 'Other';
    if (in_array($gender, ['Male', 'Female'])) {
        $genderData[$gender]++;
    } else {
        $genderData['Other']++;
    }

    // Age
    $age = calculateAge($resident['dob']);
    if ($age !== null) {
        if ($age <= 17) $ageGroups['0-17']++;
        elseif ($age <= 35) $ageGroups['18-35']++;
        elseif ($age <= 59) $ageGroups['36-59']++;
        else $ageGroups['60+']++;
    }
    
    // Status
    $status = !empty($resident['status']) ? $resident['status'] : 'Unknown';
    $statusData[$status] = ($statusData[$status] ?? 0) + 1;

    // Purok
    $purok = !empty($resident['purok']) ? $resident['purok'] : 'Unknown';
    $purokData[$purok] = ($purokData[$purok] ?? 0) + 1;

    // Barangay
    $barangay = !empty($resident['barangay']) ? $resident['barangay'] : 'Unknown';
    $barangayData[$barangay] = ($barangayData[$barangay] ?? 0) + 1;
    
    // Civil Status
    $civilStatus = !empty($resident['civil_status']) ? $resident['civil_status'] : 'Unknown';
    $civilStatusData[$civilStatus] = ($civilStatusData[$civilStatus] ?? 0) + 1;

    // Blood Type
    $bloodType = !empty($resident['blood_type']) ? $resident['blood_type'] : 'Unknown';
    $bloodTypeData[$bloodType] = ($bloodTypeData[$bloodType] ?? 0) + 1;
    
    // Residency Status
    $residencyStatus = !empty($resident['residency_status']) ? $resident['residency_status'] : 'Unknown';
    $residencyStatusData[$residencyStatus] = ($residencyStatusData[$residencyStatus] ?? 0) + 1;
}

echo json_encode([
    'gender' => $genderData,
    'age' => $ageGroups,
    'status' => $statusData,
    'purok' => $purokData,
    'barangay' => $barangayData,
    'civil_status' => $civilStatusData,
    'blood_type' => $bloodTypeData,
    'residency_status' => $residencyStatusData,
]);