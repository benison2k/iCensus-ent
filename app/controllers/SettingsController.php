<?php
// app/controllers/SettingsController.php
require_once __DIR__ . '/../models/User.php'; // We can reuse the User model

class SettingsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: /icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();

        $data = [
            'user' => $_SESSION['user'],
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
        $auth = new Auth($db); // The Auth class has the update methods
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

        header('Location: /icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public/settings');
        exit;
    }

    public function updateTheme() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $theme = $_POST['theme'] ?? 'light';
        $theme = ($theme === 'dark') ? 'dark' : 'light';
        $userId = $_SESSION['user']['id'];

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);
        $auth->updateTheme($userId, $theme); // Assumes you add an updateTheme method to Auth.php

        echo json_encode(['status' => 'success', 'theme' => $theme]);
        exit;
    }
}