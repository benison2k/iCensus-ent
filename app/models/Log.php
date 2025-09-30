<?php
// /app/models/Log.php

class Log {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    public function getLogs($filters = []) {
        $where_clause = "";
        $params = [];
        $conditions = [];

        if (!empty($filters['actions'])) {
            $placeholders = implode(',', array_fill(0, count($filters['actions']), '?'));
            $conditions[] = "action IN ($placeholders)";
            $params = array_merge($params, $filters['actions']);
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "timestamp >= ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['end_date'])) {
            $conditions[] = "timestamp <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $conditions);
        }

        // Get total count
        $count_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM system_logs $where_clause");
        $count_stmt->execute($params);
        $totalLogs = $count_stmt->fetchColumn();

        // Get paginated results
        $page = $filters['page'] ?? 1;
        $pageSize = 25;
        $offset = ($page - 1) * $pageSize;

        $stmt = $this->pdo->prepare("SELECT * FROM system_logs $where_clause ORDER BY timestamp DESC LIMIT $pageSize OFFSET $offset");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'logs' => $logs,
            'total' => $totalLogs,
            'totalPages' => ceil($totalLogs / $pageSize)
        ];
    }
}