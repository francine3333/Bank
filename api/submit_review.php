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


// Get the review, rating, and product ID from the form
$review = isset($_POST['review']) ? trim($_POST['review']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate the input
if (empty($review) || $rating < 1 || $rating > 5 || $productId <= 0) {
    die("Invalid review data.");
}

// Insert the review into the database
$stmt = $conn->prepare("INSERT INTO productreviews (product_id, username, review, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $productId, $_SESSION['username'], $review, $rating);

// Execute the query and check for success
if ($stmt->execute()) {
    // Redirect back to the product page after successful submission
    header("Location: shopproduct-details.php?id=" . $productId);  
    exit;
} else {
    die("Error submitting review: " . $stmt->error);
}
?>
