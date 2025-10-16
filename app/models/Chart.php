<?php
// app/models/Chart.php

class Chart
{
    private $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function save($data)
    {
        $allowedColumns = [
            'user_id', 'title', 'chart_type', 'aggregate_function',
            'aggregate_column', 'group_by_column', 'filter_conditions'
        ];
        $filteredData = array_intersect_key($data, array_flip($allowedColumns));
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = implode(', ', array_fill(0, count($filteredData), '?'));
        try {
            $sql = "INSERT INTO charts ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($filteredData));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Chart Save Error: ' . $e->getMessage());
            return false;
        }
    }

    public function update($chartId, $data) {
        $allowedColumns = [
            'title', 'chart_type', 'aggregate_function',
            'aggregate_column', 'group_by_column', 'filter_conditions'
        ];
        $filteredData = array_intersect_key($data, array_flip($allowedColumns));

        $setClauses = [];
        foreach ($filteredData as $key => $value) {
            $setClauses[] = "{$key} = ?";
        }
        $setClause = implode(', ', $setClauses);

        try {
            $sql = "UPDATE charts SET {$setClause} WHERE id = ? AND user_id = ?";
            $params = array_values($filteredData);
            $params[] = $chartId;
            $params[] = $_SESSION['user']['id']; 

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Chart Update Error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($chartId) {
        try {
            $sql = "DELETE FROM charts WHERE id = ? AND user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$chartId, $_SESSION['user']['id']]);
        } catch (PDOException $e) {
            error_log('Chart Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    public function find($chartId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM charts WHERE id = ?");
        $stmt->execute([$chartId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDataForChart($chartDef)
    {
        list($selectClause, $groupByClause) = $this->buildSelectAndGroupClause($chartDef);
        list($whereClause, $params) = $this->buildWhereClause($chartDef);
        
        $sql = "SELECT {$selectClause}
                FROM residents
                WHERE approval_status = 'approved'
                {$whereClause}
                {$groupByClause}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatDataForFrontend($results, $chartDef);
    }

    private function buildSelectAndGroupClause($def)
    {
        $aggFunc = $def['aggregate_function'];
        $aggCol = $def['aggregate_column'];
        $groupByCol = $def['group_by_column'];
        $aggSelect = ($aggFunc === 'AVG' && $aggCol === 'dob')
            ? "ROUND(AVG(TIMESTAMPDIFF(YEAR, dob, CURDATE())), 1) as value"
            : "{$aggFunc}({$aggCol}) as value";
        if (!$groupByCol) {
            return [$aggSelect, ""];
        }
        switch ($groupByCol) {
            case 'dob':
                $categorySelect = "CASE
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= 17 THEN '0-17 (Minors)'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= 30 THEN '18-30 (Youth)'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= 59 THEN '31-59 (Adults)'
                    ELSE '60+ (Seniors)'
                END as category";
                break;
            case 'employment_status':
                $categorySelect = "CASE 
                    WHEN occupation IS NULL OR occupation = '' OR LOWER(occupation) IN ('unemployed', 'n/a', 'student') 
                    THEN 'Unemployed' 
                    ELSE 'Employed' 
                END as category";
                break;
            case 'is_pwd':
            case 'is_solo_parent':
            case 'is_4ps_member':
            case 'is_registered_voter':
            case 'is_indigent':
                $categorySelect = "CASE WHEN {$groupByCol} = 1 THEN 'Yes' ELSE 'No' END as category";
                break;
            default:
                $categorySelect = "COALESCE(NULLIF({$groupByCol}, ''), 'Unspecified') as category";
                break;
        }
        return ["{$aggSelect}, {$categorySelect}", "GROUP BY category ORDER BY category ASC"];
    }

    private function buildWhereClause($def)
    {
        $where = "";
        $params = [];
        $allowedColumns = [
            'purok', 'gender', 'civil_status', 'educational_attainment', 'occupation',
            'ownership_status', 'blood_type', 'nationality', 'relationship', 'residency_status', 'status',
            'employment_status', 'is_student', 'is_pwd', 'is_4ps_member', 'is_registered_voter', 'is_solo_parent', 'is_indigent'
        ];
        $allowedOperators = ['=', '!='];

        if (!empty($def['filter_conditions'])) {
            $filters = json_decode($def['filter_conditions'], true);
            foreach ($filters as $filter) {
                if (isset($filter['column'], $filter['operator']) && in_array($filter['column'], $allowedColumns) && in_array($filter['operator'], $allowedOperators)) {
                    if ($filter['column'] === 'employment_status') {
                        if ($filter['value'] === 'employed') {
                            $where .= " AND (occupation IS NOT NULL AND occupation != '' AND LOWER(occupation) NOT IN ('unemployed', 'n/a', 'student'))";
                        } else {
                            $where .= " AND (occupation IS NULL OR occupation = '' OR LOWER(occupation) IN ('unemployed', 'n/a', 'student'))";
                        }
                    } elseif ($filter['column'] === 'is_student') {
                        if ($filter['value'] === '1') {
                            $where .= " AND LOWER(occupation) = 'student'";
                        } else {
                            $where .= " AND (LOWER(occupation) != 'student' OR occupation IS NULL)";
                        }
                    } else {
                        $where .= " AND {$filter['column']} {$filter['operator']} ?";
                        $params[] = $filter['value'];
                    }
                }
            }
        }

        if (!empty($def['start_date']) && !empty($def['end_date'])) {
            $where .= " AND DATE(date_approved) BETWEEN ? AND ?";
            $params[] = $def['start_date'];
            $params[] = $def['end_date'];
        }

        return [$where, $params];
    }

    private function formatDataForFrontend($results, $def)
    {
        if ($def['chart_type'] === 'KPI') {
            return ['value' => $results[0]['value'] ?? 0];
        }
        $formattedData = [];
        foreach ($results as $row) {
            $formattedData[$row['category']] = (int) $row['value'];
        }
        return $formattedData;
    }

    public function findAllByUserId($userId)
    {
        $sql = "SELECT id, title, chart_type, group_by_column FROM charts WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}