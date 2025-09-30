<?php
// app/controllers/SettingsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/functions.php';

class SettingsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: /iCensus-ent/public/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();

        // --- REFRESH LOGIC ADDED HERE ---
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
        // --- END REFRESH LOGIC ---

        $data = [
            'user' => $_SESSION['user'], // This is now fresh data
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);

        view('settings/index', $data);
    }
    
    public function process() {
        $this->checkAuth();
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db; // Make db global for log_action
        $auth = new Auth($db);
        $userId = $_SESSION['user']['id'];
        
        try {
            if (isset($_POST['update_username'])) {
                $auth->updateUsername($userId, $_POST['username']);
                log_action('INFO', 'SETTINGS_UPDATE', "User updated their username.");
                $_SESSION['modal'] = ['message' => 'Username updated successfully', 'type' => 'success'];
            }
            if (isset($_POST['update_password'])) {
                $auth->updatePassword($userId, $_POST['password']);
                log_action('INFO', 'SETTINGS_UPDATE', "User changed their password.");
                $_SESSION['modal'] = ['message' => 'Password updated successfully', 'type' => 'success'];
            }
        } catch (Exception $e) {
            log_action('ERROR', 'SETTINGS_ERROR', $e->getMessage());
            $_SESSION['modal'] = ['message' => 'An error occurred.', 'type' => 'error'];
        }

        header('Location: /iCensus-ent/public/settings');
        exit;
    }

    public function updateTheme() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $theme = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $userId = $_SESSION['user']['id'];
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->updateTheme($userId, $theme);
        echo json_encode(['status' => 'success', 'theme' => $theme]);
        exit;
    }

    public function verifyPassword() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $currentPassword = $_POST['current_password'] ?? '';
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        if ($auth->verifyPassword($_SESSION['user']['id'], $currentPassword)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password']);
        }
        exit;
    }
}