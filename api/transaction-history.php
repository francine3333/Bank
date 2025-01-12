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
$savingsQuery = "SELECT accbalance.*
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

// Fetch transaction history for the logged-in user only
if (isset($_POST['btnsearch'])) {
    $searchvalue = '%' . $_POST['txtsearch'] . '%';
    $historyQuery = "SELECT * FROM thistory WHERE username = ? AND username LIKE ? OR id LIKE ? OR transaction_type LIKE ? ORDER BY username";
    $stmt = $conn->prepare($historyQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username, $searchvalue, $searchvalue, $searchvalue);
} else {
    $historyQuery = "SELECT * FROM thistory WHERE username = ? ORDER BY username";
    $stmt = $conn->prepare($historyQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
}

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$historyResult = $stmt->get_result();
$stmt->close();

// Fetch bank transfer history for the logged-in user only
if (isset($_POST['btnsearch'])) {
    $searchvalue = '%' . $_POST['txtsearch'] . '%';
    $resulthistoryQuery = "SELECT * FROM transferhistory WHERE fromaccount = ? AND fromaccount LIKE ? OR toaccount LIKE ? OR id LIKE ? ORDER BY fromaccount";
    $stmt = $conn->prepare($resulthistoryQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username, $searchvalue, $searchvalue, $searchvalue);
} else {
    $resulthistoryQuery = "SELECT * FROM transferhistory WHERE fromaccount = ? ORDER BY fromaccount";
    $stmt = $conn->prepare($resulthistoryQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
}

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$resulthistoryResult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
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
        <h3>Deposit & Withdrawal Transaction History</h3>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <input type="text" name="txtsearch" placeholder="Search:">
                <button type="submit" name="btnsearch" class="btn btn-primary">Search</button>
            </div>
        </form>
        <table class="table table-striped">
            <!-- Table header -->
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Amount</th>
                    <th>Transaction Type</th>
                    <th>Type of Saving</th>
                    <th>Saving ID Number</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP to fetch and display transaction history -->
                <?php
                if ($historyResult->num_rows > 0) {
                    while ($row = $historyResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                        echo "<td>" . ($row['transaction_type'] == 'Deposit' ? 'Deposit' : ($row['transaction_type'] == 'Withdraw' ? 'Withdrawal' : $row['transaction_type'])) . "</td>";
                        echo "<td>" . $row['usersavings'] . "</td>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['transaction_date'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No transaction history found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="main">
        <h3>Bank Transfer History</h3>
        <table class="table table-striped">
            <!-- Table header -->
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Type of Saving</th>
                    <th>Saving ID Number</th>
                    <th>Amount</th>
                    <th>Bank Transfer Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP to fetch and display bank transfer history -->
                <?php
                if ($resulthistoryResult->num_rows > 0) {
                    while ($row = $resulthistoryResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['fromaccount'] . "</td>";
                        echo "<td>" . $row['toaccount'] . "</td>";
                        echo "<td>" . $row['usersavings'] . "</td>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                        echo "<td>" . $row['transfer_date'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No bank transfer history found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
