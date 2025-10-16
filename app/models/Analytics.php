<?php
// /app/models/Analytics.php

class Analytics {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    public function getDistinct($column) {
        $stmt = $this->pdo->prepare("SELECT DISTINCT {$column} FROM residents WHERE {$column} IS NOT NULL AND {$column} != '' ORDER BY {$column}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getLayoutForUser($userId) {
        $stmt = $this->pdo->prepare("SELECT layout FROM user_analytics_layouts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetchColumn();
        $layout = json_decode($result);

        if (!$result || empty($layout)) {
            $defaultLayoutPath = __DIR__ . '/../../config/default_layout.json';
            if (file_exists($defaultLayoutPath)) {
                return json_decode(file_get_contents($defaultLayoutPath));
            }
            return [];
        }
        return $layout;
    }
    
    public function saveLayoutForUser($userId, $layoutData) {
        $stmt = $this->pdo->prepare("INSERT INTO user_analytics_layouts (user_id, layout) VALUES (?, ?) ON DUPLICATE KEY UPDATE layout = ?");
        return $stmt->execute([$userId, $layoutData, $layoutData]);
    }

    public function deleteLayoutForUser($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM user_analytics_layouts WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function getChartData($metric, $startDate = null, $endDate = null) {
        // Base query with approval status filter
        $sql = "SELECT * FROM residents WHERE approval_status = 'approved'";
        $params = [];

        // --- THIS IS THE FIX: Changed date_added to date_approved ---
        if ($startDate && $endDate && !empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(date_approved) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];

        // Helper functions
        $calculateAge = fn($dob) => ($dob && $dob !== '0000-00-00') ? (new DateTime($dob))->diff(new DateTime('today'))->y : null;
        $getGeneration = function($dob) {
            if (!$dob) return 'Unknown';
            $birthYear = (int)date('Y', strtotime($dob));
            if ($birthYear >= 2013) return 'Gen Alpha'; if ($birthYear >= 1997) return 'Gen Z';
            if ($birthYear >= 1981) return 'Millennials'; if ($birthYear >= 1965) return 'Gen X';
            if ($birthYear >= 1946) return 'Baby Boomers'; return 'Older';
        };

        // Pre-calculate ages
        foreach ($residents as &$r) { $r['age'] = $calculateAge($r['dob']); }
        unset($r);

        switch ($metric) {
            // SIMPLE COUNTS
            case 'gender': case 'civil_status': case 'blood_type': case 'nationality': case 'purok': case 'relationship':
            case 'resident_status_overview': case 'educational_attainment': case 'ownership_status':
                $column = ($metric === 'resident_status_overview') ? 'status' : $metric;
                foreach ($residents as $r) $data[trim($r[$column]) ?: 'Unknown'] = ($data[trim($r[$column]) ?: 'Unknown'] ?? 0) + 1;
                break;
            
            case 'occupation':
                foreach ($residents as $r) {
                    $occupation = trim($r['occupation']);
                    // Group empty, null, 'N/A', and explicit 'Unemployed' values together
                    if (empty($occupation) || strtolower($occupation) === 'n/a' || strtolower($occupation) === 'unemployed' || strtolower($occupation) === 'student') {
                        $key = 'Unemployed';
                    } else {
                        $key = $occupation;
                    }
                    $data[$key] = ($data[$key] ?? 0) + 1;
                }
                arsort($data);
                $data = array_slice($data, 0, 15); // Get top 15
                break;
            
            // --- NEW: BOOLEAN/SOCIAL WELFARE COUNTS ---
            case 'pwd_distribution':
            case 'solo_parent_distribution':
            case '4ps_distribution':
                $column_map = [
                    'pwd_distribution' => 'is_pwd',
                    'solo_parent_distribution' => 'is_solo_parent',
                    '4ps_distribution' => 'is_4ps_member'
                ];
                $col = $column_map[$metric];
                $yes_count = count(array_filter($residents, fn($r) => $r[$col] == 1));
                $data = ['Yes' => $yes_count, 'No' => count($residents) - $yes_count];
                break;


            // AGE-BASED
            case 'age':
                $groups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
                foreach ($residents as $r) {
                    if ($r['age'] === null) continue;
                    if ($r['age'] <= 17) $groups['0-17']++; elseif ($r['age'] <= 35) $groups['18-35']++;
                    elseif ($r['age'] <= 59) $groups['36-59']++; else $groups['60+']++;
                }
                $data = $groups;
                break;
            case 'detailed_age_brackets':
                $brackets = ['0-9'=>0, '10-19'=>0, '20-29'=>0, '30-39'=>0, '40-49'=>0, '50-59'=>0, '60-69'=>0, '70-79'=>0, '80+'=>0];
                foreach ($residents as $r) {
                    if($r['age'] === null) continue;
                    $key = floor($r['age'] / 10);
                    $bracket_name = ($key*10).'-'.($key*10+9);
                    if($key >= 8) $bracket_name = '80+';
                    if(isset($brackets[$bracket_name])) $brackets[$bracket_name]++;
                }
                $data = $brackets;
                break;
            case 'generation_breakdown':
                 foreach($residents as $r) $data[$getGeneration($r['dob'])] = ($data[$getGeneration($r['dob'])] ?? 0) + 1;
                break;
            case 'population_pyramid':
                $pyramid = []; $brackets = ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80+'];
                foreach($brackets as $b) $pyramid[$b] = ['Male' => 0, 'Female' => 0];
                foreach($residents as $r) {
                    if ($r['age'] === null || empty($r['gender'])) continue;
                    $key = floor($r['age'] / 10);
                    if($key >= 8) $key = 8;
                    $gender = ucfirst(strtolower($r['gender']));
                    if(isset($pyramid[$brackets[$key]][$gender])) $pyramid[$brackets[$key]][$gender]++;
                }
                $data = $pyramid;
                break;

            // KPIS
            case 'average_age_of_residents':
                $ages = array_column(array_filter($residents, fn($r) => $r['age'] !== null), 'age');
                $avg = count($ages) > 0 ? round(array_sum($ages) / count($ages), 1) : 0;
                $data = ['value' => $avg, 'label' => 'Average Resident Age'];
                break;
            case 'average_household_size':
                $heads = array_filter($residents, fn($r) => strtolower(trim($r['relationship'])) === 'self');
                $avg = count($heads) > 0 ? round(count($residents) / count($heads), 2) : 0;
                $data = ['value' => $avg, 'label' => 'Average Household Size'];
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
            case 'sex_ratio':
                $male = count(array_filter($residents, fn($r) => strtolower($r['gender']) == 'male'));
                $female = count(array_filter($residents, fn($r) => strtolower($r['gender']) == 'female'));
                $data = ['Male' => $male, 'Female' => $female];
                break;

            // GROUPED CHARTS
            case 'civil_status_distribution_by_gender':
                $civilStatusByGender = [];
                foreach($residents as $r) {
                    $status = trim($r['civil_status']) ?: 'Unknown';
                    $gender = ucfirst(strtolower($r['gender'])) ?: 'Unknown';
                    if (!isset($civilStatusByGender[$status])) $civilStatusByGender[$status] = ['Male' => 0, 'Female' => 0];
                    if (isset($civilStatusByGender[$status][$gender])) $civilStatusByGender[$status][$gender]++;
                }
                $data = $civilStatusByGender;
                break;
             case 'school_age_population_by_purok':
                $schoolAge = [];
                foreach($residents as $r) {
                    $purok = trim($r['purok']) ?: 'Unknown';
                    if (!isset($schoolAge[$purok])) $schoolAge[$purok] = ['Daycare (0-4)'=>0, 'Elementary (5-11)'=>0, 'High School (12-17)'=>0];
                    if ($r['age'] !== null) {
                        if($r['age'] <= 4) $schoolAge[$purok]['Daycare (0-4)']++;
                        else if($r['age'] <= 11) $schoolAge[$purok]['Elementary (5-11)']++;
                        else if($r['age'] <= 17) $schoolAge[$purok]['High School (12-17)']++;
                    }
                }
                $data = $schoolAge;
                break;
            
            // OTHER
            case 'voter_population_by_purok': case 'senior_citizens_by_purok':
                $byPurok = [];
                foreach($residents as $r) {
                    $purok = trim($r['purok']) ?: 'Unknown';
                    if(!isset($byPurok[$purok])) $byPurok[$purok] = 0;
                    if($r['age'] !== null) {
                        if($metric === 'voter_population_by_purok' && $r['age'] >= 18) $byPurok[$purok]++;
                        if($metric === 'senior_citizens_by_purok' && $r['age'] >= 60) $byPurok[$purok]++;
                    }
                }
                $data = $byPurok;
                break;
            case 'residents_per_street':
                $streets = [];
                foreach($residents as $r) $streets[trim($r['street']) ?: 'Unknown'] = ($streets[trim($r['street']) ?: 'Unknown'] ?? 0) + 1;
                arsort($streets);
                $data = array_slice($streets, 0, 10);
                break;
             case 'household_size_distribution':
                $households = [];
                foreach($residents as $r) {
                    $head = trim($r['head_of_household']) ?: ($r['first_name'] . ' ' . $r['last_name']);
                    if (!isset($households[$head])) $households[$head] = 0;
                    $households[$head]++;
                }
                $sizes = ['1 person'=>0, '2 people'=>0, '3 people'=>0, '4 people'=>0, '5+ people'=>0];
                foreach($households as $size) {
                    if($size >= 5) $sizes['5+ people']++;
                    else $sizes[$size . ' ' . ($size > 1 ? 'people' : 'person')]++;
                }
                $data = $sizes;
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

            // DATA HEALTH
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
            case 'emergency_contact_coverage':
                $with = count(array_filter($residents, fn($r) => !empty(trim($r['emergency_name']))));
                $data = ['Has Contact' => $with, 'None' => count($residents) - $with];
                break;

            default:
                $data = ['error' => 'Metric not found: ' . htmlspecialchars($metric)];
                break;
        }
        return $data;
    }

    public function getDataForReport($postData) {
        $sort_by = $postData['sort_by'] ?? 'last_name';
        $sort_order = $postData['sort_order'] ?? 'ASC';
        $selected_columns = $postData['columns'] ?? [];
        $selected_chart_ids = $postData['charts'] ?? [];
    
        // Whitelist allowed columns to prevent SQL injection
        $allowed_sort_columns = ['last_name', 'first_name', 'date_added', 'dob'];
        if (!in_array($sort_by, $allowed_sort_columns)) {
            $sort_by = 'last_name';
        }
    
        $all_columns = [
            'full_name' => ['label' => 'Full Name', 'sql' => "CONCAT(first_name, ' ', last_name)"],
            'address' => ['label' => 'Full Address', 'sql' => "CONCAT(house_no, ' ', street, ', Purok ', purok)"],
            'dob' => ['label' => 'Date of Birth', 'sql' => 'dob'],
            'age' => ['label' => 'Age', 'sql' => 'TIMESTAMPDIFF(YEAR, dob, CURDATE())'],
            'gender' => ['label' => 'Gender', 'sql' => 'gender'],
            'civil_status' => ['label' => 'Civil Status', 'sql' => 'civil_status'],
            'contact_number' => ['label' => 'Contact Number', 'sql' => 'contact_number'],
            'email' => ['label' => 'Email', 'sql' => 'email'],
            'blood_type' => ['label' => 'Blood Type', 'sql' => 'blood_type'],
            'nationality' => ['label' => 'Nationality', 'sql' => 'nationality'],
            'status' => ['label' => 'Resident Status', 'sql' => 'status'],
            'date_added' => ['label' => 'Date Added', 'sql' => 'date_added']
        ];
        
        $columns_to_select = [];
        $report_headers = [];
        if (!empty($selected_columns)) {
            foreach ($selected_columns as $col_key) {
                if (array_key_exists($col_key, $all_columns)) {
                    $columns_to_select[] = $all_columns[$col_key]['sql'] . " AS `$col_key`";
                    $report_headers[$col_key] = $all_columns[$col_key]['label'];
                }
            }
        }
        
        $results = [];
        if (!empty($columns_to_select)) {
            $sql = "SELECT " . implode(', ', $columns_to_select) . " FROM residents WHERE approval_status = 'approved' ORDER BY $sort_by $sort_order";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        // --- Chart Data Fetching ---
        $chart_reports = [];
        if (!empty($selected_chart_ids)) {
            $chartModel = new Chart(new Database(require __DIR__ . '/../../config/database.php'));
            foreach($selected_chart_ids as $chartId) {
                $chartDef = $chartModel->find($chartId);
                if ($chartDef) {
                    $chartData = $chartModel->getDataForChart($chartDef);
                    $chart_reports[] = [
                        'id' => $chartId,
                        'title' => $chartDef['title'],
                        'type' => $chartDef['chart_type'],
                        'data' => $chartData,
                    ];
                }
            }
        }
    
        return [
            'results' => $results,
            'headers' => $report_headers,
            'charts' => $chart_reports,
        ];
    }
}