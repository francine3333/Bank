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
                WHERE users.username = ?";

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

// Check if usertype is retrieved
if (!isset($usertype)) {
    die("Usertype not found for the logged-in user.");
}

$username = $_SESSION["username"];

// Fetch list of user investments
$userInvestmentsQuery = "SELECT ui.username, ui.amount, ui.date_time, i.package_name AS investment_name
                         FROM user_investments ui
                         JOIN investments i ON ui.investment_id = i.id
                         WHERE ui.username = ?";
$stmt = $conn->prepare($userInvestmentsQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$userInvestmentsResult = $stmt->get_result();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Current Investment</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
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
                            <li class="nav-item"><a class="nav-link" id="log" href="logout.php">Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="main">
        <h3>All your Investments</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Investment Name</th>
                    <th>Amount</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($userInvestmentsResult->num_rows > 0) {
                    while ($row = $userInvestmentsResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['investment_name'] . "</td>";
                        echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                        echo "<td>" . $row['date_time'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No investments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
