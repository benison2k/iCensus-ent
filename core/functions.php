<?php
// core/functions.php

function log_action($level, $action, $details = '') {
    global $db; // Assumes $db is an accessible global Database object

    $user_id = $_SESSION['user']['id'] ?? null;
    $username = $_SESSION['user']['username'] ?? 'SYSTEM';

    if (!$db) {
        error_log("Database connection not available for logging action: $action");
        return;
    }

    try {
        $stmt = $db->getPdo()->prepare(
            "INSERT INTO system_logs (level, user_id, username, action, details) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$level, $user_id, $username, $action, $details]);
    } catch (PDOException $e) {
        // Fallback to server's error log if the database write fails
        error_log("Failed to write to system_logs table: " . $e->getMessage());
    }
}