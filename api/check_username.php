<?php
require_once "config.php";

if (isset($_GET['username'])) {
    $receiverUsername = $_GET['username'];

    // Query to fetch distinct ID options for the receiver
    $queryReceiverIds = "SELECT DISTINCT id FROM accbalance WHERE username = ?";
    $stmtReceiverIds = $conn->prepare($queryReceiverIds);
    $stmtReceiverIds->bind_param("s", $receiverUsername);
    $stmtReceiverIds->execute();
    $stmtReceiverIds->bind_result($receiverId);

    $options = '';
    while ($stmtReceiverIds->fetch()) {
        $options .= '<option value="' . htmlspecialchars($receiverId) . '">' . htmlspecialchars($receiverId) . '</option>';
    }
    $stmtReceiverIds->close();

    echo $options;
}
?>
