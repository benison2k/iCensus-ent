<?php
// /app/models/Resident.php

class Resident {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * NEW: Generic function to get distinct, non-empty values from a column.
     * @param string $column The name of the column.
     * @return array
     */
    public function getDistinctValues($column) {
        // The query is built to be safe as the column name is controlled internally.
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT {$column} 
            FROM residents 
            WHERE {$column} IS NOT NULL AND {$column} != '' 
            ORDER BY {$column} ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getHouseholdHeads() {
        $stmt = $this->pdo->query("
            SELECT DISTINCT head_of_household 
            FROM residents 
            WHERE head_of_household IS NOT NULL AND head_of_household != ''
            ORDER BY head_of_household ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function findByAddress($house_no, $street, $purok) {
        $stmt = $this->pdo->prepare("
            SELECT id, first_name, last_name, relationship 
            FROM residents 
            WHERE house_no = ? AND street = ? AND purok = ?
        ");
        $stmt->execute([$house_no, $street, $purok]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchHeads($term) {
        $stmt = $this->pdo->prepare("
            SELECT CONCAT(first_name, ' ', last_name) as name 
            FROM residents 
            WHERE (relationship = 'Self' OR relationship = '')
            AND CONCAT(first_name, ' ', last_name) LIKE ?
            LIMIT 10
        ");
        $stmt->execute(['%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Fetches all APPROVED residents from the database for the main view.
     * @return array
     */
    public function getAll() {
        // MODIFIED: This now only fetches residents that have been approved.
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age 
                FROM residents 
                WHERE approval_status = 'approved' 
                ORDER BY last_name ASC, first_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NEW: Fetches all residents awaiting approval.
     * @return array
     */
    public function getPending() {
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age 
                FROM residents 
                WHERE approval_status = 'pending' 
                ORDER BY created_at ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPendingPaginated($page = 1, $pageSize = 10) {
        $offset = ($page - 1) * $pageSize;

        // Get total count
        $countStmt = $this->pdo->query("SELECT COUNT(*) FROM residents WHERE approval_status = 'pending'");
        $total = $countStmt->fetchColumn();

        // Get paginated results
        $stmt = $this->pdo->prepare("
            SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age 
            FROM residents 
            WHERE approval_status = 'pending' 
            ORDER BY created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int) $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'residents' => $residents,
            'total' => $total,
            'totalPages' => ceil($total / $pageSize)
        ];
    }

    /**
     * NEW: Gets the count of pending residents for notification badges.
     * @return int
     */
    public function getPendingCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM residents WHERE approval_status = 'pending'");
        return $stmt->fetchColumn();
    }

    /**
     * Finds a single resident by their ID. (No changes needed here)
     * @param int $id
     * @return mixed
     */
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * FIX: New method to find a resident regardless of approval status.
     * This is required for the modal to view/edit pending entries without error.
     * @param int $id
     * @return mixed
     */
    public function findAnyStatus($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Saves a resident's data (creates or updates). (No changes needed here)
     * @param array $data
     * @return int The ID of the saved resident.
     */
    public function save($data) {
        if (empty($data['resident_id'])) {
            // Create new resident
            $data['date_added'] = date('Y-m-d H:i:s');
            unset($data['resident_id']);
            
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $stmt = $this->pdo->prepare("INSERT INTO residents (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")");
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } else {
            // Update existing resident
            $id = $data['resident_id'];
            unset($data['resident_id']);
            $data['last_updated'] = date('Y-m-d H:i:s');
            
            $setStr = implode(',', array_map(fn($f) => "$f=?", array_keys($data)));
            
            $stmt = $this->pdo->prepare("UPDATE residents SET $setStr WHERE id = ?");
            $values = array_values($data);
            $values[] = $id;
            $stmt->execute($values);
            return $id;
        }
    }

    /**
     * NEW: Approves a resident.
     * @param int $id The ID of the resident to approve.
     * @param int $adminId The ID of the admin approving.
     * @return bool
     */
    public function approve($id, $adminId) {
        // --- THIS IS THE FIX: Added date_approved = NOW() ---
        $stmt = $this->pdo->prepare("UPDATE residents SET approval_status = 'approved', approved_by = ?, date_approved = NOW() WHERE id = ?");
        return $stmt->execute([$adminId, $id]);
    }

    public function approveAll($adminId) {
        // --- THIS IS THE FIX: Added date_approved = NOW() ---
        $stmt = $this->pdo->prepare(
            "UPDATE residents SET approval_status = 'approved', approved_by = ?, date_approved = NOW() WHERE approval_status = 'pending'"
        );
        $stmt->execute([$adminId]);
        return $stmt->rowCount();
    }

    /**
     * NEW: Rejects (deletes) a pending resident entry.
     * @param int $id The ID of the resident to reject.
     * @return bool
     */
    public function reject($id) {
        // This permanently deletes the pending record.
        // If you'd rather set approval_status to 'rejected', change DELETE to UPDATE.
        $stmt = $this->pdo->prepare("DELETE FROM residents WHERE id = ? AND approval_status = 'pending'");
        return $stmt->execute([$id]);
    }

    /**
     * Deletes a resident by their ID. (No changes needed here)
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM residents WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * NEW: Generic function to get filtered residents based on URL params
     * @param array $filters The filter parameters from the request.
     * @return array
     */
    public function getFiltered($filters) {
        $query = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age FROM residents WHERE approval_status = 'approved'";
        $params = [];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND DATE(date_approved) BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['gender'])) {
            $query .= " AND gender = ?";
            $params[] = $filters['gender'];
        }
        if (!empty($filters['civil_status'])) {
            $query .= " AND civil_status = ?";
            $params[] = $filters['civil_status'];
        }
        if (!empty($filters['purok'])) {
            $query .= " AND purok = ?";
            $params[] = $filters['purok'];
        }
        if (!empty($filters['blood_type'])) {
            $query .= " AND blood_type = ?";
            $params[] = $filters['blood_type'];
        }
        if (!empty($filters['educational_attainment'])) {
            $query .= " AND educational_attainment = ?";
            $params[] = $filters['educational_attainment'];
        }
        if (!empty($filters['occupation'])) {
            $query .= " AND occupation = ?";
            $params[] = $filters['occupation'];
        }
        if (isset($filters['employment_status']) && $filters['employment_status'] !== '') {
            if ($filters['employment_status'] === 'employed') {
                $query .= " AND (occupation IS NOT NULL AND occupation != '' AND LOWER(occupation) NOT IN ('unemployed', 'n/a', 'student'))";
            } else {
                $query .= " AND (occupation IS NULL OR occupation = '' OR LOWER(occupation) IN ('unemployed', 'n/a', 'student'))";
            }
        }
        if (!empty($filters['is_pwd'])) {
            $query .= " AND is_pwd = ?";
            $params[] = $filters['is_pwd'];
        }
        if (!empty($filters['is_solo_parent'])) {
            $query .= " AND is_solo_parent = ?";
            $params[] = $filters['is_solo_parent'];
        }
        if (!empty($filters['is_4ps_member'])) {
            $query .= " AND is_4ps_member = ?";
            $params[] = $filters['is_4ps_member'];
        }
        if (!empty($filters['age_min'])) {
            $query .= " AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?";
            $params[] = $filters['age_min'];
        }
        if (!empty($filters['age_max'])) {
            $query .= " AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?";
            $params[] = $filters['age_max'];
        }
        if (!empty($filters['relationship'])) {
            $query .= " AND relationship = ?";
            $params[] = $filters['relationship'];
        }

        // --- ADDED FILTER LOGIC ---
        if (!empty($filters['generation'])) {
            $generation = $filters['generation'];
            $yearCondition = '';
            switch ($generation) {
                case 'Gen Alpha': $yearCondition = "YEAR(dob) >= 2013"; break;
                case 'Gen Z': $yearCondition = "YEAR(dob) BETWEEN 1997 AND 2012"; break;
                case 'Millennials': $yearCondition = "YEAR(dob) BETWEEN 1981 AND 1996"; break;
                case 'Gen X': $yearCondition = "YEAR(dob) BETWEEN 1965 AND 1980"; break;
                case 'Baby Boomers': $yearCondition = "YEAR(dob) BETWEEN 1946 AND 1964"; break;
                case 'Older': $yearCondition = "YEAR(dob) < 1946"; break;
                case 'Unknown': $yearCondition = "(dob IS NULL OR dob = '0000-00-00')"; break;
            }
            if ($yearCondition) {
                $query .= " AND ($yearCondition)";
            }
        }
        
        if (!empty($filters['is_head']) && $filters['is_head'] === 'Yes') {
            $query .= " AND relationship = 'Self'";
        }
        
        if (!empty($filters['street'])) {
            $query .= " AND street = ?";
            $params[] = $filters['street'];
        }

        if (!empty($filters['has_field'])) {
            $allowed_fields = ['contact_number', 'email', 'emergency_name', 'blood_type'];
            $field = $filters['has_field'];
            if (in_array($field, $allowed_fields)) {
                $query .= " AND ({$field} IS NOT NULL AND {$field} != '')";
            }
        }

        if (!empty($filters['household_size'])) {
            $size = intval($filters['household_size']);
            $operator = str_contains($filters['household_size'], '+') ? '>=' : '=';
            $query .= " AND household_no IN (SELECT household_no FROM residents WHERE household_no IS NOT NULL AND household_no != '' GROUP BY household_no HAVING COUNT(*) {$operator} ?)";
            $params[] = $size;
        } 
        $query .= " ORDER BY last_name, first_name";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NEW: Gets submission statistics for a specific encoder.
     * @param int $encoderId The ID of the encoder.
     * @return array
     */
    public function getStatsForEncoder($encoderId) {
        $stats = [];

        // Entries Submitted Today
        $stmt_today = $this->pdo->prepare("SELECT COUNT(*) FROM residents WHERE encoded_by = ? AND DATE(created_at) = CURDATE()");
        $stmt_today->execute([$encoderId]);
        $stats['today'] = $stmt_today->fetchColumn();

        // Entries Pending Approval
        $stmt_pending = $this->pdo->prepare("SELECT COUNT(*) FROM residents WHERE encoded_by = ? AND approval_status = 'pending'");
        $stmt_pending->execute([$encoderId]);
        $stats['pending'] = $stmt_pending->fetchColumn();

        // Total Entries Approved
        $stmt_approved = $this->pdo->prepare("SELECT COUNT(*) FROM residents WHERE encoded_by = ? AND approval_status = 'approved'");
        $stmt_approved->execute([$encoderId]);
        $stats['approved'] = $stmt_approved->fetchColumn();

        return $stats;
    }
    
    /**
     * NEW: Gets the most recent submission activity for a specific encoder.
     * @param int $encoderId The ID of the encoder.
     * @param int $limit The maximum number of entries to return.
     * @return array
     */
    public function getRecentByEncoder($encoderId, $limit = 5) {
        $sql = "SELECT first_name, last_name, created_at, approval_status 
                FROM residents 
                WHERE encoded_by = :encoder_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':encoder_id', $encoderId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}