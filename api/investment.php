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

// Fetch list of available investment packages
$investmentQuery = "SELECT id, package_name, description, amount FROM investments";
$result = $conn->query($investmentQuery);
if (!$result) {
    die("Error fetching investments: " . $conn->error);
}
$investments = [];
while ($row = $result->fetch_assoc()) {
    $investments[] = $row;
}

// Fetch account balance for the logged-in user
$accbalanceQuery = "SELECT id, balance FROM accbalance WHERE username = ?";
$stmt = $conn->prepare($accbalanceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$accbalanceresult = $stmt->get_result();
$savingIDs = [];
while ($row = $accbalanceresult->fetch_assoc()) {
    $savingIDs[] = $row;
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $investment_id = $_POST["investment_id"];
    $payment_amount = $_POST["payment_amount"];
    $selected_id = $_POST["id"];

    // Validate investment_id to ensure it exists in the investments table
    $validateQuery = "SELECT amount FROM investments WHERE id = ?";
    $stmt = $conn->prepare($validateQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $investment_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $investmentResult = $stmt->get_result();
    if ($investmentResult->num_rows > 0) {
        $investment_amount = $investmentResult->fetch_assoc()['amount'];
    } else {
        die("Error: Invalid investment package selected.");
    }
    $stmt->close();

    // Check if the entered payment amount matches the investment amount
    if ($payment_amount != $investment_amount) {
        die("Error: Payment amount does not match the selected investment package amount.");
    }

    // Find the selected saving ID's balance
    $selected_balance = null;
    foreach ($savingIDs as $id_balance) {
        if ($id_balance['id'] == $selected_id) {
            $selected_balance = $id_balance['balance'];
            break;
        }
    }

    // Ensure user has sufficient balance
    if ($selected_balance < $payment_amount) {
        die("Error: Insufficient balance in selected saving ID.");
    }

    // Start transaction for atomicity
    $conn->begin_transaction();

    try {
        // Deduct payment_amount from user's balance
        $updateBalanceQuery = "UPDATE accbalance SET balance = balance - ? WHERE username = ? AND id = ?";
        $stmt = $conn->prepare($updateBalanceQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("dss", $payment_amount, $username, $selected_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        $stmt->close();

        // Insert into user_investments table
        $insertInvestmentQuery = "INSERT INTO user_investments (username, investment_id, amount, date_time)
                                 VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertInvestmentQuery);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sid", $username, $investment_id, $payment_amount);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to success page or display success message
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Transaction failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <script>
        function showDescriptionAndAmount() {
            var select = document.getElementById("investment_id");
            var description = select.options[select.selectedIndex].getAttribute('data-description');
            var amount = select.options[select.selectedIndex].getAttribute('data-amount');
            
            document.getElementById("description_display").value = description;
            document.getElementById("amount_display").value = "₱" +amount;
        }

        function updateBalanceDisplay() {
            var select = document.getElementById("id");
            var selectedOption = select.options[select.selectedIndex];
            var balance = selectedOption.getAttribute('data-balance');

            document.getElementById("balance_display").textContent = "₱" + balance;
        }
    </script>
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
    <div class="container">
        <h2>Choose an Investment Package</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <select id="investment_id" name="investment_id" onchange="showDescriptionAndAmount()" required>
                <option value="">Select an Investment Package</option>
                <?php foreach ($investments as $investment): ?>
                    <option value="<?php echo $investment['id']; ?>" 
                            data-description="<?php echo htmlspecialchars($investment['description']); ?>"
                            data-amount="<?php echo htmlspecialchars($investment['amount']); ?>">
                        <?php echo $investment['package_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="description_display">Package Description:</label>
            <textarea id="description_display" name="description_display" readonly></textarea>
            <br><br>
            <label for="amount_display">Amount:</label>
            <input type="text" id="amount_display" name="amount_display" readonly>
            <br><br>
            <label for="id">Select Your Saving ID Number:</label>
            <select id="id" name="id" onchange="updateBalanceDisplay()" required>
                <?php foreach ($savingIDs as $id_balance): ?>
                    <option value="<?php echo $id_balance['id']; ?>" 
                            data-balance="<?php echo htmlspecialchars($id_balance['balance']); ?>">
                        <?php echo $id_balance['id']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p>Your current balance: <span id="balance_display"><?php echo $selected_balance ?? ""; ?></span></p>
          
            <!-- Additional fields for payment method, etc., can be added here -->
            <label for="payment_amount">Enter Payment Amount:</label>
            <input type="number" id="payment_amount" name="payment_amount" step="0.01" required>
            <br><br>
            <button type="submit">Purchase</button>
        </form>
    </div>
    <script src="script.js"></script> <!-- Include your JavaScript file if needed -->
</body>
</html>
