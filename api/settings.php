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


$username = $_SESSION["username"];

// Query to fetch user's savings information
$savingsQuery = "SELECT id, savingstype, balance
                FROM accbalance
                JOIN users ON accbalance.username = users.username
                WHERE users.username = ?";

$stmt = $conn->prepare($savingsQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$result = $stmt->get_result();

// Initialize variables
$savings = [];
if ($result->num_rows > 0) {
    $savings = $result->fetch_assoc();
} else {
    die("No savings found for the logged-in user.");
}
$stmt->close();

// Query to fetch user's usertype
$userQuery = "SELECT usertype FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($usertype);
$stmt->fetch();
$stmt->close();

// Check if usertype is retrieved
if (!isset($usertype)) {
    die("Usertype not found for the logged-in user.");
}
// Query to fetch user's current password
$current_password_query = "SELECT password FROM users WHERE username = ?";
$stmt_current_password = mysqli_prepare($conn, $current_password_query);
mysqli_stmt_bind_param($stmt_current_password, "s", $username);
mysqli_stmt_execute($stmt_current_password);
mysqli_stmt_bind_result($stmt_current_password, $current_password);
mysqli_stmt_fetch($stmt_current_password);
mysqli_stmt_close($stmt_current_password);

// Check if current password is retrieved
if (!isset($current_password)) {
    die("Current password not found for the logged-in user.");
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnchangepassword'])) {
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate input
    if (empty($new_password) || empty($confirm_password)) {
        $errors[] = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        // Update password in the database
        $update_query = "UPDATE users SET password = ? WHERE username = ?";
        $stmt_update = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt_update, "ss", $new_password, $username);

        if (mysqli_stmt_execute($stmt_update)) {
            // Password updated successfully
            $success = "Password updated successfully.";

            // Logging the password change
            $date = date("Y-m-d");
            $time = date("h:i:sa");
            $module = "Accounts Management";
            $action = "Change password";
            $id = $username; 
            $performedby = $username; 

            $sql_insert = "INSERT INTO tbllogs (datelog, timelog, action, module, id, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $date, $time, $action, $module, $id, $performedby);

            if (mysqli_stmt_execute($stmt_insert)) {
                // Log inserted successfully
            } else {
                $errors[] = "Error inserting log: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt_insert);
        } else {
            $errors[] = "Error updating password: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt_update);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<body>
<header>    
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 style="margin-top:8px;">
                        <span id="logo" class="oi" data-glyph="flag"></span>
                        Wealth Finance Management</h2>
                    <div class="header-menu">
                        <nav>
                            <ul class="nav mx-auto">
                                <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>        
                                <li class="nav-item"><a class="nav-link" id="log" href="logout.php">Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div id="mainrow" class="row">
        <div class="col-md-4">
            <div id="notifications">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
                <p><span class="oi" data-glyph="Messageopen" title="icon name" aria-hidden="true"></span> Account type: <?php echo htmlspecialchars($usertype . ', You are now logged in'); ?></p>
              <button id="notification" class="btn-notification">View Notification</button>
              <br>  <strong>Welcome to our Online Banking. </strong>
            </div>   
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Live Better</h4>
                    <img src="ad.gif" />
                    <br /><br />
                    <p>We have everything you'd ever need.</p>
                    <a href="#" onclick="triggerInactive()">Learn More</a>
                </div>
            </div>
        </div>
    <div class="col-md-8">
            <div id="filler">
                <div class="row">
                    <ul class="vertical">
                        <li><span class="oi" data-glyph="book"></span><a href="#"onclick="openchangepasswordmodal()">Change Password</a></li>
                        <li><span class="oi" data-glyph="book"></span><a href="contact.php">Contact Support</a></li>
                        <li><span class="oi" data-glyph="book"></span><a href="recentlogin.php">Login History</a></li>
                    </ul>
                </div>
            </div>
    <?php
 if ($usertype === "ADMINISTRATOR"): 
echo '<div class="main">';
if (!function_exists('buildTable')) {
    function buildTable($result) {
        if(mysqli_num_rows($result) > 0) {
            echo "<table>";
            echo "<tr>";
            echo "<th>Username</th> <th>Password</th> <th>Usertype</th> <th>User Savings Type</th> <th> Savings ID Number</th> <th>Balance</th> <th>User Status</th> <th>Created by</th> <th>Date Created</th> <th>Action</th>";
            echo "</tr>";
            echo "<br>";
            while ($row = mysqli_fetch_array($result)) {
                echo "<tr>";
                echo "<td><strong>" . $row['username'] . "</strong></td>";
                echo "<td><strong>" . $row['password'] . "</td></strong>";
                echo "<td><strong>" . $row['usertype'] . "</td></strong>";
                echo "<td><strong>" . $row['savingstype'] . "</td></strong>";
                echo "<td><strong>" . $row['id'] . "</td></strong>";
                echo "<td><strong>" . $row['balance'] . "</td></strong>";
                echo "<td><strong>" . $row['account_status'] . "</td></strong>";
                echo "<td><strong>" . $row['createdby'] . "</td></strong>";
                echo "<td><strong>" . $row['datecreated'] . "</td></strong>";
                echo "<td>";
                if ($row['account_status'] == 'active') {
                    echo "<button onclick='closeAccount(\"" . $row['username'] . "\")' class='btndelete' style='width: 65px; height: 50px;'><strong>Close Account</strong></button>";
                } else {
                    echo "<button onclick='activateAccount(\"" . $row['username'] . "\")' class='btnactivate' style='width: 65px; height: 50px;'><strong>Activate Account</strong></button>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No record/s found.";
        }
    }
}

if(isset($_POST['btnsearch'])) {
    $sql = "SELECT u.*, a.savingstype, a.id, a.balance FROM users u JOIN accbalance a ON u.username = a.username WHERE u.username LIKE ? OR u.usertype LIKE ? ORDER BY u.username";
    if($stmt = mysqli_prepare($conn, $sql)) {
        $searchvalue = '%' . $_POST['txtsearch'] . '%';
        mysqli_stmt_bind_param($stmt, "ss", $searchvalue, $searchvalue);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            buildTable($result);
        }
    } else {
        echo "Error on search";
    }
} else {
    $sql = "SELECT u.*, a.savingstype, a.id, a.balance FROM users u JOIN accbalance a ON u.username = a.username ORDER BY u.username";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt); 
            buildTable($result);
        }
    } else {
        echo "Error on accounts load";
    }
}
mysqli_close($conn);
echo '</div>';
endif;
?>

<script>
function closeAccount(username) {
    if (confirm('Are you sure you want to close this account?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'close_account.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
                location.reload();
            }
        }
        xhr.send('username=' + username);
    }
}
function activateAccount(username) {
    if (confirm('Are you sure you want to activate this account?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'activate_account.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
                location.reload();
            }
        }
        xhr.send('username=' + username);
    }
}
</script>

    <div class="modal" id="changepasswordmodal">
        <div class="modal-content">
            <h2><strong>Change password</strong></h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="displayusername">Username:</label>
                    <input type="text" id="displayusername" name="txtusername" value="<?php echo htmlspecialchars($username); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="displaypassword">Current Password:</label>
                    <input type="password" id="displaypassword" name="txtpassword" value="<?php echo htmlspecialchars($current_password); ?>" readonly>
                    <input type="checkbox" id="showCurrentPasswordCheckbox" onclick="togglePasswordVisibility('displaypassword')"> Show Password
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                    <input type="checkbox" id="showNewPasswordCheckbox" onclick="togglePasswordVisibility('new_password')"> Show Password
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    <input type="checkbox" id="showConfirmPasswordCheckbox" onclick="togglePasswordVisibility('confirm_password')"> Show Password
                </div>
                
                <button type="submit" name="btnchangepassword" style="font-weight: bold; background-color: green;">Save</button>
                <button type="button" onclick="closechangepasswordModal();" style="font-weight: bold; background-color: red;">Cancel</button>
            </form>
        </div>
    </div>
 
    <script>
        function openchangepasswordmodal() {
            var modal = document.getElementById('changepasswordmodal');
            modal.style.display = 'block';
        }

        function closechangepasswordModal() {
            var modal = document.getElementById('changepasswordmodal');
            modal.style.display = 'none';
        }

        document.getElementById("showCurrentPasswordCheckbox").addEventListener("change", function() {
            var passwordField = document.getElementById("displaypassword");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
        document.getElementById("showNewPasswordCheckbox").addEventListener("change", function() {
            var passwordField = document.getElementById("new_password");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
        document.getElementById("showConfirmPasswordCheckbox").addEventListener("change", function() {
            var passwordField = document.getElementById("confirm_password");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</body>
</html>
