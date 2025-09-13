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

    // Fetch residents data
    $stmt = $pdo->query("SELECT * FROM residents");
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch chart settings from the database
    $chartSettingsStmt = $pdo->query("SELECT metric, chart_type FROM chart_settings");
    $chartSettingsData = $chartSettingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fetch user layout
    $layoutStmt = $pdo->prepare("SELECT layout FROM user_analytics_layouts WHERE user_id = ?");
    $layoutStmt->execute([$_SESSION['user']['id']]);
    $userLayout = $layoutStmt->fetchColumn();
    
    // Fetch user chart sizes
    $sizesStmt = $pdo->prepare("SELECT chart_id, size FROM user_chart_settings WHERE user_id = ?");
    $sizesStmt->execute([$_SESSION['user']['id']]);
    $userChartSizes = $sizesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Initialize the complete analytics data structure
    $analyticsData = [
        'totalResidents' => count($residents),
        'genderDistribution' => [],
        'purokDistribution' => [],
        'barangayDistribution' => [],
        'statusDistribution' => [],
        'civilStatusDistribution' => [],
        'bloodTypeDistribution' => [],
        'nationalityDistribution' => [],
        'householdSizeDistribution' => [],
        'totalHouseholds' => 0,
        'ageDistribution' => [
            '0-17' => 0,
            '18-35' => 0,
            '36-59' => 0,
            '60+' => 0,
            'Unknown' => 0
        ],
        'seniorCount' => 0,
        'maleCount' => 0,
        'femaleCount' => 0
    ];

    $households = [];
    foreach ($residents as $resident) {
        // --- Household Calculation ---
        $head = trim($resident['head_of_household']);
        if (!empty($head)) {
            if (!isset($households[$head])) {
                $households[$head] = 0;
            }
            $households[$head]++;
        }

        // --- Age Calculation (Robust) ---
        $age = null;
        if ($resident['dob'] && $resident['dob'] !== '0000-00-00') {
            try {
                $age = (new DateTime($resident['dob']))->diff(new DateTime())->y;
            } catch (Exception $e) {
                $age = null; // Handle invalid date formats
            }
        }

        if ($age !== null) {
            if ($age <= 17) $analyticsData['ageDistribution']['0-17']++;
            elseif ($age <= 35) $analyticsData['ageDistribution']['18-35']++;
            elseif ($age <= 59) $analyticsData['ageDistribution']['36-59']++;
            else $analyticsData['ageDistribution']['60+']++;
            if ($age >= 60) $analyticsData['seniorCount']++;
        } else {
            $analyticsData['ageDistribution']['Unknown']++;
        }

        // --- Other Demographics (with checks for empty values) ---
        if (!empty($resident['gender'])) {
            if (!isset($analyticsData['genderDistribution'][$resident['gender']])) $analyticsData['genderDistribution'][$resident['gender']] = 0;
            $analyticsData['genderDistribution'][$resident['gender']]++;
            if ($resident['gender'] === 'Male') $analyticsData['maleCount']++;
            if ($resident['gender'] === 'Female') $analyticsData['femaleCount']++;
        }
        
        if (!empty($resident['purok'])) {
             if (!isset($analyticsData['purokDistribution'][$resident['purok']])) $analyticsData['purokDistribution'][$resident['purok']] = 0;
            $analyticsData['purokDistribution'][$resident['purok']]++;
        }
        
        if (!empty($resident['barangay'])) {
             if (!isset($analyticsData['barangayDistribution'][$resident['barangay']])) $analyticsData['barangayDistribution'][$resident['barangay']] = 0;
            $analyticsData['barangayDistribution'][$resident['barangay']]++;
        }

        if (!empty($resident['status'])) {
            if (!isset($analyticsData['statusDistribution'][$resident['status']])) $analyticsData['statusDistribution'][$resident['status']] = 0;
            $analyticsData['statusDistribution'][$resident['status']]++;
        }
        
        if (!empty($resident['civil_status'])) {
            if (!isset($analyticsData['civilStatusDistribution'][$resident['civil_status']])) $analyticsData['civilStatusDistribution'][$resident['civil_status']] = 0;
            $analyticsData['civilStatusDistribution'][$resident['civil_status']]++;
        }
        
        if (!empty($resident['blood_type'])) {
            if (!isset($analyticsData['bloodTypeDistribution'][$resident['blood_type']])) $analyticsData['bloodTypeDistribution'][$resident['blood_type']] = 0;
            $analyticsData['bloodTypeDistribution'][$resident['blood_type']]++;
        }

        if (!empty($resident['nationality'])) {
            if (!isset($analyticsData['nationalityDistribution'][$resident['nationality']])) $analyticsData['nationalityDistribution'][$resident['nationality']] = 0;
            $analyticsData['nationalityDistribution'][$resident['nationality']]++;
        }
    }
    
    // --- Final Household Processing ---
    $analyticsData['totalHouseholds'] = count($households);
    foreach ($households as $size) {
        $label = "$size Members";
        if (!isset($analyticsData['householdSizeDistribution'][$label])) {
            $analyticsData['householdSizeDistribution'][$label] = 0;
        }
        $analyticsData['householdSizeDistribution'][$label]++;
    }
    ksort($analyticsData['householdSizeDistribution']);
    
    // Combine analytics data and chart settings into a single response
    $response = [
        'status' => 'success',
        'data' => $analyticsData,
        'chartSettings' => $chartSettingsData,
        'layout' => $userLayout ? json_decode($userLayout) : null,
        'sizes' => $userChartSizes
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}