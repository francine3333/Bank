<?php
require_once 'config.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION["username"])) {
    echo json_encode([]);
    exit;
}
$username = $_SESSION["username"];

$query = "SELECT * FROM notifications WHERE username = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($notifications);
?>
