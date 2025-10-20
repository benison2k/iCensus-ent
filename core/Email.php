<?php
// core/Email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

class Email {
    /**
     * Sends an OTP code to a recipient's email address.
     */
    public function sendOtp($recipientEmail, $otpCode) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings (using user-provided Gmail credentials)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jbgl2263@gmail.com'; 
            $mail->Password   = 'eclf hpww hozr ghry'; // This is your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;
            
            // Recipients
            $mail->setFrom('no-reply@icensus.com', 'iCensus OTP');
            $mail->addAddress($recipientEmail);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'iCensus One-Time Password (OTP)';
            $mail->Body    = "
                <h2>Your One-Time Password</h2>
                <p>Use the following code to complete your login:</p>
                <h1 style='background-color: #eee; padding: 15px; border-radius: 5px; display: inline-block;'>{$otpCode}</h1>
                <p>This code will expire in 5 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";
            $mail->AltBody = "Your One-Time Password is: {$otpCode}. This code will expire in 5 minutes.";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            if (function_exists('log_action') && isset($GLOBALS['db'])) {
                $userId = $_SESSION['2fa_user_id'] ?? 'Unknown';
                log_action('ERROR', 'OTP_SEND_FAIL', "Mailer Error for user ID #{$userId}: {$mail->ErrorInfo}.");
            }
            return false;
        }
    }
}