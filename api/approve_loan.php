<?php
session_start();
require_once "config.php";

// Redirect to login page if user is not logged in or not an administrator
if (!isset($_SESSION["loggedin"])  && $_SESSION["usertype"] !== "ADMINISTRATOR") {
    header("location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $loan_id = $_GET['id'];

    // Update loan status to "Approved"
    $updateQuery = "UPDATE loans SET status = 'Approved' WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $loan_id);
    if ($stmt->execute()) {
        header("location:loan-application.php"); // Redirect back to loan application
        exit;
    } else {
        echo "Error approving loan: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
