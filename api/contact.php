<?php
session_start();
require_once "config.php";

// Check for user session, redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Check if an auth token cookie exists
    if (isset($_COOKIE['auth_token'])) {
        $_SESSION['username'] = $_COOKIE['auth_token'];  // Set session from cookie
    } else {
        header("location: login.php");
        exit;
    }
}

$username = $_SESSION["username"];

// Query to fetch user's savings information
$savingsQuery = "SELECT id, savingstype, balance
FROM accbalance
JOIN users ON accbalance.username = users.username
WHERE users.username = ? AND users.account_status = 'active'";

$stmt = $conn->prepare($savingsQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$result = $stmt->get_result();

// Initialize variables
$savings = [];
if ($result->num_rows > 0) {
    $savings = $result->fetch_assoc();
} else {
    die("No savings found for the logged-in user.");
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact us</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<style>
    /* Basic form styles */
.contact-form {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: Arial, sans-serif;
}

.contact-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.contact-form input[type="email"],
.contact-form input[type="text"],
.contact-form textarea {
    width: calc(100% - 20px);
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 14px;
    box-sizing: border-box;
}

.contact-form textarea {
    resize: vertical; /* Allow vertical resizing of textarea */
}

.contact-form .btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-right: 10px;
    cursor: pointer;
    border-radius: 3px;
}

.contact-form .btn:hover {
    background-color: #45a049;
}

.contact-form .cancel-link {
    color: #666;
    font-size: 14px;
    text-decoration: none;
    margin-left: 10px;
}

.contact-form .cancel-link:hover {
    color: #333;
}
@media only screen and (max-width: 600px) {
    .contact-form {
        padding: 10px;
    }
    
    .contact-form input[type="email"],
    .contact-form input[type="text"],
    .contact-form textarea {
        width: calc(100% - 20px);
        padding: 8px;
        font-size: 12px;
    }
    
    .contact-form .btn {
        padding: 8px 16px;
        font-size: 14px;
    }
}
</style>
<body>
    <header>    
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 style="margin-top:8px;">
                    <span id="logo" class="oi" data-glyph="flag"></span>
                    Wealth Finance Management</h2>
                    <div class="header-menu">
                        <nav>
                            <ul class="nav mx-auto">
                                <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>      
                                <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>   
                                <li class="nav-item"><a class="nav-link" id="log" href="logout.php">Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <br><br>
    <form action="send.php" method="post" class="contact-form">
    <label for="email">Enter your Email:</label>
    <input type="email" id="email" name="email" value="" required><br>
    
    <label for="subject">Enter your Name:</label>
    <input type="text" id="subject" name="subject" value="" required><br>
    
    <label for="message">Enter your Message:</label>
    <textarea id="message" name="message" rows="4" required></textarea><br>
    
    <button type="submit" class="btn activate" name="send">Send</button>
    <a href="settings.php" class="cancel-link">Cancel</a>
</form>

    </body>
    </html>