<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/EnvLoader.php';

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    private function configureMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->getSmtpHost();
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->getSmtpUsername();
            $this->mailer->Password = $this->getSmtpPassword();
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $this->getSmtpPort();
            
            // Default settings
            $this->mailer->setFrom($this->getFromEmail(), $this->getFromName());
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Enable debug if needed (set to 0 for production)
            $this->mailer->SMTPDebug = 0;
            
        } catch (Exception $e) {
            error_log("PHPMailer configuration error: " . $e->getMessage());
            throw new Exception("Email service configuration failed");
        }
    }
    
    public function sendPasswordResetEmail($email, $otp) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            $this->mailer->Subject = "Password Reset Request - SACOETEC";
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($otp);
            $this->mailer->AltBody = $this->getPasswordResetTextTemplate($otp);
            
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Password reset email sent successfully to: $email");
                return true;
            } else {
                error_log("Failed to send password reset email to: $email");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending password reset email: " . $e->getMessage());
            return false;
        }
    }
    
    private function getPasswordResetEmailTemplate($otp) {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset Request</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h2 { color: #2c3e50; margin: 0; }
                .header h3 { color: #34495e; margin: 10px 0 0 0; }
                .content { background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 20px; }
                .otp-box { background-color: #ffffff; padding: 20px; border: 3px solid #3498db; border-radius: 8px; text-align: center; margin: 25px 0; }
                .otp-code { color: #3498db; font-size: 28px; font-weight: bold; letter-spacing: 8px; margin: 0; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
                .warning ul { margin: 10px 0; padding-left: 20px; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
                .footer p { color: #7f8c8d; font-size: 14px; margin: 0; }
                .logo { max-width: 150px; height: auto; margin-bottom: 15px; }
                @media only screen and (max-width: 600px) {
                    .container { padding: 10px; }
                    .content { padding: 20px; }
                    .otp-code { font-size: 24px; letter-spacing: 5px; }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://tascesalary.com.ng/assets/images/tasce_r_logo.png' alt='SACOETEC Logo' class='logo'>
                    <h2>SACOETEC</h2>
                    <h3>Password Reset Request</h3>
                </div>
                
                <div class='content'>
                    <p>Hello,</p>
                    
                    <p>You have requested to reset your password for your SACOETEC account. To proceed with the password reset, please use the verification code below:</p>
                    
                    <div class='otp-box'>
                        <h2 class='otp-code'>$otp</h2>
                    </div>
                    
                    <div class='warning'>
                        <p><strong>Important Security Information:</strong></p>
                        <ul>
                            <li>This verification code will expire in <strong>15 minutes</strong></li>
                            <li>If you didn't request this password reset, please ignore this email</li>
                            <li>For security reasons, never share this code with anyone</li>
                            <li>This code can only be used once</li>
                        </ul>
                    </div>
                    
                    <p>If you have any questions or concerns, please contact the IT department.</p>
                    
                    <p>Best regards,<br>
                    <strong>SACOETEC IT Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from SACOETEC. Please do not reply to this email.</p>
                    <p>© " . date('Y') . " SACOETEC. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getPasswordResetTextTemplate($otp) {
        return "
        Password Reset Request - SACOETEC
        
        You have requested to reset your password for your SACOETEC account.
        
        Your verification code is: $otp
        
        Important:
        - This code will expire in 15 minutes
        - If you didn't request this password reset, please ignore this email
        - For security reasons, never share this code with anyone
        - This code can only be used once
        
        If you have any questions or concerns, please contact the IT department.
        
        Best regards,
        SACOETEC IT Team
        
        This is an automated message from SACOETEC. Please do not reply to this email.
        © " . date('Y') . " SACOETEC. All rights reserved.
        ";
    }
    
    // Configuration methods - using EnvLoader for proper .env file handling
    private function getSmtpHost() {
        return EnvLoader::get('SMTP_HOST', 'smtp.gmail.com'); // Default to Gmail
    }
    
    private function getSmtpUsername() {
        return EnvLoader::get('SMTP_USERNAME', 'your-email@gmail.com');
    }
    
    private function getSmtpPassword() {
        return EnvLoader::get('SMTP_PASSWORD', 'your-app-password');
    }
    
    private function getSmtpPort() {
        return EnvLoader::get('SMTP_PORT', 587);
    }
    
    private function getFromEmail() {
        return EnvLoader::get('FROM_EMAIL', 'noreply@sacoetec.edu.ng');
    }
    
    private function getFromName() {
        return EnvLoader::get('FROM_NAME', 'SACOETEC');
    }
    
    // Test email configuration
    public function testEmailConfiguration() {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($this->getFromEmail());
            $this->mailer->Subject = "SACOETEC Email Configuration Test";
            $this->mailer->Body = "
                <h2>Email Configuration Test</h2>
                <p>This is a test email to verify that your SACOETEC email configuration is working correctly.</p>
                <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
            ";
            
            $result = $this->mailer->send();
            return $result ? "Email configuration test successful" : "Email configuration test failed";
            
        } catch (Exception $e) {
            return "Email configuration test failed: " . $e->getMessage();
        }
    }
} 