<?php
// /app/models/Resident.php

class Resident {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
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
        $stmt = $this->pdo->prepare("UPDATE residents SET approval_status = 'approved', approved_by = ? WHERE id = ?");
        return $stmt->execute([$adminId, $id]);
    }

    public function approveAll($adminId) {
        $stmt = $this->pdo->prepare(
            "UPDATE residents SET approval_status = 'approved', approved_by = ? WHERE approval_status = 'pending'"
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
}