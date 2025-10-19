<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/User.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {

    /**
     * Shows the login form View.
     */
    public function showLoginForm() {
        $data = ['error' => '', 'usernameValue' => ''];
        view('auth/login', $data);
    }

    /**
     * Processes the login form submission.
     */
    public function login() {
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $userModel = new User($db);

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role_name'] === 'System Admin') {
                // Generate and send OTP
                $otp = random_int(100000, 999999);
                $expiryTime = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

                $userModel->setOtp($user['id'], $otp, $expiryTime);
                $this->sendOtpEmail($user['email'], $otp);

                $_SESSION['otp_user_id'] = $user['id'];

                // RENDER THE LOGIN VIEW AGAIN, BUT WITH THE MODAL TRIGGERED
                view('auth/login', ['showOtpModal' => true]);
                exit;

            } else {
                // Regular user login
                $auth = new Auth($db);
                $result = $auth->login($username, $password);

                if ($result['success']) {
                    $role = $_SESSION['user']['role_name'];
                    $base_url = '/iCensus-ent/public';
                    if ($role == 'Barangay Admin') $redirect_to = $base_url . '/dashboard';
                    elseif ($role == 'Encoder') $redirect_to = $base_url . '/encoder-dashboard';
                    else $redirect_to = $base_url . '/login';
                    header("Location: " . $redirect_to);
                    exit;
                }
            }
        }

        log_action('WARNING', 'USER_LOGIN_FAIL', "Failed login attempt for username: '" . htmlspecialchars($username) . "'");
        $data = ['error' => 'Invalid credentials', 'usernameValue' => htmlspecialchars($username)];
        view('auth/login', $data);
    }

    public function verifyOtp() {
        if (empty($_POST['otp']) || empty($_SESSION['otp_user_id'])) {
            header('Location: /iCensus-ent/public/login');
            exit;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $userModel = new User($db);
        $userId = $_SESSION['otp_user_id'];
        $otp = $_POST['otp'];

        if ($userModel->verifyOtp($userId, $otp)) {
            // OTP is correct, log the user in
            unset($_SESSION['otp_user_id']);
            $user = $userModel->find($userId);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role_id' => $user['role_id'],
                'role_name' => 'System Admin',
                'full_name' => $user['full_name'],
                'theme' => $user['theme'] ?? 'light',
            ];
            $_SESSION['LAST_ACTIVITY'] = time();
            log_action('INFO', 'USER_LOGIN_SUCCESS', "User '" . $user['username'] . "' logged in successfully via OTP.");
            
            session_write_close(); // <-- THIS IS THE FIX
            header("Location: /iCensus-ent/public/sysadmin/dashboard");
            exit;
        } else {
            // OTP is incorrect
            view('auth/login', ['showOtpModal' => true, 'otpError' => 'Invalid or expired OTP.']);
        }
    }

    private function sendOtpEmail($recipientEmail, $otp) {
        $mailConfig = require __DIR__ . '/../../config/mail.php';
        require_once __DIR__ . '/../../vendor/autoload.php';
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
            $mail->addAddress($recipientEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Your iCensus OTP';
            $mail->Body    = "Your One-Time Password is: <b>{$otp}</b>. It will expire in 15 minutes.";
            $mail->AltBody = "Your One-Time Password is: {$otp}. It will expire in 15 minutes.";
            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
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
        session_unset();
        session_destroy();
        session_start();
        header("Location: /iCensus-ent/public/login");
        exit;
    }
}