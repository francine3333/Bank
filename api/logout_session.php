<?php
session_start();
require_once "config.php";

// Check if IP address is provided
if (isset($_POST['ip_address'])) {
    $ip_address = $_POST['ip_address'];

    // Query to delete sessions associated with the IP address
    $deleteQuery = "DELETE FROM login_attempts WHERE ip_address = ?";
    $stmt = $conn->prepare($deleteQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $ip_address);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->close();

    // Unset all of the session variables
    $_SESSION = array();
    session_destroy();    
    // Redirect to login page
    header("location:dashboard.php");
    exit;
} else {
    header("location:login.php");
    exit;
}
?>
