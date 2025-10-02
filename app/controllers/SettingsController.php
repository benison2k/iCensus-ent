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
        header('Content-Type: application/json'); // Set header for JSON response
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db; // Make db global for log_action
        $auth = new Auth($db);
        $userId = $_SESSION['user']['id'];
        $oldUsername = $_SESSION['user']['username'];
        
        try {
            $message = '';
            if (isset($_POST['update_username'])) {
                $newUsername = $_POST['username'];
                if ($oldUsername !== $newUsername) {
                    $auth->updateUsername($userId, $newUsername);
                    log_action('INFO', 'SETTINGS_UPDATE', "User updated their username from '{$oldUsername}' to '{$newUsername}'.");
                    $message = 'Username updated successfully';
                } else {
                    $message = 'Username is the same, no changes made.';
                }
            }
            if (isset($_POST['update_password'])) {
                $auth->updatePassword($userId, $_POST['password']);
                log_action('INFO', 'SETTINGS_UPDATE', "User changed their password.");
                $message = 'Password updated successfully';
            }
            echo json_encode(['status' => 'success', 'message' => $message]);

        } catch (Exception $e) {
            log_action('ERROR', 'SETTINGS_ERROR', $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }

        exit; // Important to stop execution after sending JSON
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