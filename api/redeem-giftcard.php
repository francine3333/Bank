<?php
session_start();

// Include database connection
require_once 'config.php'; // or your actual connection file

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

// Predefined gift card codes and values
$giftCards = [
    "X9G7WQ48TJ6PRM23" => 100,
    "P3Y5ZL89QR1TBN56" => 200,
    "D7X4FQ92MW1KYP38" => 300,
];

// Initialize variables
$successMessage = "";
$errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["giftcard_code"]);
    $savingstype = trim($_POST["savingstype"]);

    // Check if the gift card code is valid
    if (isset($giftCards[$code])) {
        $amount = $giftCards[$code];

        // Ensure the database connection is available
        if (isset($conn)) {
            // Check if the selected savings type exists for the user
            $query = "SELECT * FROM accbalance WHERE username = ? AND savingstype = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $savingstype);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update the balance for the selected savings type
                $updateBalanceQuery = "UPDATE accbalance SET balance = balance + ? WHERE username = ? AND savingstype = ?";
                $stmt = $conn->prepare($updateBalanceQuery);
                $stmt->bind_param("dss", $amount, $username, $savingstype);
                if ($stmt->execute()) {
                    $successMessage = "Gift card redeemed successfully! Amount added: ₱" . number_format($amount, 2);
                    unset($giftCards[$code]);
                } else {
                    $errorMessage = "Error updating balance. Please try again.";
                }
            } else {
                $errorMessage = "The selected savings type does not exist.";
            }
            $stmt->close();
        } else {
            $errorMessage = "Database connection failed.";
        }
    } else {
        $errorMessage = "Invalid or already redeemed gift card code.";
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem Gift Card</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<style>
body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #001f3f, #0074d9);
    color: #ffffff;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.container {
    background: #ffffff;
    color: #333333;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    max-width: 450px;
    padding: 30px;
    text-align: center;
}

h2 {
    color: #001f3f;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 20px;
}

.alert {
    font-size: 0.9rem;
    padding: 10px;
    margin: 10px 0;
    border-radius: 8px;
}

.alert-success {
    background-color: #2ecc71;
    color: #ffffff;
}

.alert-danger {
    background-color: #e74c3c;
    color: #ffffff;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

label {
    font-weight: bold;
    color: #001f3f;
    margin-bottom: 5px;
    display: block;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
}

button {
    background: linear-gradient(135deg, #0074d9, #001f3f);
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(135deg, #001f3f, #0074d9);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
    background: #555555;
    color: #ffffff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1rem;
    margin-left: 10px;
}

.btn-secondary:hover {
    background: #333333;
}

/* Add a decorative gradient bar inspired by gift cards */
.container::before {
    content: "";
    display: block;
    height: 5px;
    width: 100%;
    border-radius: 5px 5px 0 0;
    background: linear-gradient(90deg, #ff5733, #ffc300, #33ff57, #3380ff, #8333ff);
    margin-bottom: 20px;
}

</style>
<body>
    <div class="container">
        <h2>Redeem Gift Card</h2>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
    <div class="form-group">
        <label for="giftcard_code">Gift Card Code</label>
        <input type="text" name="giftcard_code" id="giftcard_code" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="savingstype">Choose Savings Type</label>
        <select name="savingstype" id="savingstype" class="form-control" required>
            <?php
            // Fetch available savings types
            $existingSavingsTypes = getExistingSavingsTypes($conn, $username);
            foreach ($existingSavingsTypes as $type) {
                echo "<option value='" . htmlspecialchars($type['savingstype']) . "'>" . htmlspecialchars($type['savingstype']) . "</option>";
            }
            ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Redeem</button>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</form>


    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</body>
</html> 