<?php
// /app/models/Resident.php

class Resident {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getPdo();
    }

    /**
     * Fetches all residents from the database.
     * @return array
     */
    public function getAll() {
        // We add the age calculation directly in the query for efficiency
        $sql = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age FROM residents ORDER BY last_name ASC, first_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a single resident by their ID.
     * @param int $id
     * @return mixed
     */
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM residents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Saves a resident's data (creates or updates).
     * @param array $data
     * @return int The ID of the saved resident.
     */
    public function save($data) {
        if (empty($data['resident_id'])) {
            // Create new resident
            $data['date_added'] = date('Y-m-d H:i:s');
            unset($data['resident_id']); // remove the empty id
            
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
     * Deletes a resident by their ID.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM residents WHERE id = ?");
        return $stmt->execute([$id]);
    }
}