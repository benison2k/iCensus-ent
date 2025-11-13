<?php
// core/Csrf.php

class Csrf {
    /**
     * Generates a token if one doesn't exist, or returns the existing one.
     */
    public static function generate() {
        // Ensure session is started before accessing $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifies if the submitted token matches the session token.
     * * @param string $token The token submitted via POST
     * @return bool True if valid, False otherwise
     */
    public static function verify($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns the hidden input field HTML to be used in forms.
     */
    public static function getField() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}