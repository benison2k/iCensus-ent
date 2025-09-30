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
            // Return the default layout if none is saved
            return json_decode(file_get_contents(__DIR__ . '/../../config/default_layout.json'));
        }
        return $layout;
    }
    
    public function saveLayoutForUser($userId, $layoutData) {
        $stmt = $this->pdo->prepare("INSERT INTO user_analytics_layouts (user_id, layout) VALUES (?, ?) ON DUPLICATE KEY UPDATE layout = ?");
        return $stmt->execute([$userId, $layoutData, $layoutData]);
    }
    
    // A simplified version of your analytics data logic
    public function getChartData($metric) {
        $stmt = $this->pdo->query("SELECT dob, gender, civil_status, purok FROM residents");
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];

        switch ($metric) {
            case 'gender':
                foreach ($residents as $r) $data[$r['gender'] ?: 'Unknown'] = ($data[$r['gender'] ?: 'Unknown'] ?? 0) + 1;
                break;
            case 'age':
                $ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
                foreach ($residents as $r) {
                    if (!$r['dob']) continue;
                    $age = date_diff(date_create($r['dob']), date_create('today'))->y;
                    if ($age <= 17) $ageGroups['0-17']++;
                    elseif ($age <= 35) $ageGroups['18-35']++;
                    elseif ($age <= 59) $ageGroups['36-59']++;
                    else $ageGroups['60+']++;
                }
                $data = $ageGroups;
                break;
            // Add other cases here from your analytics_data.php...
            default:
                $data = ['error' => 'Invalid metric specified'];
                break;
        }
        return $data;
    }
}