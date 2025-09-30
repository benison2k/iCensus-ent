<?php
// app/controllers/DashboardController.php
require_once __DIR__ . '/../../core/Auth.php';

class DashboardController {

    /**
     * Checks for a valid session, user role, and refreshes session data.
     * @param string $requiredRole The role required to view the page.
     */
    private function requireAuthAndRefresh($requiredRole) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== $requiredRole) {
            header("Location: /iCensus-ent/public/login");
            exit;
        }
        // --- REFRESH LOGIC ADDED HERE ---
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
    }

    /**
     * Display the main dashboard for Barangay Admins.
     */
    public function index() {
        $this->requireAuthAndRefresh('Barangay Admin');
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light'
        ];
        view('dashboard/barangay_admin', $data);
    }

    /**
     * Display the dashboard for Encoders.
     */
    public function encoderDashboard() {
        $this->requireAuthAndRefresh('Encoder');
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light'
        ];
        view('dashboard/encoder', $data);
    }
}