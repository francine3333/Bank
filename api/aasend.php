<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if (isset($_POST["send_code"])) {
    $email = $_POST['email'];

    // Generate a random 6-digit code
    $code = mt_rand(100000, 999999);
    
    // Save the code in session for verification later
    session_start();
    $_SESSION['2fa_code'] = $code;
    $_SESSION['email'] = $email;

    // Send email with the code
    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lastimosarisch@gmail.com'; // Your Gmail email address
        $mail->Password   = 'qzmcyovyunprzszu';       // Your Gmail password
        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption; `ssl` also accepted
        $mail->Port       = 587;                   // TCP port to connect to
        
        //Recipients
        $mail->setFrom('lastimosarisch@gmail.com');
        $mail->addAddress($_POST['email']);        // Add a recipient
        
        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = 'Your Two-Factor Authentication Code';
        $mail->Body    = 'Your 2FA code is: ' . $code;
        $mail->send();

        echo "<script>alert('Email sent successfully');
        document.location.href = 'aaack.php';</script>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>