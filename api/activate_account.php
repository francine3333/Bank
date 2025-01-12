<?php
include 'config.php'; // configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    $sql = "UPDATE users SET account_status = 'active' WHERE username = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            echo "Account activated successfully.";
        } else {
            echo "Error activating account.";
        }
    } else {
        echo "Error preparing statement.";
    }

    mysqli_close($conn);
}
?>
