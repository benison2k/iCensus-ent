<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../../core/Auth.php';
// FIX: Ensure this path correctly points to the file containing get_greeting_name() and time_elapsed_string()
require_once __DIR__ . '/../../core/functions.php'; 

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

        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $residentModel = new Resident($db);
        $pending_count = $residentModel->getPendingCount();

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'pending_count' => $pending_count
        ];
        view('dashboard/barangay_admin', $data);
    }

    /**
     * Display the dashboard for Encoders.
     */
    public function encoderDashboard() {
        $this->requireAuthAndRefresh('Encoder');
        
        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $residentModel = new Resident($db);
        
        $encoderId = $_SESSION['user']['id'];
        
        // Fetch stats
        $encoderStats = $residentModel->getStatsForEncoder($encoderId);
        
        // Fetch recent activity
        $recentActivity = $residentModel->getRecentByEncoder($encoderId);
    
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'stats' => $encoderStats, 
            'recent_activity' => $recentActivity, 
            // The fatal error happens here. The fix relies on the require_once above.
            'greeting_name' => get_greeting_name($_SESSION['user']) 
        ];
        view('dashboard/encoder', $data);
    }

    /**
     * Display the page for reviewing pending resident entries.
     */    
    public function review() {
        $this->requireAuthAndRefresh('Barangay Admin');
        
        // We need the Resident model here
        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $residentModel = new Resident($db);
        
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'pending_residents' => $residentModel->getPending(),
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);
        
        view('dashboard/review', $data);
    }

    /**
     * Process the approval of a resident.
     */
    public function approveResident() {
        $this->requireAuthAndRefresh('Barangay Admin');
        
        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $GLOBALS['db'] = $db; // For logging
        $residentModel = new Resident($db);

        $residentId = $_GET['id'] ?? null;
        if ($residentId) {
            $residentModel->approve($residentId, $_SESSION['user']['id']);
            log_action('INFO', 'RESIDENT_APPROVED', "Admin approved resident entry ID#{$residentId}.");
            $_SESSION['modal'] = ['message' => 'Resident approved successfully.', 'type' => 'success'];
        }
        
        header("Location: /iCensus-ent/public/review");
        exit;
    }

    /**
     * Process the rejection of a resident.
     */
    public function rejectResident() {
        $this->requireAuthAndRefresh('Barangay Admin');

        require_once __DIR__ . '/../models/Residents.php';
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $GLOBALS['db'] = $db; // For logging
        $residentModel = new Resident($db);
        
        $residentId = $_GET['id'] ?? null;
        if ($residentId) {
            $residentModel->reject($residentId);
            log_action('INFO', 'RESIDENT_REJECTED', "Admin rejected pending resident entry ID#{$residentId}.");
            $_SESSION['modal'] = ['message' => 'Resident entry rejected.', 'type' => 'success'];
        }

        header("Location: /iCensus-ent/public/review");
        exit;
    }
}