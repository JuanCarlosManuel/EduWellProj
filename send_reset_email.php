<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer - Composer autoload
require 'vendor/autoload.php';

function sendResetEmail($toEmail, $userName, $token) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mmapatoc@tip.edu.ph'; // ⚠️ CHANGE THIS
        $mail->Password = 'suhv ceyy lmht dggc';     // ⚠️ CHANGE THIS (use App Password, not regular password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email settings
        $mail->setFrom('mmapatoc@tip.edu.ph', 'EduWell Support');
        $mail->addAddress($toEmail, $userName);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your EduWell Password';
        
        // Reset link
        $resetLink = "http://localhost/eduwell/new_password.php?token=" . $token;
        
        // Email body
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%); 
                          color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; 
                          color: white; text-decoration: none; border-radius: 6px; 
                          font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                .info-box { background: #e3f2fd; border-left: 4px solid #2196f3; 
                            padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <p>Hi <strong>{$userName}</strong>,</p>
                    <p>We received a request to reset your EduWell password. Click the button below to create a new password:</p>
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #667eea;'>{$resetLink}</p>
                    <div class='info-box'>
                        <strong>📌 Note:</strong> You can use this link anytime to reset your password. It will remain active until you use it.
                    </div>
                    <p>If you didn't request this, please ignore this email. Your password will remain unchanged.</p>
                </div>
                <div class='footer'>
                    <p>© 2025 EduWell | This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hi {$userName},\n\nClick this link to reset your password: {$resetLink}\n\nYou can use this link anytime to reset your password.\n\nIf you didn't request this, please ignore this email.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>