<?php
// /app/models/Analytics1.php

class Analytics1 {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }
    
    public function getAvailableCharts($userId) {
        // Fetch all necessary info for the library and for mapping titles later.
        $stmt = $this->pdo->prepare("SELECT id, title, chart_type, metric_id FROM charts WHERE user_id = ? ORDER BY title ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDynamicChartData($chartIdentifier, $userId) {
        // Determine if the identifier is the numeric ID or the string-based metric_id
        $lookupColumn = is_numeric($chartIdentifier) ? 'id' : 'metric_id';

        // 1. Fetch the chart's definition using the correct column
        // We also remove the user_id check here to allow default charts to load for any admin
        $sql = "SELECT * FROM charts WHERE {$lookupColumn} = ?";
        $params = [$chartIdentifier];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
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
        
        if ($agg_func === 'AVG' && $agg_col === 'dob') {
            $agg_col = 'TIMESTAMPDIFF(YEAR, dob, CURDATE())';
        }

        $group_col = $chart['group_by_column'];

        $base_query = "FROM residents WHERE approval_status = 'approved'";
        if ($group_col) {
            $base_query .= " AND {$group_col} IS NOT NULL AND {$group_col} != ''";
        }

        if ($chart['chart_type'] === 'KPI') {
            $sql = "SELECT {$agg_func}({$agg_col}) as value {$base_query}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return ['value' => round($result['value'] ?? 0, 2), 'label' => $chart['title']];
        } else {
            if ($group_col === 'dob') {
                 $stmt = $this->pdo->prepare("SELECT dob FROM residents WHERE approval_status = 'approved' AND dob IS NOT NULL AND dob != '0000-00-00'");
                 $stmt->execute();
                 $all_residents_dob = $stmt->fetchAll(PDO::FETCH_COLUMN);
                 
                 if (strpos($chart['title'], 'Generation') !== false) {
                     return $this->calculateGenerationData($all_residents_dob);
                 } else {
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
    
    private function calculateAgeBracketData($dobs) { /* ... same as before ... */ }
    private function calculateGenerationData($dobs) { /* ... same as before ... */ }
}
