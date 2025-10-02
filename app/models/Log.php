<?php
// /app/models/Log.php

class Log {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * Gets the count of all unseen logs.
     * @return int
     */
    public function getUnseenLogCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM system_logs WHERE is_seen = 0");
        return $stmt->fetchColumn();
    }

    /**
     * Marks a single log as seen.
     * @param int $logId
     * @return bool
     */
    public function markAsSeen($logId) {
        $stmt = $this->pdo->prepare("UPDATE system_logs SET is_seen = 1 WHERE id = ?");
        return $stmt->execute([$logId]);
    }

    /**
     * Marks all logs as seen.
     * @return bool
     */
    public function markAllAsSeen() {
        $stmt = $this->pdo->query("UPDATE system_logs SET is_seen = 1 WHERE is_seen = 0");
        return $stmt->rowCount(); // Returns the number of affected rows
    }

    public function getLogs($filters = []) {
        // ... existing getLogs code remains the same ...
        $params = [];
        $conditions = [];

        if (!empty($filters['actions'])) {
            $placeholders = implode(',', array_fill(0, count($filters['actions']), '?'));
            $conditions[] = "s.action IN ($placeholders)";
            $params = array_merge($params, $filters['actions']);
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "s.timestamp >= ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['end_date'])) {
            $conditions[] = "s.timestamp <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "s.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['level'])) {
            $conditions[] = "s.level = ?";
            $params[] = $filters['level'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "s.details LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $count_sql = "SELECT COUNT(*) FROM system_logs s " . $where_clause;
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $totalLogs = $count_stmt->fetchColumn();

        $page = $filters['page'] ?? 1;
        $pageSize = in_array($filters['pageSize'], [10, 25, 50, 100]) ? $filters['pageSize'] : 10;
        $offset = ($page - 1) * $pageSize;

        $sort_by_whitelist = ['timestamp'];
        $sort_by = in_array($filters['sort_by'], $sort_by_whitelist) ? $filters['sort_by'] : 'timestamp';
        $sort_order = strtoupper($filters['sort_order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "
            SELECT 
                s.*, 
                r.role_name 
            FROM system_logs s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            {$where_clause}
            ORDER BY s.{$sort_by} {$sort_order} 
            LIMIT {$pageSize} OFFSET {$offset}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'logs' => $logs,
            'total' => $totalLogs,
            'totalPages' => ceil($totalLogs / $pageSize)
        ];
    }
}