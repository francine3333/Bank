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

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');
$username = $_SESSION["username"];
$error = '';
$success = '';

// Handling the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fromAccount = $_POST['fromAccount'];
    $toAccount = $_POST['toaccount'];
    $savingstype_id = $_POST['savingstype_id'];
    list($savingstype, $id) = explode('|', $savingstype_id);
    $amount = $_POST['amount'];

    if (empty($toAccount) || empty($savingstype_id) || empty($amount)) {
        $error = "All fields are required.";
    } else {
        if ($fromAccount === $toAccount) {
            $error = "Cannot transfer money to the same account.";
        } else {
            // Check if the receiver account exists
            $queryReceiverExists = "SELECT 1 FROM accbalance WHERE username = ?";
            $stmtReceiverExists = $conn->prepare($queryReceiverExists);
            $stmtReceiverExists->bind_param("s", $toAccount);
            $stmtReceiverExists->execute();
            $stmtReceiverExists->store_result();

            if ($stmtReceiverExists->num_rows === 0) {
                $error = "Receiver account does not exist.";
                $stmtReceiverExists->close();
            } else {
                $stmtReceiverExists->close();

                // Fetch the current balance of the sender
                $queryBalance = "SELECT balance FROM accbalance WHERE username = ? AND id = ?";
                $stmtBalance = $conn->prepare($queryBalance);
                $stmtBalance->bind_param("ss", $fromAccount, $id);
                $stmtBalance->execute();
                $stmtBalance->bind_result($fromBalance);
                $stmtBalance->fetch();
                $stmtBalance->close();

                if ($fromBalance < $amount) {
                    $error = "Insufficient balance.";
                } else {
                    // Fetch the current balance of the receiver
                    $queryReceiverBalance = "SELECT balance FROM accbalance WHERE username = ?";
                    $stmtReceiverBalance = $conn->prepare($queryReceiverBalance);
                    $stmtReceiverBalance->bind_param("s", $toAccount);
                    $stmtReceiverBalance->execute();
                    $stmtReceiverBalance->bind_result($toBalance);
                    $stmtReceiverBalance->fetch();
                    $stmtReceiverBalance->close();

                    // Perform the transfer
                    $newFromBalance = $fromBalance - $amount;
                    $newToBalance = $toBalance + $amount;

                    // Update the sender's balance
                    $updateSender = "UPDATE accbalance SET balance = ? WHERE username = ? AND id = ?";
                    $stmtUpdateSender = $conn->prepare($updateSender);
                    $stmtUpdateSender->bind_param("dss", $newFromBalance, $fromAccount, $id);
                    $stmtUpdateSender->execute();
                    $stmtUpdateSender->close();

                    // Update the receiver's balance
                    $updateReceiver = "UPDATE accbalance SET balance = ? WHERE username = ?";
                    $stmtUpdateReceiver = $conn->prepare($updateReceiver);
                    $stmtUpdateReceiver->bind_param("ds", $newToBalance, $toAccount);
                    $stmtUpdateReceiver->execute();
                    $stmtUpdateReceiver->close();

                    // Insert the transfer record
                    $insertTransfer = "INSERT INTO transferhistory (fromaccount, toaccount, usersavings, id, amount, transfer_date) VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmtInsertTransfer = $conn->prepare($insertTransfer);

                    if (!$stmtInsertTransfer) {
                        $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                    } else {
                        $stmtInsertTransfer->bind_param("sssis", $fromAccount, $toAccount, $savingstype, $id, $amount);
                        $stmtInsertTransfer->execute();

                        if ($stmtInsertTransfer->error) {
                            $error = "Execute failed: (" . $stmtInsertTransfer->errno . ") " . $stmtInsertTransfer->error;
                        } else {
                            $success = "Bank Transfer successful.";
                        }
                        $stmtInsertTransfer->close();
                    }
                }
            }
        }
    }
}

// Fetching combined savingstype and id options dynamically from the database
$querySavingstypeOptions = "SELECT savingstype, id FROM accbalance WHERE username = ?";
$stmtSavingstypeOptions = $conn->prepare($querySavingstypeOptions);
$stmtSavingstypeOptions->bind_param("s", $username);
$stmtSavingstypeOptions->execute();
$stmtSavingstypeOptions->bind_result($savingstype, $id);

$savingstypeOptions = '';
while ($stmtSavingstypeOptions->fetch()) {
    $savingstypeOptions .= '<option value="' . htmlspecialchars($savingstype) . '|' . htmlspecialchars($id) . '">' . htmlspecialchars($savingstype) . ' - ' . htmlspecialchars($id) . '</option>';
}
$stmtSavingstypeOptions->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer - Wealth Finance Management</title>
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
            font-size: 1rem;
        }
        form button:hover {
            background-color: #003366;
        }
        .alert {
            margin-top: 20px;
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
                    <h2>Bank Transfer</h2>
                    <!-- Display success or error messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <div class="form-group">
                            <label for="fromAccount">From Account (ID):</label>
                            <input type="text" id="fromAccount" name="fromAccount" value="<?php echo htmlspecialchars($username); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="toaccount">To Account (ID):</label>
                            <input type="text" id="toaccount" name="toaccount" required>
                        </div>

                        <div class="form-group">
                            <label for="savingstype_id">Select Savings Type and ID:</label>
                            <select id="savingstype_id" name="savingstype_id" required>
                                <?php echo $savingstypeOptions; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="amount">Amount to Transfer:</label>
                            <input type="number" id="amount" name="amount" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Transfer</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
