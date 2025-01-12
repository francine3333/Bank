<?php
session_start();
require_once "config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to get IP address
function getIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
require_once "../vendor/namespaced/Detection/MobileDetect.php";
// Function to get device information
use Detection\MobileDetect;
function getDevice() {
    $detect = new MobileDetect;
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if ($detect->isMobile()) {
        return 'Mobile Device: ' . substr($userAgent, 0, 255);
    } elseif ($detect->isTablet()) {
        return 'Tablet Device: ' . substr($userAgent, 0, 255);
    } else {
        return 'Desktop Device: ' . substr($userAgent, 0, 255);
    }
}

// Function to get location information
function getLocation($ip) {
    $json = file_get_contents("http://ipinfo.io/{$ip}/json");
    $details = json_decode($json);
    if (isset($details->city, $details->region, $details->country)) {
        return $details->city . ', ' . $details->region . ', ' . $details->country;
    } else {
        return 'Unknown';
    }
}

// Check for persistent login cookie
if (isset($_COOKIE['auth_token']) && !isset($_SESSION['username'])) {
    // Restore session from cookie
    $_SESSION['username'] = $_COOKIE['auth_token'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username, password, and email
   $login_message = ""; 
    $login_err = "";
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);
        
        // Validate user credentials and check account status
        $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND account_status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // User is authenticated, now check if the email matches
            $sql_email_check = "SELECT email FROM users WHERE username = ? AND email = ?";
            $stmt_email_check = $conn->prepare($sql_email_check);
            $stmt_email_check->bind_param("ss", $username, $email);
            $stmt_email_check->execute();
            $result_email_check = $stmt_email_check->get_result();
            
            if ($result_email_check->num_rows == 1) {
                // Check if a 2FA code is already in session
                if (!isset($_SESSION['2fa_code'])) {
                    // Generate 2FA code
                    $code = mt_rand(100000, 999999);
                    $_SESSION['2fa_code'] = $code;
                    $_SESSION['username'] = $username;

                    // Send email with the code
                    require 'phpmailer/src/Exception.php';
                    require 'phpmailer/src/PHPMailer.php';
                    require 'phpmailer/src/SMTP.php';

                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'lastimosarisch@gmail.com'; // Your Gmail email address
                        $mail->Password   = 'qzmcyovyunprzszu';     // Your Gmail app password
                        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption; `ssl` also accepted
                        $mail->Port       = 587;                   // TCP port to connect to
                        
                        // Recipients
                        $mail->setFrom('lastimosarisch@gmail.com', 'System Mail');
                        $mail->addAddress($email); 
                        
                        // Content
                        $mail->isHTML(true);                       // Set email format to HTML
                        $mail->Subject = 'Your Two-Factor Authentication Code';
                        $mail->Body    = 'Your Two Factor Authentication code is: ' . $code . ' PLEASE DO NOT SHARE THIS WITH OTHERS';
                        $mail->Priority = 1;                       // Mark email as important
                        $mail->send();
                    } catch (Exception $e) {
                        $login_err = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }

                // Log login attempt
                $ip_address = getIpAddress();
                $device = getDevice();
                $location = getLocation($ip_address);
                $timestamp = date("Y-m-d H:i:s");

                // Check if a login attempt for this username and timestamp already exists
                $stmt_check = $conn->prepare("SELECT * FROM login_attempts WHERE username = ? AND timestamp = ?");
                $stmt_check->bind_param("ss", $username, $timestamp);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows == 0) {
                    // No existing record found, insert new record
                    $stmt_log = $conn->prepare("INSERT INTO login_attempts (username, ip_address, device, location, timestamp) VALUES (?, ?, ?, ?, ?)");
                    $stmt_log->bind_param("sssss", $username, $ip_address, $device, $location, $timestamp);
                    $stmt_log->execute();
                    $stmt_log->close();
                }

                $stmt_check->close();

                // Set a persistent login cookie (valid for 30 days)
                setcookie("auth_token", $username, time() + (30 * 24 * 60 * 60), "/", "", true, true);

                // Redirect user to 2FA verification page
                echo '<script>
                        alert("Two-factor Authentication code sent to your email successfully.");
                        window.location.href = "verify_2fa.php";
                      </script>';
                      $login_message = "Log In Successful"; 
                exit; 
            } else {
                $login_err = "The provided email does not match our records for the given username.";
            }
        } else {
            $login_err = "Invalid username/password or account is inactive.";
        }
    } else {
        $login_err = "Please fill in all required fields.";
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth Finance Management - Secure Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <style>
        body {
            background: linear-gradient(to bottom right, #1e3c72, #2a5298);
            color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background-color: #203647;
            padding: 10px 20px;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
            font-weight: bold;
        }
        .navbar-brand img {
            width: 150px;
        }
        .hero-section {
            background: linear-gradient(140deg, #0F2027, #203A43, #2C5364);
            color: #fff;
            text-align: center;
            padding: 60px 15px;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        .hero-section p {
            font-size: 1.2rem;
            margin-top: 15px;
        }
        .notification {
    background-color: rgb(211, 211, 211); /* Neutral background */
    margin: 20px auto; /* Centered and spaced below other elements */
    padding: 15px;
    border-radius: 5px;
    font-size: 18px; /* Slightly smaller for readability */
    max-width: 80%; /* Limit the width */
    text-align: center; /* Center text alignment */
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    position: relative; /* Allows for proper positioning of child elements */
    z-index: 1000; /* Ensure it appears above other content */
}

/* Error notification styles */
.notification.error {
    background-color: #f8d7da; /* Light red background */
    color: #721c24; /* Dark red text for better contrast */
    border: 1px solid #f5c6cb; /* Border for clearer definition */
}

/* Success notification styles */
.notification.success {
    background-color: #d4edda; /* Light green background */
    color: #155724; /* Dark green text for better contrast */
    border: 1px solid #c3e6cb; /* Border for clearer definition */
}

/* Add smooth animation for visibility changes */
.notification.fade-in {
    animation: fadeIn 0.5s ease-in-out forwards;
}
.notification.fade-out {
    animation: fadeOut 0.5s ease-in-out forwards;
}

/* Keyframes for fade-in and fade-out */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}


        .login-container {
            background-color: #1f2937;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin-top: -50px;
        }
        .login-container h2 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ffc107;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-control {
            background-color: #f7f7f7;
            border: 1px solid #ced4da;
            color: #495057;
        }
        .btn-login {
            background-color: #ffc107;
            color: #212529;
            font-weight: bold;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #e0a800;
        }
        .trust-badges img {
            width: 60px;
            margin: 0 10px;
        }
        footer {
            background-color: #203647;
            color: #ccc;
            padding: 40px;
            text-align: left;
        }
        .footer-link {
            color: #ffc107;
            text-decoration: none;
        }
        .footer-link:hover {
            color: #e0a800;
        }
        .features-section, .testimonials-section, .faqs-section, .security-section {
            background: linear-gradient(140deg, #0F2027, #203A43, #2C5364);
            padding: 60px 0;
        }
        .testimonial, .faq, .security-feature {
            background: linear-gradient(140deg, #0F2027, #203A43, #2C5364);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .faq p {
            font-size: 1rem;
        }
        .partners img {
            width: 80px;
            margin: 10px;
        }
        .social-media-icons {
            display: flex;
            justify-content: center;
            gap: 20px; 
        }

        .social-icon img {
            width: 40px; 
            height: auto; 
            transition: transform 0.3s; 
        }

        .social-icon img:hover {
            transform: scale(1.1); 
        }

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#"><img src="logo.png" alt="Wealth Finance Management"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#security">Security</a></li>
                <li class="nav-item"><a class="nav-link" href="#faqs">FAQs</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="hero-section">
    <h1>Secure Your Financial Future</h1>
    <p>Login to manage your wealth and access secure features.</p>
</div>

<!-- Notification Area -->
<?php if (!empty($login_err)): ?>
    <div class="notification error fade-in"><?php echo $login_err; ?></div>
<?php elseif (!empty($login_message)): ?>
    <div class="notification success fade-in"><?php echo $login_message; ?></div>
<?php endif; ?>

<!-- Login Form Section -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
          
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</div>
    
    <section id="features" class="features-section text-center">
        <div class="container">
            <h2>Our Key Features</h2>
            <div class="row mt-4">
                <div class="col-md-4">
                    <h5>Secure Transactions</h5>
                    <p>All transactions are encrypted for maximum security.</p>
                </div>
                <div class="col-md-4">
                    <h5>24/7 Customer Support</h5>
                    <p>We're here to help whenever you need us.</p>
                </div>
                <div class="col-md-4">
                    <h5>Financial Insights</h5>
                    <p>Detailed reports and analytics for smart financial planning.</p>
                </div>
            </div>
        </div> 
    </section>
    <section id="security" class="security-section text-center bg-light">
        <div class="container">
            <h2>Bank-Level Security</h2>
            <div class="row mt-4">
                <div class="col-md-4 security-feature">
                    <h5>Multi-Factor Authentication</h5>
                    <p>Enhanced security with two-factor authentication.</p>
                </div>
                <div class="col-md-4 security-feature">
                    <h5>Data Encryption</h5>
                    <p>All data is encrypted, keeping your information safe.</p>
                </div>
                <div class="col-md-4 security-feature">
                    <h5>Fraud Detection</h5>
                    <p>Advanced monitoring for any suspicious activities.</p>
                </div>
            </div>
        </div>
    </section>
    <section id="faqs" class="faqs-section text-center">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="faq">
                        <h5>What should I do if I forget my password?</h5>
                        <p>Click on "Forgot Password?" link on the login page.</p>
                    </div>
                    <div class="faq">
                        <h5>How can I contact customer support?</h5>
                        <p>Reach out to us via the Contact Us section.</p>
                    </div>
                </div>      
                <div class="col-md-6">
                    <div class="faq">
                        <h5>Is my data secure?</h5>
                        <p>Yes, we use bank-level security to protect your data.</p>
                    </div>
                    <div class="faq">
                        <h5>Can I change my username?</h5>
                        <p>Username changes can be requested through customer support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="testimonials" class="testimonials-section text-center">
        <div class="container">
            <h2>What Our Clients Say</h2>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"The best financial management platform I have ever used!"</p>
                        <cite>- John Doe</cite>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"Excellent customer support and user-friendly interface."</p>
                        <cite>- Jane Smith</cite>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"Highly recommend for anyone looking to manage their finances."</p>
                        <cite>- Alex Brown</cite>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <!-- Footer -->
     <footer id="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <p>Email: <a href="mailto:lastimosarisch@gmail.com" class="footer-link">lastimosarisch@gmail.com</a></p>
                    <p>Phone: 09192789050</p>
                </div>
                <div class="col-md-3">
                    <h5>Resources</h5>
                    <ul>
                        <li><a href="#" class="footer-link">Terms of Service</a></li>
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Partnerships</h5>
                    <div class="partners">
                        <img src="./img/logo1.png" alt="Partner 1">
                        <img src="./img/log2.png" alt="Partner 2">
                    </div>
                </div>
                <div class="col-md-3">
                    <h5>Stay Connected</h5>
                    <p>Follow us on:</p>
                    <div class="social-media-icons text-center mt-4">
    <a href="#" class="social-icon">
        <img src="./img/facebook.svg" alt="Facebook">
    </a>
    <a href="#" class="social-icon">
        <img src="./img/twitter.svg" alt="Twitter">
    </a>
    <a href="#" class="social-icon">
        <img src="./img/instagram.svg" alt="Instagram">
    </a>
</div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    // Fade out notifications after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.notification').forEach(el => el.classList.add('fade-out'));
    }, 5000);
</script>
</body>
</html>
