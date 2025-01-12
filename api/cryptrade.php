<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demowealthdatabase";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in user's details
$user_id = $_SESSION['username'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset = $_POST['asset'];
    $amount = floatval($_POST['amount']);
    $price = floatval($_POST['price']);
    $type = $_POST['type'];

    // Calculate total cost
    $total_cost = $amount * $price;

    // Fetch selected savings ID and balance
    $selected_id = $_POST['id'];
    $stmt = $conn->prepare("SELECT balance FROM accbalance WHERE username = ? AND id = ?");
    $stmt->bind_param("ss", $user_id, $selected_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $balance = $row['balance'];
        $stmt->close();

        // Perform transaction based on type (buy or sell)
        if ($type == 'buy') {
            if ($balance >= $total_cost) {
                // Deduct from balance
                $new_balance = $balance - $total_cost;
                $stmt = $conn->prepare("UPDATE accbalance SET balance = ? WHERE username = ? AND id = ?");
                $stmt->bind_param("dss", $new_balance, $user_id, $selected_id);
                $stmt->execute();
                $stmt->close();

                // Record transaction
                $stmt = $conn->prepare("INSERT INTO transactions (username, asset, amount, price, type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdds", $user_id, $asset, $amount, $price, $type);
                $stmt->execute();
                $stmt->close();

                // Update portfolio
                $stmt = $conn->prepare("INSERT INTO portfolio (username, asset, amount) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = amount + ?");
                $stmt->bind_param("ssdd", $user_id, $asset, $amount, $amount);
                $stmt->execute();
                $stmt->close();

                // Redirect after successful transaction
                echo "<script>
                    alert('Buy transaction successful!');
                    window.location.href = 'crypto.php';
                </script>";
            } else {
                echo "Insufficient balance.";
            }
        } elseif ($type == 'sell') {
            // Check if user owns enough of the asset
            $stmt = $conn->prepare("SELECT amount FROM portfolio WHERE username = ? AND asset = ?");
            $stmt->bind_param("ss", $user_id, $asset);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $portfolio_amount = $row['amount'];
                $stmt->close();

                if ($portfolio_amount >= $amount) {
                    // Add to balance
                    $new_balance = $balance + $total_cost;
                    $stmt = $conn->prepare("UPDATE accbalance SET balance = ? WHERE username = ? AND id = ?");
                    $stmt->bind_param("dss", $new_balance, $user_id, $selected_id);
                    $stmt->execute();
                    $stmt->close();

                    // Record transaction
                    $stmt = $conn->prepare("INSERT INTO transactions (username, asset, amount, price, type) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssdds", $user_id, $asset, $amount, $price, $type);
                    $stmt->execute();
                    $stmt->close();

                    // Update portfolio
                    $stmt = $conn->prepare("UPDATE portfolio SET amount = amount - ? WHERE username = ? AND asset = ?");
                    $stmt->bind_param("dss", $amount, $user_id, $asset);
                    $stmt->execute();
                    $stmt->close();

                    // Redirect after successful transaction
                    echo "<script>
                        alert('Sell transaction successful!');
                        window.location.href = 'crypto.php';
                    </script>";
                } else {
                    echo "Insufficient assets.";
                }
            } else {
                echo "You don't own this asset.";
            }
        } else {
            echo "Invalid transaction type.";
        }
    } else {
        echo "Invalid savings ID or no account details found.";
    }
}

$conn->close();
?>
