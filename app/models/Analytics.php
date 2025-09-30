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
            return []; // Return empty array if default is missing
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

    /**
     * This is the complete and final version of getChartData,
     * containing all the logic from the original analytics_data.php file.
     */
    public function getChartData($metric) {
        $stmt = $this->pdo->query("SELECT dob, gender, civil_status, blood_type, nationality, status, relationship, head_of_household, purok FROM residents");
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];

        $calculateAge = function($dob) {
            if (!$dob || $dob === '0000-00-00') return null;
            return (new DateTime($dob))->diff(new DateTime('today'))->y;
        };

        $getGeneration = function($dob) {
            if (!$dob || $dob === '0000-00-00') return 'Unknown';
            $birthYear = (int)date('Y', strtotime($dob));
            if ($birthYear <= 1900) return 'Unknown';
            if ($birthYear >= 2013) return 'Gen Alpha';
            if ($birthYear >= 1997) return 'Gen Z';
            if ($birthYear >= 1981) return 'Millennials';
            if ($birthYear >= 1965) return 'Gen X';
            if ($birthYear >= 1946) return 'Baby Boomers';
            return 'Older Generations';
        };

        foreach ($residents as &$resident) {
            $resident['age'] = $calculateAge($resident['dob']);
        }
        unset($resident);

        switch ($metric) {
            case 'gender':
            case 'civil_status':
            case 'blood_type':
            case 'nationality':
            case 'purok':
            case 'relationship':
                foreach ($residents as $r) $data[trim($r[$metric]) ?: 'Unknown'] = ($data[trim($r[$metric]) ?: 'Unknown'] ?? 0) + 1;
                break;

            case 'age':
                $ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0, 'Unknown' => 0];
                foreach ($residents as $r) {
                    if ($r['age'] === null) $ageGroups['Unknown']++;
                    elseif ($r['age'] <= 17) $ageGroups['0-17']++;
                    elseif ($r['age'] <= 35) $ageGroups['18-35']++;
                    elseif ($r['age'] <= 59) $ageGroups['36-59']++;
                    else $ageGroups['60+']++;
                }
                $data = $ageGroups;
                break;
            
            case 'generation_breakdown':
                 foreach($residents as $r) $data[$getGeneration($r['dob'])] = ($data[$getGeneration($r['dob'])] ?? 0) + 1;
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
            
            case 'average_household_size':
                $households = [];
                foreach($residents as $r) {
                    $head = trim($r['head_of_household']) ?: ($r['first_name'] . ' ' . $r['last_name']);
                    $households[$head] = 1;
                }
                $total_members = count($residents);
                $total_households = count($households);
                $avg = $total_households > 0 ? round($total_members / $total_households, 2) : 0;
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
                $male = 0; $female = 0;
                foreach ($residents as $r) {
                    if (strtolower($r['gender']) == 'male') $male++;
                    if (strtolower($r['gender']) == 'female') $female++;
                }
                $data = ['Male' => $male, 'Female' => $female];
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
            
            default:
                $data = ['error' => 'Invalid metric specified: ' . htmlspecialchars($metric)];
                break;
        }
        return $data;
    }
}