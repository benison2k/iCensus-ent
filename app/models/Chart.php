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
            case 'is_pwd':
            case 'is_solo_parent':
            case 'is_4ps_member':
            case 'is_registered_voter':
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
        if (empty($def['filter_conditions'])) {
            return ["", []];
        }
        $filters = json_decode($def['filter_conditions'], true);
        $where = "";
        $params = [];
        $allowedColumns = ['purok', 'gender', 'civil_status', 'is_pwd', 'is_4ps_member'];
        $allowedOperators = ['=', '!='];
        foreach ($filters as $filter) {
            if (in_array($filter['column'], $allowedColumns) && in_array($filter['operator'], $allowedOperators)) {
                $where .= " AND {$filter['column']} {$filter['operator']} ?";
                $params[] = $filter['value'];
            }
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
        $sql = "SELECT id, title, chart_type FROM charts WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}