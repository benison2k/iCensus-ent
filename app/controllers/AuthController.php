<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../core/Auth.php';

class AuthController {

    /**
     * Shows the login form View.
     */
    public function showLoginForm() {
        // Data to pass to the view (e.g., for error messages)
        $data = [
            'error' => '',
            'usernameValue' => ''
        ];
        view('auth/login', $data);
    }

    /**
     * Processes the login form submission.
     */
    public function login() {
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $auth = new Auth($db);

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $result = $auth->login($username, $password);

        if ($result['success']) {
            $role = $_SESSION['user']['role_name'];
            $base_url = '/iCensus-ent/public';

            // Redirect based on role
            if ($role == 'System Admin') $redirect_to = $base_url . '/sysadmin/dashboard';
            elseif ($role == 'Barangay Admin') $redirect_to = $base_url . '/dashboard';
            elseif ($role == 'Encoder') $redirect_to = $base_url . '/encoder-dashboard';
            else $redirect_to = $base_url . '/login'; // Fallback

            header("Location: " . $redirect_to);
            exit;
        } else {
            // If login fails, show the form again with an error
            $data = [
                'error' => 'Invalid credentials',
                'usernameValue' => htmlspecialchars($username)
            ];
            view('auth/login', $data);
        }
    }

    /**
     * Handles user logout.
     */
    public function logout() {
        // This logic is from your old /pages/logout.php
        session_start();
        $config = require __DIR__ . '/../../core/config.php';
        require_once __DIR__ . '/../../core/Database.php';
        require_once __DIR__ . '/../../core/functions.php';
        $db = new Database($config);

        if (isset($_SESSION['user'])) {
            log_action('INFO', 'USER_LOGOUT', "User '" . $_SESSION['user']['username'] . "' logged out.");
        }
        session_unset();
        session_destroy();
        header("Location: /iCensus-ent/public/login");
        exit;
    }
}