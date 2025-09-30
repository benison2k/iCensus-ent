<?php
// app/controllers/SysadminController.php

class SysadminController {

    /**
     * Bouncer: Checks if the user is a logged-in System Admin.
     * Redirects to login page if not authorized.
     */
    private function requireSysadmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'System Admin') {
            header("Location: /icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/login");
            exit;
        }
    }

    /**
     * Display the System Admin dashboard.
     */
    public function dashboard() {
        $this->requireSysadmin();

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light'
        ];

        view('sysadmin/dashboard', $data);
    }
}