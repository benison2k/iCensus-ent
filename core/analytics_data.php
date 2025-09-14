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

// --- Robust Helper Functions ---
function calculateAge($dob) {
    if (!$dob || $dob === '0000-00-00') return null;
    try {
        $dobDate = new DateTime($dob);
        $today = new DateTime('today');
        if ($dobDate > $today) return 0; // Handle future dates gracefully
        return $dobDate->diff($today)->y;
    } catch (Exception $e) {
        // Log error: error_log("Invalid date format for DOB: $dob");
        return null;
    }
}

function getGeneration($dob) {
    if (!$dob || $dob === '0000-00-00') return 'Unknown';
    $birthYear = (int)date('Y', strtotime($dob));
    if ($birthYear <= 1900) return 'Unknown';

    if ($birthYear >= 2013) return 'Gen Alpha';
    if ($birthYear >= 1997) return 'Gen Z';
    if ($birthYear >= 1981) return 'Millennials';
    if ($birthYear >= 1965) return 'Gen X';
    if ($birthYear >= 1946) return 'Baby Boomers';
    if ($birthYear >= 1928) return 'Silent Gen';
    return 'Oldest Gen';
}

try {
    $db = new Database($config);
    $pdo = $db->getPdo();

    // Fetch all necessary data in one go
    $stmt = $pdo->query("SELECT first_name, last_name, dob, gender, civil_status, blood_type, nationality, status, relationship, head_of_household, purok, barangay, street, contact_number, email, emergency_name FROM residents");
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pre-calculate ages for efficiency and consistency
    foreach ($residents as &$resident) {
        $resident['age'] = calculateAge($resident['dob'] ?? null);
    }
    unset($resident);

    $data = [];

    switch ($metric) {
        // --- DEMOGRAPHICS ---
        case 'gender':
            foreach ($residents as $r) $data[$r['gender'] ?: 'Unknown'] = ($data[$r['gender'] ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'age':
            $ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
            foreach ($residents as $r) {
                if ($r['age'] === null) continue;
                if ($r['age'] <= 17) $ageGroups['0-17']++;
                elseif ($r['age'] <= 35) $ageGroups['18-35']++;
                elseif ($r['age'] <= 59) $ageGroups['36-59']++;
                else $ageGroups['60+']++;
            }
            $data = $ageGroups;
            break;
        case 'generation_breakdown':
            foreach($residents as $r) $data[getGeneration($r['dob'])] = ($data[getGeneration($r['dob'])] ?? 0) + 1;
            break;
        case 'detailed_age_brackets':
            $brackets = ['0-9'=>0, '10-19'=>0, '20-29'=>0, '30-39'=>0, '40-49'=>0, '50-59'=>0, '60-69'=>0, '70-79'=>0, '80+'=>0];
            foreach ($residents as $r) {
                if($r['age'] === null) continue;
                if($r['age'] <= 9) $brackets['0-9']++;
                else if($r['age'] <= 19) $brackets['10-19']++;
                else if($r['age'] <= 29) $brackets['20-29']++;
                else if($r['age'] <= 39) $brackets['30-39']++;
                else if($r['age'] <= 49) $brackets['40-49']++;
                else if($r['age'] <= 59) $brackets['50-59']++;
                else if($r['age'] <= 69) $brackets['60-69']++;
                else if($r['age'] <= 79) $brackets['70-79']++;
                else $brackets['80+']++;
            }
            $data = $brackets;
            break;
        case 'civil_status':
            foreach ($residents as $r) $data[trim($r['civil_status']) ?: 'Unknown'] = ($data[trim($r['civil_status']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'nationality':
            foreach ($residents as $r) $data[trim($r['nationality']) ?: 'Unknown'] = ($data[trim($r['nationality']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'blood_type':
            foreach ($residents as $r) $data[trim($r['blood_type']) ?: 'Unknown'] = ($data[trim($r['blood_type']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'resident_status_overview':
            foreach ($residents as $r) $data[$r['status'] ?: 'Unknown'] = ($data[$r['status'] ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'sex_ratio':
            $male = 0; $female = 0;
            foreach ($residents as $r) {
                if (strtolower($r['gender']) == 'male') $male++;
                if (strtolower($r['gender']) == 'female') $female++;
            }
            $data = ['Male' => $male, 'Female' => $female];
            break;
        case 'dependency_ratio':
            $dependents = 0; $working_age = 0;
            foreach ($residents as $r) {
                if ($r['age'] !== null) {
                    if (($r['age'] <= 14) || ($r['age'] >= 65)) $dependents++;
                    else if ($r['age'] >= 15 && $r['age'] <= 64) $working_age++;
                }
            }
            $ratio = $working_age > 0 ? round(($dependents / $working_age) * 100, 2) : 0;
            $data = ['value' => $ratio . '%', 'label' => 'Dependents per 100 working-age'];
            break;
        case 'population_pyramid':
            $pyramid = [];
            $ageBrackets = ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80+'];
            foreach($ageBrackets as $bracket) $pyramid[$bracket] = ['Male' => 0, 'Female' => 0];

            foreach($residents as $r) {
                if ($r['age'] === null || empty($r['gender'])) continue;
                $bracketKey = floor($r['age'] / 10);
                if($bracketKey >= 8) $bracketKey = 8;
                $bracket = $ageBrackets[$bracketKey];
                $gender = ucfirst(strtolower($r['gender']));
                if(isset($pyramid[$bracket][$gender])) {
                   $pyramid[$bracket][$gender]++;
                }
            }
            $data = $pyramid;
            break;
        case 'average_age_of_residents':
            $total_age = 0; $count = 0;
            foreach ($residents as $r) {
                if ($r['age'] !== null) {
                    $total_age += $r['age'];
                    $count++;
                }
            }
            $avg = $count > 0 ? round($total_age / $count, 1) : 0;
            $data = ['value' => $avg, 'label' => 'Average Resident Age'];
            break;

        // --- HOUSEHOLD ---
        case 'relationship':
            foreach ($residents as $r) $data[trim($r['relationship']) ?: 'Unknown'] = ($data[trim($r['relationship']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'household_size_distribution':
            $households = [];
            foreach($residents as $r) {
                $head = trim($r['head_of_household']) ?: ($r['first_name'] . ' ' . $r['last_name']); // Fallback to self
                if (!isset($households[$head])) $households[$head] = 0;
                $households[$head]++;
            }
            $sizes = ['1 person'=>0, '2 people'=>0, '3 people'=>0, '4 people'=>0, '5+ people'=>0];
            foreach($households as $size) {
                if($size == 1) $sizes['1 person']++;
                else if($size == 2) $sizes['2 people']++;
                else if($size == 3) $sizes['3 people']++;
                else if($size == 4) $sizes['4 people']++;
                else $sizes['5+ people']++;
            }
            $data = $sizes;
            break;
        case 'average_household_size':
            $households = [];
            foreach($residents as $r) {
                $head = trim($r['head_of_household']) ?: ($r['first_name'] . ' ' . $r['last_name']);
                $households[$head] = 1; // Just mark that this household exists
            }
            $total_members = count($residents);
            $total_households = count($households);
            $avg = $total_households > 0 ? round($total_members / $total_households, 2) : 0;
            $data = ['value' => $avg, 'label' => 'Average Household Size'];
            break;
        case 'heads_of_household_by_gender':
            $heads = [];
            foreach($residents as $r) {
                if(strtolower(trim($r['relationship'])) == 'self') {
                    $gender = $r['gender'] ?: 'Unknown';
                    $heads[$gender] = ($heads[$gender] ?? 0) + 1;
                }
            }
            $data = $heads;
            break;
        case 'civil_status_distribution_by_gender':
            $civilStatusByGender = [];
            $statuses = array_unique(array_column($residents, 'civil_status'));
            foreach($statuses as $status) {
                if(empty(trim($status))) $status = 'Unknown';
                $civilStatusByGender[trim($status)] = ['Male' => 0, 'Female' => 0];
            }
             if(!isset($civilStatusByGender['Unknown'])) $civilStatusByGender['Unknown'] = ['Male' => 0, 'Female' => 0];

            foreach($residents as $r) {
                $status = trim($r['civil_status']) ?: 'Unknown';
                $gender = ucfirst(strtolower($r['gender']));
                if(isset($civilStatusByGender[$status][$gender])) {
                    $civilStatusByGender[$status][$gender]++;
                }
            }
            $data = $civilStatusByGender;
            break;


        // --- LOCATION ---
        case 'purok':
            foreach ($residents as $r) $data[trim($r['purok']) ?: 'Unknown'] = ($data[trim($r['purok']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'barangay':
            foreach ($residents as $r) $data[trim($r['barangay']) ?: 'Unknown'] = ($data[trim($r['barangay']) ?: 'Unknown'] ?? 0) + 1;
            break;
        case 'residents_per_street':
            $streets = [];
            foreach($residents as $r) $streets[trim($r['street']) ?: 'Unknown'] = ($streets[trim($r['street']) ?: 'Unknown'] ?? 0) + 1;
            arsort($streets);
            $data = array_slice($streets, 0, 10);
            break;
        case 'voter_population_by_purok':
            $votersByPurok = [];
            foreach($residents as $r) {
                $purok = trim($r['purok']) ?: 'Unknown';
                if(!isset($votersByPurok[$purok])) $votersByPurok[$purok] = 0;
                if($r['age'] !== null && $r['age'] >= 18) {
                    $votersByPurok[$purok]++;
                }
            }
            $data = $votersByPurok;
            break;
        case 'senior_citizens_by_purok':
            $seniorsByPurok = [];
            foreach($residents as $r) {
                $purok = trim($r['purok']) ?: 'Unknown';
                if(!isset($seniorsByPurok[$purok])) $seniorsByPurok[$purok] = 0;
                if($r['age'] !== null && $r['age'] >= 60) {
                    $seniorsByPurok[$purok]++;
                }
            }
            $data = $seniorsByPurok;
            break;
        case 'school_age_population_by_purok':
            $schoolAge = [];
            $puroks = array_unique(array_column($residents, 'purok'));
            foreach($puroks as $purok) {
                if(empty(trim($purok))) $purok = 'Unknown';
                $schoolAge[trim($purok)] = ['Daycare (0-4)'=>0, 'Elementary (5-11)'=>0, 'High School (12-17)'=>0];
            }
            if (!isset($schoolAge['Unknown'])) $schoolAge['Unknown'] = ['Daycare (0-4)'=>0, 'Elementary (5-11)'=>0, 'High School (12-17)'=>0];
            
            foreach($residents as $r) {
                $purok = trim($r['purok']) ?: 'Unknown';
                if($r['age'] !== null) {
                    if($r['age'] <= 4) $schoolAge[$purok]['Daycare (0-4)']++;
                    else if($r['age'] <= 11) $schoolAge[$purok]['Elementary (5-11)']++;
                    else if($r['age'] <= 17) $schoolAge[$purok]['High School (12-17)']++;
                }
            }
            $data = $schoolAge;
            break;
        case 'average_age_by_purok':
            $purokData = [];
            foreach($residents as $r) {
                $purok = trim($r['purok']) ?: 'Unknown';
                if(!isset($purokData[$purok])) $purokData[$purok] = ['total_age' => 0, 'count' => 0];
                if($r['age'] !== null) {
                    $purokData[$purok]['total_age'] += $r['age'];
                    $purokData[$purok]['count']++;
                }
            }
            foreach($purokData as $purok => $d) {
                $data[$purok] = $d['count'] > 0 ? round($d['total_age'] / $d['count']) : 0;
            }
            break;
        case 'youth_vs_seniors_by_purok':
            $comparison = [];
            $puroks = array_unique(array_column($residents, 'purok'));
            foreach($puroks as $purok) {
                if(empty(trim($purok))) $purok = 'Unknown';
                $comparison[trim($purok)] = ['Youth (0-17)'=>0, 'Seniors (60+)'=>0];
            }
             if (!isset($comparison['Unknown'])) $comparison['Unknown'] = ['Youth (0-17)'=>0, 'Seniors (60+)'=>0];

            foreach($residents as $r) {
                $purok = trim($r['purok']) ?: 'Unknown';
                if($r['age'] !== null) {
                    if($r['age'] <= 17) $comparison[$purok]['Youth (0-17)']++;
                    else if($r['age'] >= 60) $comparison[$purok]['Seniors (60+)']++;
                }
            }
            $data = $comparison;
            break;

        // --- DATA HEALTH ---
        case 'profile_completeness':
            $completeness = ['Contact Info' => 0, 'Email' => 0, 'Emergency Contact' => 0, 'Blood Type' => 0];
            $total = count($residents);
            if ($total > 0) {
                foreach($residents as $r) {
                    if(!empty(trim($r['contact_number']))) $completeness['Contact Info']++;
                    if(!empty(trim($r['email']))) $completeness['Email']++;
                    if(!empty(trim($r['emergency_name']))) $completeness['Emergency Contact']++;
                    if(!empty(trim($r['blood_type']))) $completeness['Blood Type']++;
                }
                foreach($completeness as $key => $value) {
                    $data[$key] = round(($value / $total) * 100);
                }
            }
            break;
        case 'blood_type_data_coverage':
            $with = 0; $without = 0;
            foreach($residents as $r) {
                if(!empty(trim($r['blood_type'])) && strtolower(trim($r['blood_type'])) != 'unknown') $with++;
                else $without++;
            }
            $data = ['Recorded' => $with, 'Not Recorded' => $without];
            break;
        case 'emergency_contact_coverage':
            $with = 0; $without = 0;
            foreach($residents as $r) {
                if(!empty(trim($r['emergency_name']))) $with++;
                else $without++;
            }
            $data = ['Has Emergency Contact' => $with, 'None' => $without];
            break;

        default:
            $data = ['error' => 'Invalid metric specified: ' . htmlspecialchars($metric)];
            http_response_code(400);
            break;
    }

    echo json_encode($data);

} catch (PDOException $e) {
    // Database connection error
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // General script error
    http_response_code(500);
    echo json_encode(['error' => 'An unexpected error occurred in metric ' . htmlspecialchars($metric) . ': ' . $e->getMessage()]);
}