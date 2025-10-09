<?php
// /app/models/Analytics1.php

class Analytics1 {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }
    
    /**
     * Fetches all charts created by the user for the "Add Widget" library.
     */
    public function getAvailableCharts($userId) {
        $stmt = $this->pdo->prepare("SELECT id, title FROM charts WHERE user_id = ? ORDER BY title ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * The dynamic chart data engine.
     * Reads a chart definition from the database and builds a secure SQL query.
     */
    public function getDynamicChartData($chartId, $userId) {
        // 1. Fetch the chart's definition, ensuring the user owns it.
        $stmt = $this->pdo->prepare("SELECT * FROM charts WHERE id = ? AND user_id = ?");
        $stmt->execute([$chartId, $userId]);
        $chart = $stmt->fetch();

        if (!$chart) {
            return ['error' => 'Chart not found or you do not have permission to view it.'];
        }

        // 2. Validate column names against a whitelist to prevent SQL injection.
        $allowed_columns = [
            'id', '*', 'gender', 'purok', 'civil_status', 'dob', 
            'status', 'educational_attainment', 'occupation', 'ownership_status',
            'is_pwd', 'is_solo_parent', 'is_4ps_member'
        ];

        if (!in_array($chart['aggregate_column'], $allowed_columns) || 
            ($chart['group_by_column'] && !in_array($chart['group_by_column'], $allowed_columns))) {
            return ['error' => 'Invalid column specified in chart definition.'];
        }
        
        // 3. Handle special cases for calculations.
        $agg_func = $chart['aggregate_function'];
        $agg_col = $chart['aggregate_column'];
        
        // If calculating average age, we need to transform the 'dob' column.
        if ($agg_func === 'AVG' && $agg_col === 'dob') {
            $agg_col = 'TIMESTAMPDIFF(YEAR, dob, CURDATE())';
        }

        $group_col = $chart['group_by_column'];

        // 4. Dynamically and safely build the SQL query.
        $base_query = "FROM residents WHERE approval_status = 'approved'";
        if ($group_col) {
            $base_query .= " AND {$group_col} IS NOT NULL AND {$group_col} != ''";
        }

        if ($chart['chart_type'] === 'KPI') {
            $sql = "SELECT {$agg_func}({$agg_col}) as value {$base_query}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return ['value' => round($result['value'], 2) ?? 0, 'label' => $chart['title']];
        } else {
             // Special handling for generation/age brackets which are calculated in PHP
            if ($group_col === 'dob') {
                 $stmt = $this->pdo->prepare("SELECT dob FROM residents WHERE approval_status = 'approved' AND dob IS NOT NULL AND dob != '0000-00-00'");
                 $stmt->execute();
                 $all_residents_dob = $stmt->fetchAll(PDO::FETCH_COLUMN);
                 
                 if (strpos($chart['title'], 'Generation') !== false) {
                     return $this->calculateGenerationData($all_residents_dob);
                 } else { // Assumes Age Brackets
                     return $this->calculateAgeBracketData($all_residents_dob);
                 }
            }

            $sql = "SELECT {$group_col} as category, {$agg_func}({$agg_col}) as value 
                    {$base_query}
                    GROUP BY {$group_col}
                    ORDER BY value DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($results as $row) {
                // Handle boolean (0/1) results for PWD, Solo Parent, etc.
                if (in_array($group_col, ['is_pwd', 'is_solo_parent', 'is_4ps_member'])) {
                    $category = ($row['category'] == 1) ? 'Yes' : 'No';
                    $data[$category] = $row['value'];
                } else {
                    $data[$row['category']] = $row['value'];
                }
            }
            return $data;
        }
    }
    
    public function saveChart($data, $userId) {
        if (empty($data['title']) || empty($data['chart_type']) || empty($data['aggregate_function']) || empty($data['aggregate_column'])) {
            throw new Exception("Missing required chart data.");
        }

        $chartId = $data['chart_id'] ?? null;

        if ($chartId) {
            $stmt = $this->pdo->prepare(
                "UPDATE charts SET title = ?, chart_type = ?, aggregate_function = ?, aggregate_column = ?, group_by_column = ? WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([
                $data['title'], $data['chart_type'], $data['aggregate_function'],
                $data['aggregate_column'], $data['group_by_column'] ?: null, $chartId, $userId
            ]);
            return $chartId;
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO charts (user_id, title, chart_type, aggregate_function, aggregate_column, group_by_column) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId, $data['title'], $data['chart_type'],
                $data['aggregate_function'], $data['aggregate_column'], $data['group_by_column'] ?: null
            ]);
            return $this->pdo->lastInsertId();
        }
    }
    
    private function calculateAgeBracketData($dobs) {
        $brackets = ['0-9'=>0, '10-19'=>0, '20-29'=>0, '30-39'=>0, '40-49'=>0, '50-59'=>0, '60-69'=>0, '70-79'=>0, '80+'=>0];
        foreach ($dobs as $dob) {
            $age = (new DateTime($dob))->diff(new DateTime('today'))->y;
            $key = floor($age / 10);
            $bracket_name = ($key*10).'-'.($key*10+9);
            if($key >= 8) $bracket_name = '80+';
            if(isset($brackets[$bracket_name])) $brackets[$bracket_name]++;
        }
        return $brackets;
    }

    private function calculateGenerationData($dobs) {
        $generations = ['Gen Z' => 0, 'Millennials' => 0, 'Gen X' => 0, 'Baby Boomers' => 0, 'Older' => 0];
        foreach ($dobs as $dob) {
            $birthYear = (int)date('Y', strtotime($dob));
            if ($birthYear >= 1997) $generations['Gen Z']++;
            else if ($birthYear >= 1981) $generations['Millennials']++;
            else if ($birthYear >= 1965) $generations['Gen X']++;
            else if ($birthYear >= 1946) $generations['Baby Boomers']++;
            else $generations['Older']++;
        }
        return $generations;
    }
}