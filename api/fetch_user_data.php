<?php
include 'config.php'; // Include your database connection file

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';

    if (empty($username)) {
        echo json_encode(["success" => false, "message" => "Username is required."]);
        exit;
    }

    // Query to fetch user data
    $sql = "SELECT password, usertype FROM users WHERE username = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $password, $usertype);
            if (mysqli_stmt_fetch($stmt)) {
                $response = ["success" => true, "password" => $password, "usertype" => $usertype];
            } else {
                $response = ["success" => false, "message" => "User not found."];
            }
        } else {
            $response = ["success" => false, "message" => "Error executing query: " . mysqli_error($conn)];
        }
        mysqli_stmt_close($stmt);
    } else {
        $response = ["success" => false, "message" => "Error preparing query: " . mysqli_error($conn)];
    }

    echo json_encode($response);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
