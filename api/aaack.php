<?php
session_start();

if (isset($_POST['verify'])) {
    $user_code = $_POST['code'];
    $stored_code = $_SESSION['2fa_code'];

    if ($user_code == $stored_code) {
        echo "<script>alert('Verification successful. You are logged in.');
       ;</script>";
        // Perform login actions here
    } else {
        echo "<script>alert('Verification failed. Please try again.');
        document.location.href = 'aaack.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA Code</title>
</head>
<body>
    <h2>Verify 2FA Code</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="login-form">
        <label for="code">Enter 2FA Code:</label><br>
        <input type="text" id="code" name="code" required><br><br>
        <input type="submit" name="verify" value="Verify Code">
    </form>
</body>
</html>
