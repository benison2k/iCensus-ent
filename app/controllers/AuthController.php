<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../core/Auth.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

class AuthController {

    public function showLoginForm() {
        $data = [
            'error' => '',
            'usernameValue' => ''
        ];
        view('auth/login', $data);
    }

    public function login() {
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $auth = new Auth($db);

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $db->getPdo()->prepare(
            "SELECT users.*, roles.role_name
             FROM users
             JOIN roles ON users.role_id = roles.id
             WHERE users.username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role_name'] === 'System Admin') {
                if (empty($user['email'])) {
                    log_action('ERROR', 'OTP_NO_EMAIL', "Attempted OTP login for user '{$user['username']}' but no email is set.");
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'System Admin account does not have an email address configured.']);
                    exit;
                }

                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['temp_user_id'] = $user['id'];

                $mailConfig = require __DIR__ . '/../../config/mail.php';
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = $mailConfig['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $mailConfig['username'];
                    $mail->Password   = $mailConfig['password'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $mailConfig['port'];

                    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                    $mail->addAddress($user['email'], $user['full_name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your iCensus OTP Code';
                    $mail->Body    = "Your one-time password for iCensus is: <b>{$otp}</b>";
                    $mail->AltBody = "Your one-time password for iCensus is: {$otp}";

                    $mail->send();
                    log_action('INFO', 'OTP_SENT', "OTP successfully sent to user ID #{$user['id']}.");

                } catch (Exception $e) {
                    log_action('ERROR', 'OTP_SEND_FAIL', "Mailer Error for user ID #{$user['id']}: {$mail->ErrorInfo}");
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Could not send OTP email. Check system logs.']);
                    exit;
                }

                header('Content-Type: application/json');
                echo json_encode(['status' => 'otp_required']);
                exit;
            }

            $auth->login($username, $password);
            $role = $_SESSION['user']['role_name'];
            $base_url = '/iCensus-ent/public';

            $redirect_to = $base_url . '/login';
            if ($role == 'Barangay Admin') $redirect_to = $base_url . '/dashboard';
            elseif ($role == 'Encoder') $redirect_to = $base_url . '/encoder-dashboard';

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'redirect' => $redirect_to]);
            exit;

        } else {
            log_action('WARNING', 'USER_LOGIN_FAIL', "Failed login attempt for username: '" . htmlspecialchars($username) . "'");
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            exit;
        }
    }

    public function verifyOtpAndLogin() {
        session_start();
        header('Content-Type: application/json');

        $submittedOtp = $_POST['otp'] ?? '';
        $storedOtp = $_SESSION['otp'] ?? '';

        if (!empty($submittedOtp) && !empty($storedOtp) && $submittedOtp == $storedOtp) {
            $config = require __DIR__ . '/../../config/database.php';
            $db = new Database($config);
            $GLOBALS['db'] = $db;

            $stmt = $db->getPdo()->prepare(
                "SELECT users.*, roles.role_name
                 FROM users JOIN roles ON users.role_id = roles.id
                 WHERE users.id = ?"
            );
            $stmt->execute([$_SESSION['temp_user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // --- THIS IS THE FIX ---
            // The 'language' and 'two_fa' keys have been removed to match your database.
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name'],
                'full_name' => $user['full_name'],
                'theme' => $user['theme'] ?? 'light'
            ];
            // --- END OF FIX ---

            $_SESSION['LAST_ACTIVITY'] = time();
            log_action('INFO', 'USER_LOGIN_SUCCESS', "User '{$user['username']}' logged in successfully via OTP.");

            unset($_SESSION['otp']);
            unset($_SESSION['temp_user_id']);

            echo json_encode(['status' => 'success', 'redirect' => '/iCensus-ent/public/sysadmin/dashboard']);
        } else {
            log_action('WARNING', 'OTP_VERIFY_FAIL', "Failed OTP attempt for user ID #{$_SESSION['temp_user_id']}.");
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
        }
        exit;
    }

    public function logout() {
        $config = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../../core/Database.php';
        require_once __DIR__ . '/../../core/functions.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;

        if (isset($_SESSION['user'])) {
            log_action('INFO', 'USER_LOGOUT', "User '" . $_SESSION['user']['username'] . "' logged out.");
        }

        $last_logout_time = date('Y-m-d H:i:s');

        session_unset();
        session_destroy();

        session_start();
        $_SESSION['last_logout'] = $last_logout_time;

        header("Location: /iCensus-ent/public/login");
        exit;
    }
}