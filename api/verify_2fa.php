<?php
session_start();
require_once "config.php"; // Ensure config.php sets up $pdo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Check if the 2FA code is set in session
if (!isset($_SESSION['2fa_code'])) {
    header("location: login.php");
    exit;
}

$verification_err = "";
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resend_code'])) {
        // Logic to resend the verification code
        $verification_code = generate_verification_code();
        $_SESSION['2fa_code'] = $verification_code;

        // Send the verification code to the user via email, SMS, etc.
        send_verification_code($_SESSION['username'], $verification_code);

        // Display a success message to the user
        $verification_err = "A new verification code has been sent to your email/phone.";
    } else {
        $entered_code = trim($_POST["verification_code"]);

        if ($entered_code == $_SESSION['2fa_code']) {
            // 2FA code is correct, login the user
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $_SESSION['username'];
            
            // Clear 2FA code from session
            unset($_SESSION['2fa_code']);
            
            header("location: dashboard.php");
            exit;
        } else {
            $verification_err = "Invalid verification code.";
        }
    }
}

// Function to generate a random 6-digit verification code
function generate_verification_code() {
    return rand(100000, 999999);  // Generates a random 6-digit number
}

// Function to send the verification code (e.g., via email or SMS)
function send_verification_code($username, $code) {
    $email = get_user_email($username);  // Retrieve the user's email from the database

    // Send email logic (as an example)
    $subject = "Your Verification Code";
    $message = "Your verification code is: " . $code;
    send_email($email, $subject, $message);  // Implement send_email() to use PHPMailer
}

// Fetch user's email from the database based on their username
function get_user_email($username) {
    global $pdo;  // Use the global $pdo variable for database connection

    // Check if $pdo is null
    if (!$pdo) {
        throw new Exception("Database connection not initialized.");
    }

    $stmt = $pdo->prepare("SELECT email FROM users WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found.");
    }

    return $user['email'];
}

function send_email($to, $subject, $message) {
 
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'lastimosarisch@gmail.com'; // Your Gmail email address
    $mail->Password   = 'qzmcyovyunprzszu';     // Your Gmail password
    $mail->SMTPSecure = 'tls';                 // Enable TLS encryption; `ssl` also accepted
    $mail->Port       = 587;                   // TCP port to connect to

    $mail->setFrom('lastimosarisch@gmail.com', 'Bank Web Application');
    $mail->addAddress($to);

    $mail->Subject = $subject;
    $mail->Body = $message;

    if (!$mail->send()) {
        throw new Exception("Mail could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Wealth Finance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
</head>
<style>
    /* Basic reset and body setup */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        background-color: #f3f4f6;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: #333;
    }

    /* Container for centered card */
    .container {
        max-width: 420px;
        width: 100%;
        padding: 20px;
    }

    /* Card design with shadow and rounded corners */
    .verification-container {
        background: #ffffff;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-align: center;
    }

    /* Header style for title */
    .verification-header {
        font-size: 1.8rem;
        color: #004080;
        font-weight: 700;
        margin-bottom: 20px;
    }

    /* Error message style */
    .error {
        background-color: #ff4d4d;
        color: white;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    /* Form labels and input styling */
    .form-group label {
        font-size: 1rem;
        color: #333;
        font-weight: 600;
        margin-bottom: 5px;
        text-align: left;
        display: block;
    }

    /* Input styling for modern appearance */
    .form-control {
        padding: 15px;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        color: #4b5563;
        background-color: #f9fafb;
        transition: border-color 0.3s;
    }
    .form-control:focus {
        border-color: #004080;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 64, 128, 0.2);
    }

    /* Main button style for submit action */
    .btn-verify {
        background-color: #004080;
        color: white;
        font-weight: bold;
        padding: 12px;
        border-radius: 8px;
        border: none;
        width: 100%;
        font-size: 1.1rem;
        transition: background-color 0.3s ease;
    }

    /* Hover effect for button */
    .btn-verify:hover {
        background-color: #003366;
    }

    /* Resend Code button with a different color */
    .btn-resend {
        background-color: #ff9900;
        color: white;
        font-weight: bold;
        padding: 12px;
        border-radius: 8px;
        border: none;
        width: 100%;
        font-size: 1.1rem;
        margin-top: 15px;
        transition: background-color 0.3s ease;
    }

    .btn-resend:hover {
        background-color: #e68900;
    }

    /* Additional footer with link styling */
    .footer {
        margin-top: 20px;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .footer a {
        color: #004080;
        text-decoration: none;
        font-weight: 600;
    }

    .footer a:hover {
        text-decoration: underline;
    }
</style>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="verification-header">Verify Your Identity</h2>
            <?php 
            if (!empty($verification_err)) {
                echo '<div class="error">' . $verification_err . '</div>';
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="verification_code">Verification Code</label>
                    <input type="text" id="verification_code" name="verification_code" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-verify">Verify</button>
                <button type="submit" name="resend_code" class="btn btn-resend">Resend Code</button>
            </form>
        </div>
    </div>
</body>
</html>
