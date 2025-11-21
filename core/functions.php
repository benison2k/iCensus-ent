<?php
// core/functions.php

/**
 * Renders a view file and passes data to it.
 * @param string $name The name of the view file (e.g., 'dashboard/encoder').
 * @param array $data Data to be extracted and made available to the view.
 */
if (!function_exists('view')) {
    function view($name, $data = []) {
        $view_file = __DIR__ . '/../app/views/' . $name . '.php';
        
        if (!file_exists($view_file)) {
            error_log("View file not found: " . $view_file);
            return;
        }

        // FIX (Security): Use an immediately invoked function to create a clean, isolated scope
        // for variables extracted from $data, mitigating the security risk of extract().
        (function() use ($view_file, $data) {
            extract($data);
            require $view_file;
        })();
    }
}

/**
 * Logs an action to the system_logs database table.
 * @param string $level Log level (INFO, WARNING, ERROR).
 * @param string $action The action performed (e.g., USER_LOGIN).
 * @param string $details Additional details.
 */
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

/**
 * Safely extracts the user's first name for a greeting.
 * Falls back to 'User' or the first word of full_name if first_name is unavailable.
 * @param array $user The user session data array.
 * @return string The name to use in a greeting.
 */
function get_greeting_name($user) {
    if (isset($user['first_name']) && !empty($user['first_name'])) {
        return $user['first_name'];
    }
    if (isset($user['full_name'])) {
        $parts = explode(' ', $user['full_name']);
        // Use the first part of the full name, or 'User' if it's empty
        return !empty($parts[0]) ? $parts[0] : 'User';
    }
    return 'User';
}

/**
 * Calculates and formats a human-readable "time ago" string.
 * @param string $datetime The datetime string to calculate from (e.g., '2025-11-20 10:00:00').
 * @param bool $full Whether to show the full list of time units (e.g., '1 month, 3 days') or just the largest unit (e.g., '1 month').
 * @return string The time elapsed string (e.g., '5 minutes ago', 'yesterday').
 */
function time_elapsed_string($datetime, $full = false) {
    // FIX: Wrap date creation in try/catch to handle invalid datetime strings gracefully
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        error_log("Error creating DateTime object from string: " . $datetime . " - " . $e->getMessage());
        return 'Unknown time';
    }
    
    $diff = $now->diff($ago);

    // FIX: Calculate weeks using a local variable ($weeks) instead of attempting to write
    // to the non-existent $diff->w property, which causes a PHP Notice/Warning.
    $weeks = floor($diff->d / 7);
    $diff->d %= 7; // Get the remaining days (0-6)

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week', // The unit name for weeks
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        
        $value = 0;
        
        // Special case for the locally calculated weeks
        if ($k === 'w') {
            $value = $weeks;
        } 
        // Use the DateInterval properties for all other units
        elseif (isset($diff->$k)) {
            $value = $diff->$k;
        }

        if ($value) {
            $v = $value . ' ' . $v . ($value > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    
    if (empty($string)) {
        return 'just now';
    }
    
    // Check for future time 
    if ($ago > $now) {
        return 'in ' . implode(', ', $string);
    }

    return implode(', ', $string) . ' ago';
}