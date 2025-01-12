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

// Fetching savingstype and id options dynamically from the database for combined display
$querySavingOptions = "SELECT savingstype, id FROM accbalance WHERE username = ?";
$stmtSavingOptions = $conn->prepare($querySavingOptions);
$stmtSavingOptions->bind_param("s", $username);
$stmtSavingOptions->execute();
$stmtSavingOptions->bind_result($savingstype, $id);

$savingOptionsCombined = '';
while ($stmtSavingOptions->fetch()) {
    $savingOptionsCombined .= '<option value="' . htmlspecialchars($id) . '">' . htmlspecialchars($savingstype . ' - ' . $id) . '</option>';
}
$stmtSavingOptions->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $savingoptions = $_POST["id"];
    $amount = floatval($_POST["amount"]);

    // Validate input
    if (empty($amount)) {
        die("Error: Amount cannot be empty.");
    }

    // Update balance in the database for savingoptions based on the selected id
    $updateQuery = "UPDATE accbalance SET balance = balance - ? WHERE username = ? AND id = ?";
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("dss", $amount, $username, $savingoptions);

    // Execute the update query
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Insert into thistory table
    $transaction_type = "Withdraw";
    date_default_timezone_set('Asia/Manila');
    $transaction_date = date("Y-m-d H:i:s");

    // Insert into thistory database table
    $insertQuery = "INSERT INTO thistory (username, amount, transaction_type, usersavings, id, transaction_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertQuery);

    if (!$stmtInsert) {
        die("Error preparing insert statement: " . $conn->error);
    }

    $stmtInsert->bind_param("sdssss", $username, $amount, $transaction_type, $savingstype, $savingoptions, $transaction_date);

    if (!$stmtInsert->execute()) {
        die("Error executing insert statement: " . $stmtInsert->error);
    }

    // Close the statement
    $stmtInsert->close();
    $stmt->close();

    // Redirect to the dashboard
    header("location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash-out - Wealth Finance Management</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f4f7f9;
            color: #333;
        }
        header {
            background-color: #004080;
            color: white;
            padding: 15px 0;
        }
        header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .header-menu {
            margin-top: 10px;
        }
        .header-menu a {
            color: white;
            font-weight: bold;
            margin-left: 20px;
        }
        .header-menu a:hover {
            text-decoration: underline;
        }
        main {
            margin-top: 30px;
        }
        main h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        form .form-group label {
            font-weight: bold;
        }
        form .form-group input, 
        form .form-group select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #003366;
        }
    </style>
</head>
<body>
    <header>    
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>
                        <i class="fas fa-university"></i> Wealth Finance Management
                    </h2>
                </div>
                <div class="col-md-6 text-right">
                    <div class="header-menu">
                        <a href="dashboard.php">Home</a>
                        <a id="log" href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h2>Withdraw to Savings</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <div class="form-group">
                            <label for="txtusername">Username:</label>
                            <input type="text" id="txtusername" name="txtusername" value="<?php echo htmlspecialchars($username); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="savingoptions">Select Saving Type and ID Number:</label>
                            <select id="savingoptions" name="id" required>
                                <?php echo $savingOptionsCombined; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount to Withdraw:</label>
                            <input type="number" id="amount" name="amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Withdraw</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

