<?php
session_start();
header('Content-Type: application/json');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Fetch all residents
    $stmt = $pdo->query("SELECT * FROM residents");
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate age for each resident
    foreach ($residents as &$resident) {
        $dob = new DateTime($resident['dob']);
        $now = new DateTime();
        $resident['age'] = $now->diff($dob)->y;
    }

    // Prepare analytics data
    $analyticsData = [
        'totalResidents' => count($residents),
        'genderDistribution' => array_count_values(array_column($residents, 'gender')),
        'purokDistribution' => array_count_values(array_column($residents, 'purok')),
        'barangayDistribution' => array_count_values(array_column($residents, 'barangay')),
        'statusDistribution' => array_count_values(array_column($residents, 'status')),
        'civilStatusDistribution' => array_count_values(array_column($residents, 'civil_status')),
        'ageDistribution' => [
            '0-17' => 0,
            '18-35' => 0,
            '36-59' => 0,
            '60+' => 0,
        ],
        'seniorCount' => 0,
        'maleCount' => 0,
        'femaleCount' => 0
    ];

    foreach ($residents as $resident) {
        if ($resident['age'] >= 0 && $resident['age'] <= 17) {
            $analyticsData['ageDistribution']['0-17']++;
        } elseif ($resident['age'] >= 18 && $resident['age'] <= 35) {
            $analyticsData['ageDistribution']['18-35']++;
        } elseif ($resident['age'] >= 36 && $resident['age'] <= 59) {
            $analyticsData['ageDistribution']['36-59']++;
        } else {
            $analyticsData['ageDistribution']['60+']++;
        }
        
        if($resident['age'] >= 60){
            $analyticsData['seniorCount']++;
        }
        
        if($resident['gender'] === 'Male'){
            $analyticsData['maleCount']++;
        } else if ($resident['gender'] === 'Female'){
            $analyticsData['femaleCount']++;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $analyticsData]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}