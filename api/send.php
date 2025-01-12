<?php
session_start();
require_once "config.php"; // configurations

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Initialize PHPMailer object
$mail = new PHPMailer(true); // true enables exceptions

// SMTP configuration (you missed setting up $mail object)
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'lastimosarisch@gmail.com'; // Your Gmail email address
$mail->Password   = 'qzmcyovyunprzszu';     // Your Gmail password
$mail->SMTPSecure = 'tls';                  // Enable TLS encryption; `ssl` also accepted
$mail->Port       = 587;                    // TCP port to connect to

// Sender and recipient details
$mail->setFrom('lastimosarisch@gmail.com', 'System Mail');
$mail->addAddress($_POST["email"]); // Assuming $_POST["email"] contains recipient's email address

// Email content
$mail->isHTML(true);                    // Set email format to HTML
$mail->Subject = $_POST["subject"];
$mail->Body    = $_POST["message"];
$mail->Priority = 1;                    // Set email to priority 

// Try to send email
try {
    $mail->send();
    echo '<script>
    alert("The  email Successfully Send.");
    window.location.href = "contact.php";
  </script>';
} catch (Exception $e) {
    echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
}
$conn->close();
?>
