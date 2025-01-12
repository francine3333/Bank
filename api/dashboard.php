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

// Set auth_token cookie to keep user logged in after session expires
setcookie("auth_token", $username, time() + (30 * 24 * 60 *60 * 60 * 60), "/", "", true, true); // 30 days expiration

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lastimosarisch@gmail.com'; // Your Gmail email address
        $mail->Password   = 'qzmcyovyunprzszu';     // Your Gmail password
        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption; `ssl` also accepted
        $mail->Port       = 587;                   // TCP port to connect to
        
        // Recipients
        $mail->setFrom('lastimosarisch@gmail.com', 'System Mail');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function addNotification($username, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (username, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $message);
    $stmt->execute();
    $stmt->close();
}
$userJustLoggedIn = false; // Initialize the variable

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Query to check the username and password
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $_SESSION['username'] = $username;
        $userJustLoggedIn = true; // Set to true when user just logged in
    }
}

// Check if the user is logged in and has a valid session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

if ($username) {
    // Fetch user email
    $query = "SELECT email FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $email = $row['email'];

    // Check balances and send notifications
    $lowBalanceThreshold = 500;
    $notifications = [];

    if (!isset($_SESSION['low_balance_notified'])) {
        $query = "SELECT * FROM accbalance WHERE username = '$username'";
        $accbalanceresult = $conn->query($query);

        while ($savings = $accbalanceresult->fetch_assoc()) {
            if ($savings['balance'] < $lowBalanceThreshold) {
                $notificationMessage = "Low Balance Alert Your balance for savings id number '{$savings['id']}' with savings type '{$savings['savingstype']}' is below {$lowBalanceThreshold}.";
                addNotification($username, $notificationMessage);
                $notifications[] = $notificationMessage;
            }
        }

        // Send email notifications for low balances
        if (!empty($notifications)) {
            $notificationMessages = implode("\n", $notifications);
            sendEmail($email, "Low Balance Alert", "Dear $username,\n\n$notificationMessages\n\nThank you,\nWealth Finance Management");
            $_SESSION['low_balance_notified'] = true;
        }
    }

    // Check if a new device is used
    $currentDevice = $_SERVER['HTTP_USER_AGENT'];
    if (!isset($_SESSION['last_device'])) {
        $_SESSION['last_device'] = $currentDevice;
    }

    if ($_SESSION['last_device'] !== $currentDevice) {
        if (!isset($_SESSION['new_device_notified']) || $_SESSION['new_device_notified'] !== $currentDevice) {
            $notification = "Someone attempt to Login to a new device.";
            addNotification($username, $notification);
            sendEmail($email, "New Device Login Alert", "Dear $username,\n\n$notification\n\nThank you,\nWealth Finance Management");
            $_SESSION['new_device_notified'] = $currentDevice;
        }
        $_SESSION['last_device'] = $currentDevice;
    }

    // Reset session after a successful login
    if ($userJustLoggedIn) {
        unset($_SESSION['low_balance_notified']);
        unset($_SESSION['new_device_notified']);
    }
}

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

// Query to fetch user's usertype
$userQuery = "SELECT usertype FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($usertype);
$stmt->fetch();
$stmt->close();

