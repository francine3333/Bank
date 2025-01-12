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


// Check if the form was submitted and required parameters are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'], $_POST['order_status'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Validate the order status
    $valid_statuses = ['pending', 'completed', 'cancelled'];
    if (!in_array($order_status, $valid_statuses)) {
        echo "Invalid order status.";
        exit;
    }

    // Update the order status in the database
    $updateQuery = "UPDATE orders SET order_status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameters and execute the query
    $stmt->bind_param("si", $order_status, $order_id);
    
    if ($stmt->execute()) {
        // Redirect back to the orders page with a success message
        header("location: shopordermanage.php?status=success");
    } else {
        // Handle query failure
        echo "Error updating order status: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
