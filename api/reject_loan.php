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


if (isset($_GET['id'])) {
    $loan_id = $_GET['id'];

    // Update loan status to "Rejected"
    $updateQuery = "UPDATE loans SET status = 'Rejected' WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $loan_id);
    if ($stmt->execute()) {
        header("location: loan-application.php"); // Redirect back to dashboard
        exit;
    } else {
        echo "Error rejecting loan: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
