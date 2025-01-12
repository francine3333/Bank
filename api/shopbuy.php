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

$username = $_SESSION['username'];
$productId = isset($_GET['add']) ? (int)$_GET['add'] : 0;

// Fetch product details based on product ID
$productQuery = "SELECT id, name, price, image, category FROM products WHERE id = ?";
$stmt = $conn->prepare($productQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->bind_result($id, $name, $price, $image, $category);
$stmt->fetch();
$stmt->close();

if (!$name) {
    die("Product not found.");
}

// Fetch user's savings (bank account) information from accbalance table
$paymentQuery = "SELECT id, savingstype, balance FROM accbalance WHERE username = ? AND balance > 0";
$stmt = $conn->prepare($paymentQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($accbalance_id, $savingstype, $balance);
$paymentMethods = [];
while ($stmt->fetch()) {
    $paymentMethods[] = ['id' => $accbalance_id, 'savingstype' => $savingstype, 'balance' => $balance];
}
$stmt->close();

if (empty($paymentMethods)) {
    die("No available payment methods. Please add funds to your account.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $payment_method_id = $_POST['payment_method'];
    $payment_amount = $_POST['amount'];

    if (empty($full_name) || empty($contact) || empty($address) || empty($payment_method_id)) {
        die("All fields are required.");
    }

    // Check if the selected payment method has sufficient balance
    $balanceQuery = "SELECT balance FROM accbalance WHERE id = ? AND balance >= ?";
    $stmt = $conn->prepare($balanceQuery);
    $stmt->bind_param("ii", $payment_method_id, $payment_amount);
    $stmt->execute();
    $stmt->bind_result($current_balance);
    $stmt->fetch();
    $stmt->close();

    if ($current_balance < $payment_amount) {
        die("Insufficient balance for this purchase.");
    }

    // Deduct the amount from the selected payment method
    $new_balance = $current_balance - $payment_amount;
    $updateBalanceQuery = "UPDATE accbalance SET balance = ? WHERE id = ?";
    $stmt = $conn->prepare($updateBalanceQuery);
    $stmt->bind_param("ii", $new_balance, $payment_method_id);
    if (!$stmt->execute()) {
        die("Error updating balance: " . $stmt->error);
    }
    $stmt->close();

    // Save the order to the orders table
    $orderQuery = "INSERT INTO orders (username, product_id, full_name, contact, address, payment_method, payment_amount) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($orderQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sissssi", $username, $productId, $full_name, $contact, $address, $payment_method_id, $payment_amount);
    if ($stmt->execute()) {
        echo "Order placed successfully!";
    } else {
        die("Error placing the order: " . $stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy - <?php echo htmlspecialchars($name); ?> - Wealth Finance Store</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1>Wealth Finance Store</h1>
            <nav>
                <ul class="nav">
                <li class="nav-item"><a href="shop.php" class="nav-link text-white">Home</a></li>
                    <li class="nav-item"><a href="shop.php" class="nav-link text-white">Shop</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="py-5">
            <div class="container">
                <h2>Purchase: <?php echo htmlspecialchars($name); ?></h2>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="text" name="contact" id="contact" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea name="address" id="address" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Select Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?php echo $method['id']; ?>">
                                    <?php echo htmlspecialchars($method['savingstype']) . " - Balance: ₱" . number_format($method['balance'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount (₱)</label>
                        <input type="number" name="amount" id="amount" class="form-control" value="<?php echo number_format($price, 2); ?>" required readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p>&copy; 2024 Wealth Finance Management. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
