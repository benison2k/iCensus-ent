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
        
        // --- THIS IS THE FIX ---
        // Make the database connection globally available for the log_action function.
        $GLOBALS['db'] = $db;
        
        $auth = new Auth($db);

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Temporarily store last logout time if it exists
        $last_logout = $_SESSION['last_logout'] ?? null;

        $result = $auth->login($username, $password);

        if ($result['success']) {
            // If login is successful, store the last logout time in the new session
            if ($last_logout) {
                $_SESSION['user']['last_log_view'] = $last_logout;
            }

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
        $config = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../../core/Database.php';
        require_once __DIR__ . '/../../core/functions.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;

        if (isset($_SESSION['user'])) {
            log_action('INFO', 'USER_LOGOUT', "User '" . $_SESSION['user']['username'] . "' logged out.");
        }
        
        // Store the logout timestamp to track new logs later
        $last_logout_time = date('Y-m-d H:i:s');
        
        session_unset();
        session_destroy();

        // Start a new, clean session just to hold the last_logout time
        session_start();
        $_SESSION['last_logout'] = $last_logout_time;


        header("Location: /iCensus-ent/public/login");
        exit;
    }
}