// Query to fetch user's email
$emailQuery = "SELECT email FROM users WHERE username = ?";
$stmt = $conn->prepare($emailQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Check if email is retrieved
if (!isset($email)) {
    die("Email not found for the logged-in user.");
}

// Check if usertype is retrieved
if (!isset($usertype)) {
    die("Usertype not found for the logged-in user.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $savingstype = trim($_POST["savingstype"]);
    $balance = trim($_POST["balance"]);

    // Prepare an insert statement
    $insertQuery = "INSERT INTO accbalance (username, savingstype, balance) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssd", $username, $savingstype, $balance);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    // Redirect back to the dashboard
    header("location: dashboard.php");
    exit;
}

// Function to retrieve existing savings types from the database
function getExistingSavingsTypes($conn, $username) {
    $sql = "SELECT savingstype, balance, id FROM accbalance WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingSavingsTypes = [];
    while ($row = $result->fetch_assoc()) {
        $existingSavingsTypes[] = $row;
    }
    return $existingSavingsTypes;
}

// Fetch account balance for the logged-in user only
$accbalanceQuery = "SELECT * FROM accbalance WHERE username = ?";
$stmt = $conn->prepare($accbalanceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$accbalanceresult = $stmt->get_result();
$stmt->close();

// Fetch the user's  recent login device
$deviceQuery = "SELECT ip_address FROM login_attempts WHERE username = ? ORDER BY timestamp DESC LIMIT 1";
$stmt = $conn->prepare($deviceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($lastDevice);
$stmt->fetch();
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

</head>
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
                                <li class="nav-item"><a class="nav-link" href="chat.php">Chat</a></li>      
                           
                                <?php if ($usertype === "ADMINISTRATOR"): ?>
                                    <li class="nav-item"><a class="nav-link" href="manage-user.php">Accounts Management</a></li>
                                <?php endif; ?> 
                                <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li> 
                                <li class="nav-item"><a class="nav-link" id="log" href="logout.php">Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div id="maincontainer" class="row">
        <div class="col-md-4">
            <div id="notifications">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
                <p><span class="oi" data-glyph="Messageopen" title="icon name" aria-hidden="true"></span> Account type: <?php echo htmlspecialchars($usertype . ', You are now logged in'); ?></p>
              <button id="notification" class="btn-notification">View Notification</button>
              <br>  <strong>Welcome to our Online Banking. </strong>
            </div>   
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Live Better</h4>
                    <img src="ad.gif" />
                    <br /><br />
                    <p>We have everything you'd ever need.</p>
                    <a href="#" onclick="triggerInactive()">Learn More</a><br>
                    <a href="shop.php">Visit our shop</a><br>
                    <a href="./wealth_food/foodstore.php">Visit our food store</a><br>
                    <a href="redeem-giftcard.php">Redeem Gift codes</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div id="filler">
                <div class="row">
                    <ul class="vertical">
                        <li><span class="oi" data-glyph="book"></span><a href="crypto.php">Cryptocurrency</a></li>
                        <li><span class="oi" data-glyph="book"></span><a href="transaction-history.php">All transactions Reports</a></li>
                        <li><span class="oi" data-glyph="calculator"></span><a href="#" onclick="openaddnewsavings()">Add new Savings</a></li>
                        <li><span class="oi" data-glyph="briefcase"></span><a href="investment.php">Investment</a></li>
                        <li><span class="oi" data-glyph="info"></span><a href="viewinvestment.php">View Investment</a></li>
                        <li><span class="oi" data-glyph="grid-three-up"></span><a href="loan-application.php">Apply Loans</a></li>
                    </ul>
                </div>
            </div>
            <div class="modal" id="addnewsavings">
            <div class="modal-content">
                <h2><strong>Add New Savings</strong></h2>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <label for="savingstype">Select Savings Type:</label>  
                    <select id="savingstype" name="savingstype" required>
                        <option value="My savings">My savings</option>
                        <option value="Checking savings">Checking savings</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Money Market">Money Market</option>
                        <option value="Health Savings">Health Savings</option>
                        <option value="Retirement Savings">Retirement Savings</option>
                    </select><br><br>
                    <div class="form-group">
                        <label for="balance">Initial Balance</label>
                        <input type="number" name="balance" id="balance" class="form-control" required>
                    </div>
                    <button type="submit" name="btnopensavings" style="font-weight: bold; background-color: green;">Add Savings Type</button>
                    <button type="button" onclick="closeaddnewsavings();" style="font-weight: bold; background-color: red;">Cancel</button>
                </form>
            </div>
        </div>
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <?php
                    // Fetch and display notifications
                    $notificationQuery = "SELECT message FROM notifications WHERE username = ? ORDER BY created_at DESC";
                    $stmt = $conn->prepare($notificationQuery);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $notificationResult = $stmt->get_result();
                    if ($notificationResult->num_rows > 0) {
                        while ($notification = $notificationResult->fetch_assoc()) {
                            echo "<p style='color:red'>" . htmlspecialchars($notification['message']) . "</p>";
                        }
                    } else {
                        echo "<p>No new notifications.</p>";
                    }
                    $stmt->close();
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
        <div class="row">
        <?php
$existingSavings = getExistingSavingsTypes($conn, $username);
if (!empty($existingSavings)) {
    foreach ($existingSavings as $savings) {
        echo "<div id='savingsaccountRow' class='col-md-12'><br>" .
            "<div id='accountInfo'><br>" .
            "<div class='row'><br>" .
            "<div class='col-md-8'>" .
                "<h5 style='margin-left:15px;'>" . htmlspecialchars($savings['savingstype']) . ", ID: " . htmlspecialchars($savings['id']) . "</h5>" .
                "<hr />" .
                "<h1 style='margin-left:15px;'>₱" . number_format($savings['balance'], 2) . "</h1>" .
            "</div>" .
            "<div class='col-md-4 text-right'>" .
                "<ul class='vertical'>" .
                '<center>' . "<li><a href='deposit.php'>Deposit</a><span class='oi' data-glyph='caret-right'></span></li>" . "<hr />" .
                    "<li><a href='banktransfer.php'>Make a transfer</a><span class='oi' data-glyph='caret-right'></span></li>" . "<hr />" .
                    "<li><a class='moreactions' href='withdraw.php'>Withdraw</a><span class='oi' data-glyph='caret-right'></span></li>" .
                    '</center>'.
                "</ul>" .
            "</div>" .
            "</div>" .
            "</div>" .
            "</div>";
    }
} else {
    echo "<p>No existing savings types found.</p>";
}
?>
    </div>
    <script>
    function openaddnewsavings() {
        var modal = document.getElementById('addnewsavings');
        modal.style.display = 'block';
    }

    function closeaddnewsavings() {
        var modal = document.getElementById('addnewsavings');
        modal.style.display = 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
    // Event listener for notification button
    document.getElementById('notification').addEventListener('click', function() {
        $('#notificationModal').modal('show');
        fetchNotifications();
    });

    // Function to fetch notifications from server
    function fetchNotifications() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_notifications.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var notifications = JSON.parse(xhr.responseText);
                var notificationList = document.getElementById('notification-list');
                notificationList.innerHTML = '';

                if (Array.isArray(notifications) && notifications.length > 0) {
                    notifications.forEach(function(notification) {
                        var listItem = document.createElement('li');
                        listItem.className = 'list-group-item';
                        listItem.textContent = notification.message + ' - ' + notification.created_at;
                        notificationList.appendChild(listItem);
                    });
                } else {
                    notificationList.innerHTML = '<li class="list-group-item">No notifications found.</li>';
                }
            }
        };
        xhr.send();
    }
});
    </script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</body>
</html>