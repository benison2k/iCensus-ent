<?php
session_start();

// --- STEP 1: Aggressive Error Control ---
// Prevent any PHP warnings/errors from being sent to the browser and breaking the JSON.
error_reporting(0);
ini_set('display_errors', 0);

// --- STEP 2: Set Content-Type Header Early ---
// Ensure the browser knows to expect JSON.
header('Content-Type: application/json');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Fetch all potentially relevant columns
    $stmt = $pdo->query("SELECT gender, dob, civil_status, purok, blood_type, status FROM residents");
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- STEP 3: Safe Data Processing ---

    // Initialize counters
    $genderCounts = [];
    $ageGroups = ['0-17' => 0, '18-30' => 0, '31-50' => 0, '51-65' => 0, '66+' => 0, 'Unknown' => 0];
    $civilStatusCounts = [];
    $purokCounts = [];
    $bloodTypeCounts = [];
    $statusCounts = [];

    // Loop through each resident and safely categorize their data
    foreach ($residents as $r) {
        // Safely count gender
        $gender = !empty($r['gender']) ? trim($r['gender']) : 'N/A';
        $genderCounts[$gender] = ($genderCounts[$gender] ?? 0) + 1;
        
        // Safely count civil status
        $civilStatus = !empty($r['civil_status']) ? trim($r['civil_status']) : 'N/A';
        $civilStatusCounts[$civilStatus] = ($civilStatusCounts[$civilStatus] ?? 0) + 1;
        
        // Safely count purok
        $purok = !empty($r['purok']) ? trim($r['purok']) : 'N/A';
        $purokCounts[$purok] = ($purokCounts[$purok] ?? 0) + 1;

        // Safely count blood type
        $bloodType = !empty($r['blood_type']) ? trim($r['blood_type']) : 'N/A';
        $bloodTypeCounts[$bloodType] = ($bloodTypeCounts[$bloodType] ?? 0) + 1;
        
        // Safely count status
        $status = !empty($r['status']) ? trim($r['status']) : 'N/A';
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

        // --- FIX: Robust Age Calculation ---
        if (!empty($r['dob']) && $r['dob'] !== '0000-00-00') {
            // Use try-catch for date creation as an extra layer of safety
            try {
                $dob = new DateTime($r['dob']);
                $age = $dob->diff(new DateTime())->y;

                if ($age <= 17) $ageGroups['0-17']++;
                elseif ($age <= 30) $ageGroups['18-30']++;
                elseif ($age <= 50) $ageGroups['31-50']++;
                elseif ($age <= 65) $ageGroups['51-65']++;
                else $ageGroups['66+']++;
            } catch (Exception $e) {
                // If date parsing fails for any reason, count as Unknown
                $ageGroups['Unknown']++;
            }
        } else {
            $ageGroups['Unknown']++; // Count null, empty, or '0000-00-00' DOBs
        }
    }

    // --- STEP 4: Format for Google Charts ---
    // This part converts the PHP arrays into the specific array format Google Charts needs.
    
    $genderData = [['Gender', 'Count']];
    foreach($genderCounts as $key => $val) { $genderData[] = [$key, $val]; }

    $ageGroupData = [['Age Group', 'Count']];
    foreach($ageGroups as $key => $val) { $ageGroupData[] = [$key, $val]; }
    
    $civilStatusData = [['Civil Status', 'Count']];
    foreach($civilStatusCounts as $key => $val) { $civilStatusData[] = [$key, $val]; }
    
    $purokData = [['Purok', 'Count']];
    foreach($purokCounts as $key => $val) { 
        // Skip 'N/A' for purok or format it nicely
        if ($key === 'N/A') continue;
        $purokData[] = ['Purok ' . $key, $val];
    }
    
    $bloodTypeData = [['Blood Type', 'Count']];
    foreach($bloodTypeCounts as $key => $val) { $bloodTypeData[] = [$key, $val]; }

    $statusData = [['Status', 'Count']];
    foreach($statusCounts as $key => $val) { $statusData[] = [$key, $val]; }


    // --- STEP 5: Final JSON Output ---
    echo json_encode([
        'gender' => $genderData,
        'age_groups' => $ageGroupData,
        'civil_status' => $civilStatusData,
        'purok' => $purokData,
        'blood_type' => $bloodTypeData,
        'status' => $statusData
    ]);

} catch (Exception $e) {
    // If the database connection or query fails, send a clean JSON error.
    http_response_code(500);
    // Log the actual error to your server's log file for debugging
    error_log("Analytics Data Error: " . $e->getMessage()); 
    echo json_encode(['error' => 'A server error occurred while fetching chart data.']);
}
?>