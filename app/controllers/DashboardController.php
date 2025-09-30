<?php
// app/controllers/DashboardController.php

class DashboardController {

    /**
     * Checks for a valid session and user role.
     * If the role doesn't match, it redirects to the login page.
     * @param string $requiredRole The role required to view the page.
     */
    private function requireAuth($requiredRole) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] != $requiredRole) {
            header("Location: /icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/login");
            exit;
        }
    }

    /**
     * Display the main dashboard for Barangay Admins.
     */
    public function index() {
        $this->requireAuth('Barangay Admin');

        // Prepare data to pass to the view
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light'
        ];
        
        // Load the view file
        view('dashboard/barangay_admin', $data);
    }

    /**
     * Display the dashboard for Encoders.
     */
    public function encoderDashboard() {
        $this->requireAuth('Encoder');
        
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light'
        ];

        view('dashboard/encoder', $data);
    }
}