<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "pass";
$dbname = "demowealthdatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$username = $password = "";
$signup_err = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $signup_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
        
        // Check if username already exists
        $stmt_check_username = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $stmt_check_username->store_result();
        
        if ($stmt_check_username->num_rows > 0) {
            $signup_err = "Username already taken.";
        }
        $stmt_check_username->close();
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $signup_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 4) {
        $signup_err = "Password must have at least 4 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, proceed with signup
    if (empty($signup_err)) {
        $usertype = "user"; // Default user type
        $created_by = "admin"; // Default creator 
        $dateCreated = date("Y-m-d"); // Date created
        
        // Prepare and bind
        $stmt_insert_user = $conn->prepare("INSERT INTO users (username, password, usertype, createdby, datecreated) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert_user->bind_param("sssss", $username, $password, $usertype, $created_by, $dateCreated);

        if ($stmt_insert_user->execute()) {
            // Insert successful, proceed with account balance insertion
            $stmt_insert_account = $conn->prepare("INSERT INTO accbalance (username, savingstype, balance) VALUES (?, ?, 0)");
            $newsavetype = "My Savings"; // Adjust as needed
            $stmt_insert_account->bind_param("ss", $username, $newsavetype);

            if ($stmt_insert_account->execute()) {
                // Account balance insert successful
                $message = "Signup successful. You can now login.";
                
                // Log the action
                $date = date("Y-m-d");
                $time = date("h:i:sa");
                $action = "Added new account: " . $username;
                $module = "Accounts Management";
                $stmt_insert_log = $conn->prepare("INSERT INTO tbllogs (datelog, timelog, id, performedby, action, module) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert_log->bind_param("ssssss", $date, $time, $username, $created_by, $action, $module);
                $stmt_insert_log->execute();
                $stmt_insert_log->close();
            } else {
                $signup_err = "Error adding account balance: " . $conn->error;
            }
            
            $stmt_insert_account->close();
        } else {
            $signup_err = "Error adding new user: " . $conn->error;
        }

        $stmt_insert_user->close();
    }
    
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/png" sizes="32x32" href="banklogo.ico">
</head>
<body>
    <div class="login-container">
        <h2>Sign Up</h2>
        <?php 
        if (!empty($signup_err)) {
            echo '<div class="error">' . $signup_err . '</div>';
        } elseif (!empty($message)) {
            echo '<div class="success">' . $message . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="signup-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <input type="checkbox" id="showPassword" onclick="togglePasswordVisibility()"> Show Password
            <input type="submit" value="Sign Up" name="btnsignup" class="btnsignup">
        </form>
        <div class="php-errors"></div>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <script>
        document.querySelector('.btnsignup').addEventListener('click', function(event) {
            var btn = event.target;
            var form = document.getElementById('signup-form');
            var phpErrors = form.querySelector('.php-errors');
            
            if (form.checkValidity()) {
                btn.value = "Please wait...";
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.7';
                
                setTimeout(function() {
                    btn.value = "Sign Up";
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                    
                    form.submit();
                }, 2000); // Adjust timeout as needed
            } else {
                event.preventDefault();
                
                phpErrors.innerHTML = "";
                
                var errorMessage = document.createElement('span');
                errorMessage.style.color = 'red';
                errorMessage.textContent = "Please fill up the form completely.";
                phpErrors.appendChild(errorMessage);
            }
        });
        document.getElementById("showPassword").addEventListener("change", function() {
            var passwordField = document.getElementById("password");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
    </script>
</body>
</html>
