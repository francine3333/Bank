<?php
session_start();
$message = "";
$error = "";
// Include configuration file
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

// Handle deletion of all logs
if (isset($_POST['btnDeleteLogs'])) {
    $sql_delete_logs = "TRUNCATE TABLE tbllogs";
    if (mysqli_query($conn, $sql_delete_logs)) {
        $message .= "<strong><span style='color: green;'>You have deleted all logs successfully</span></strong>";
        date_default_timezone_set('Asia/Manila');
        // Insert log for log deletion
        $date = date("Y-m-d");
        $time = date("h:i:sa");
        $action = "Deleted all logs";
        $module = "Database Management";
        $insert_query = "INSERT INTO tbllogs (datelog, timelog, id, performedby, action, module) VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt_log = mysqli_prepare($conn, $insert_query)) {
            mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $_SESSION['username'], $_SESSION['username'], $action, $module);
            if (mysqli_stmt_execute($stmt_log)) {
                $message .= "<br><strong><span style='color: green;'>Log for deletion inserted successfully</span></strong>";
            } else {
                $error .= "<br><strong><span style='color: red;'>Error inserting deletion log: " . mysqli_error($conn) . "</span></strong>";
            }
        } else {
            $error .= "<br><strong><span style='color: red;'>Error preparing log statement: " . mysqli_error($conn) . "</span></strong>";
        }
    } else {
        $error .= "<strong><span style='color: red;'>Error deleting logs: " . mysqli_error($conn) . "</span></strong>";
    }
}

// Check if form is submitted for adding account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnAddAccount'])) {
    // Retrieve form inputs
    date_default_timezone_set('Asia/Manila');
    $newUsername = $_POST['newUsername'];
    $newpassword = $_POST['newemail'];
    $newPassword = $_POST['newPassword']; 
    $newUserType = $_POST['newUserType'];
    $newsavetype = $_POST['newsavetype'];
    $createdBy = $_SESSION['username'];
    $dateCreated = date("Y-m-d");

    // Check if username already exists
    $check_username_query = "SELECT username FROM users WHERE username = ?";
    if ($stmt_check_username = mysqli_prepare($conn, $check_username_query)) {
        mysqli_stmt_bind_param($stmt_check_username, "s", $newUsername);
        mysqli_stmt_execute($stmt_check_username);
        mysqli_stmt_store_result($stmt_check_username);

        if (mysqli_stmt_num_rows($stmt_check_username) > 0) {
            $error = "<strong><span style='color: red;'>Username already exists. Please choose a different username.</span></strong>";
        } else {
            // Username is unique, proceed with account creation then insert to database if no errors
            $sql_add_user = "INSERT INTO users (username, email, password, usertype, createdby, datecreated) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_add_user = mysqli_prepare($conn, $sql_add_user)) {
                mysqli_stmt_bind_param($stmt_add_user, "ssssss", $newUsername, $newpassword, $newPassword, $newUserType, $createdBy, $dateCreated);
                if (mysqli_stmt_execute($stmt_add_user)) {
                    $message = "<strong><span style='color: green;'>New account added successfully.</span></strong>";

                    // Prepare insert statement for account balance
                    $sql_add_account = "INSERT INTO accbalance (username, savingstype, balance) VALUES (?, ?, 0)";
                    if ($stmt_add_account = mysqli_prepare($conn, $sql_add_account)) {
                        mysqli_stmt_bind_param($stmt_add_account, "ss", $newUsername, $newsavetype);
                        if (mysqli_stmt_execute($stmt_add_account)) {
                            // Account balance insert successful
                            $message .= "<br><strong><span style='color: green;'>Account balance added successfully.</span></strong>";
                        } else {
                            $error .= "<br><strong><span style='color: red;'>Error adding account balance: " . mysqli_error($conn) . "</span></strong>";
                        }
                    } else {
                        $error .= "<br><strong><span style='color: red;'>Error preparing account balance statement: " . mysqli_error($conn) . "</span></strong>";
                    }

                    $date = date("Y-m-d");
                    $time = date("h:i:sa");
                    $action = "Added new account: " . $newUsername;
                    $module = "Accounts Management";
                    $insert_query = "INSERT INTO tbllogs (datelog, timelog, id, performedby, action, module) VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt_log = mysqli_prepare($conn, $insert_query)) {
                        mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $newUsername, $createdBy, $action, $module);
                        mysqli_stmt_execute($stmt_log);
                    } else {
                        $error .= "<br><strong><span style='color: red;'>Error preparing log statement: " . mysqli_error($conn) . "</span></strong>";
                    }
                } else {
                    $error = "<strong><span style='color: red;'>Error adding new account: " . mysqli_error($conn) . "</span></strong>";
                }
            } else {
                $error = "<strong><span style='color: red;'>Error preparing user insert statement: " . mysqli_error($conn) . "</span></strong>";
            }
        }
    } else {
        $error = "<strong><span style='color: red;'>Error checking username existence: " . mysqli_error($conn) . "</span></strong>";
    }

    mysqli_stmt_close($stmt_check_username);
}
// Handle deletion of user account
if (isset($_POST['btnsubmit'])) {
    $username = trim($_POST['txtusername']);
    date_default_timezone_set('Asia/Manila');
    // Prepare delete statement for users table
    $sql_delete_account1 = "DELETE FROM users WHERE username = ?";
    if ($stmt_delete1 = mysqli_prepare($conn, $sql_delete_account1)) {
        mysqli_stmt_bind_param($stmt_delete1, "s", $username);
        if (mysqli_stmt_execute($stmt_delete1)) {
            // Prepare delete statement for accbalance table
            $sql_delete_account2 = "DELETE FROM accbalance WHERE username = ?";
            if ($stmt_delete2 = mysqli_prepare($conn, $sql_delete_account2)) {
                mysqli_stmt_bind_param($stmt_delete2, "s", $username);
                if (mysqli_stmt_execute($stmt_delete2)) {
                    // Insert log for successful deletion
                    $date = date("Y-m-d");
                    $time = date("h:i:sa");
                    $module = "Accounts Management";
                    $action = "Delete";

                    // Prepare log insertion
                    $insert_log_query = "INSERT INTO tbllogs (datelog, timelog, id, performedby, action, module) VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt_log = mysqli_prepare($conn, $insert_log_query)) {
                        mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $username, $_SESSION['username'], $action, $module);
                        if (mysqli_stmt_execute($stmt_log)) {
                            $message = "<strong><span style='color: green;'>You have successfully deleted the account</span></strong>";
                        } else {
                            $message = "<strong><span style='color: red;'>Error inserting deletion log: " . mysqli_error($conn) . "</span></strong>";
                        }
                    } else {
                        $message = "<strong><span style='color: red;'>Error preparing log statement: " . mysqli_error($conn) . "</span></strong>";
                    }
                } else {
                    $message = "<strong><span style='color: red;'>Error deleting user from accbalance: " . mysqli_error($conn) . "</span></strong>";
                }
            } else {
                $message = "<strong><span style='color: red;'>Error preparing delete statement for accbalance: " . mysqli_error($conn) . "</span></strong>";
            }
        } else {
            $message = "<strong><span style='color: red;'>Error deleting user from users: " . mysqli_error($conn) . "</span></strong>";
        }
    } else {
        $message = "<strong><span style='color: red;'>Error preparing delete statement for users: " . mysqli_error($conn) . "</span></strong>";
    }
}
// Query to get the total number of users and administrators
$sql_count_users = "SELECT COUNT(*) AS total_users FROM users";
$sql_count_admins = "SELECT COUNT(*) AS total_admins FROM users WHERE usertype = 'Administrator'";

$result_users = mysqli_query($conn, $sql_count_users);
$result_admins = mysqli_query($conn, $sql_count_admins);

// Fetch the results
$total_users = mysqli_fetch_assoc($result_users)['total_users'];
$total_admins = mysqli_fetch_assoc($result_admins)['total_admins'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User management</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Raleway:wght@500&display=swap">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>	
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2 style="margin-top:8px;">
				<span id="logo" class="oi" data-glyph="flag"></span>
				Wealth Finance Management </h2>
				<div class="header-menu">
											<nav>
							<ul class="nav mx-auto">
                            <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>		
							<li class="nav-item"><a class="nav-link"  id="log" href="logout.php">Logout</a></li>
                            <li class="nav-item"><a class="nav-link" href="manage-user.php">Accounts Management</a></li>
							</ul>
							</nav>
							</div>
			</div>
		</div>
	</div>
</header>
    <div class="notification" id="notification"></div>
    <script>
        function displayNotification(message, type) {
            var notification = document.getElementById('notification');
            notification.style.display = 'block';
            notification.innerHTML = '<div class="notification-content">' +
                '<div class="notification-icon ' + type + '"></div>' +
                '<div class="notification-message">' + message + '</div>' +
                '</div>';
            setTimeout(function() {
                notification.style.display = 'none';
            }, 3000);
            event.preventDefault();
        }
        <?php
        if (!empty($message )) {
            echo "displayNotification('<b>" . addslashes($message ) . "</b>', 'success');";
        }
        if (!empty($error )) {
            echo "displayNotification('<b>" . addslashes($error ) . "</b>', 'error');";
        }
        ?>
    </script>
    <br>
    <div class="container">
    <div class="section-header">
        <h3>System Statistics Overview</h3>
    </div>
    <!-- Statistics Section -->
    <div class="statistics">
        <div>
            <strong>Total Users:</strong>
            <p><?php echo $total_users; ?></p>
        </div>
        <div>
            <strong>Total Administrators:</strong>
            <p><?php echo $total_admins; ?></p>
        </div>
    </div>

    <!-- 3D Pie Chart -->
    <div class="pie-chart-container">
        <canvas id="statsPieChart"></canvas>
    </div>
</div>


    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

        <strong>Search:</strong> <input type="text" name="txtsearch">
        <input type="submit"style="background-color: orange; color: #fff; font-weight: bold"  name="btnsearch" value="Search"><br>
        <button type="button" onclick="openLogsModal()" style="width: 180px; height: 30px; margin-right: 10px; background-color: orange; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight:bold">View Logs</button>
<!-- Button to open add account modal -->
<button type="button" onclick="openAddAccountModal()" style="width: 180px; height: 30px; background-color: orange; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px;">Add New Account</button>
<button type="button" onclick="openUpdateModal()" style="width: 180px; height: 30px; background-color: orange; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px;">Update Account</button>
    
    </form>
    <div class="modal" id="deleteformmodal">
    <div class="modal-content">
        <h2><strong>Are you sure you want to delete this account?</strong></h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"id="deleteForm" method="POST">
            <label for="deleteUsername" name="txtusername">Username:</label>
            <input type="text" id="deleteUsername" name="txtusername" readonly>
            <button type="submit" name="btnsubmit" value="Yes" onclick="confirmDeleteAccount()" style="font-weight: bold;background-color:green;">Yes</button>
            <button type="button" name="btncancel" onclick="closeDeleteModal();" style="font-weight: bold;background-color:red;">No</button>
        </form>
    </div>
</div>
<div class="modallogs" id="logsModal">
    <div class="modal-contentlogs">
        <h2><strong>Logs</strong></h2>
        <strong>Search:</strong> <input type="text" id="txtlogsearch">
        <button type="button" id="btnLogSearch" style="width: 120px; height: 30px; margin-right:5px;">Search Logs</button>
        <input type="file" id="fileInput" style="display: none;" accept=".xls">
        <label for="fileInput" id="fileInputLabel" style="cursor: pointer; width: 180px; height: 30px; font-weight:bold;color:black; background-color:lightblue; margin-left:20px;">Export As Excel</label>
        <div id="logsContent"></div> 
        <button type="button" onclick="closeLogsModal()" style="font-weight: bold;">Close</button>
        <button type="button" style="width: 120px; height: 50px;background-color:Red;color:white; margin-left:5px;" onclick="openDeleteAllLogsModal()">Delete All Logs</button>
        <div class="modal" id="deleteAllLogsModal">
            <div class="modal-content">
                <h2><strong>Are you sure you want to delete all logs?</strong></h2>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="deleteAllLogsForm" method="POST" onsubmit="return confirmDeleteLogs()">
                    <input type="submit" name="btnDeleteLogs" value="Yes" style="font-weight: bold;background-color:green;">
                    <button type="button" onclick="closeDeleteAllLogsModal();" style="font-weight: bold;background-color:red;">No</button>
                </form>
            </div>
        </div>
    </div>   
</div>
<!-- Add new account modal -->
<div style="margin-bottom: 500px;" class="modal" id="addAccountModal">
<div class="modal-content">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="newUsername">Username:</label>
            <input type="text" id="newUsername" name="newUsername" required><br>
            <label for="newemail">Email:</label>
            <input type="text" id="email" name="newemail" required><br>
            <div class="form-group">
                    <label> Password</label>
                    <input type="password" id="password" name="newPassword" class="form-control">
                    <input type="checkbox" id="showPassword" onclick="togglePasswordVisibility('newPassword')"> Show Password
                </div><br>
            <label for="newUserType">User Type:</label>
            <select id="newUserType" name="newUserType" required>
                <option value="ADMINISTRATOR">ADMINISTRATOR</option>
                <option value="USERS">USERS</option>    
                </select><br><br>
                <label for="newsavetype">User Savings Type:</label>  
            <select id="newsavetype" name="newsavetype" required>
                <option value="My savings">My savings</option>
                <option value="Debit Card">Debit Card</option>
                <option value="Money Market">Money Market</option>
                <option value="Health Savings">Health Savings</option>
                <option value="Retirement Savings">Retirement Savings</option>
            </select><br><br>
            <button type="submit" name="btnAddAccount" style="font-weight: bold; background-color: green;">Add Account</button>
            <button type="button" onclick="closeAddAccountModal();" style="font-weight: bold; background-color: red;">Cancel</button>
        </form>
    </div>
</div>
<?php
require_once "config.php";

$users = [];
$account = [];
$error = '';
$message = '';

// Check if the connection is open
if ($conn) {
    // Fetch usernames
    $sql_fetch_users = "SELECT username FROM users";
    if ($result = mysqli_query($conn, $sql_fetch_users)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row['username'];
        }
        mysqli_free_result($result);
    } else {
        $error .= "<br><strong><span style='color: red;'>Error fetching users: " . mysqli_error($conn) . "</span></strong>";
    }

    // Fetch user details based on selected username
    if (isset($_POST['selectedUsername'])) {
        $selectedUsername = $_POST['selectedUsername'];
        $sql = "SELECT * FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $selectedUsername);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $account = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            $error .= "<br><strong><span style='color: red;'>Error preparing select statement: " . mysqli_error($conn) . "</span></strong>";
        }
    }

    // Handle form submission for updating the account
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUpdateAccount'])) {
        $updateUsername = $_POST['selectedUsername'];
        $updatePassword = $_POST['updatePassword'];
        $updateUserType = $_POST['updateUserType'];

        // Prepare update statement
        $sql_update_user = "UPDATE users SET password = ?, usertype = ? WHERE username = ?";
        if ($stmt_update_user = mysqli_prepare($conn, $sql_update_user)) {
            mysqli_stmt_bind_param($stmt_update_user, "sss", $updatePassword, $updateUserType, $updateUsername);
            if (mysqli_stmt_execute($stmt_update_user)) {
                $message = "<strong><span style='color: green;'>Account updated successfully.</span></strong>";

                // Log the action
                $date = date("Y-m-d");
                $time = date("h:i:sa");
                $action = "Updated account: " . $updateUsername;
                $module = "Accounts Management";
                $insert_query = "INSERT INTO tbllogs (datelog, timelog, id, performedby, action, module) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_log = mysqli_prepare($conn, $insert_query)) {
                    mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $updateUsername, $_SESSION['username'], $action, $module);
                    mysqli_stmt_execute($stmt_log);
                    mysqli_stmt_close($stmt_log);
                } else {
                    $error .= "<br><strong><span style='color: red;'>Error preparing log statement: " . mysqli_error($conn) . "</span></strong>";
                }
            } else {
                $error = "<strong><span style='color: red;'>Error updating account: " . mysqli_error($conn) . "</span></strong>";
            }
            mysqli_stmt_close($stmt_update_user);
        } else {
            $error = "<strong><span style='color: red;'>Error preparing update statement: " . mysqli_error($conn) . "</span></strong>";
        }
    }
} else {
    $error = "<strong><span style='color: red;'>Database connection error: " . mysqli_connect_error() . "</span></strong>";
}

?>
<div class="modal" id="updateAccountModal">
    <div class="modal-content">
        <h2><strong>Update Account</strong></h2>
        <form id="updateAccountForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="selectedUsername">Select Username:</label>
            <select id="selectedUsername" name="selectedUsername" onchange="this.form.submit();" required>
                <option value="">Select a user</option>
                <?php
                foreach ($users as $username) {
                    $selected = isset($account['username']) && $account['username'] == $username ? 'selected' : '';
                    echo "<option value='$username' $selected>$username</option>";
                }
                ?>
            </select><br>
            <?php if (!empty($account)): ?>
                <input type="hidden" name="selectedUsername" value="<?php echo $account['username']; ?>" readonly>
                <div class="form-group">
                <label for="txtpassword">Current Password:</label>
                <input type="password" id="txtpassword" name="txtpassword" value="<?php echo $account['password']; ?>" readonly><br>
                <input type="checkbox" id="showCurrentPasswordCheckbox" onclick="togglePasswordVisibility('displaypassword')"> Show Password
                  </div>
                  <div class="form-group">
                <label for="updatePassword">New Password:</label>
                <input type="password" id="updatePassword" required name="updatePassword"><br>
                <input type="checkbox" id="showNewPasswordCheckbox" onclick="togglePasswordVisibility('new_password')"> Show Password
                 </div>
                <label for="txtusertype">Current User Type:</label>
                <input type="text" id="txtusertype" name="txtusertype" value="<?php echo $account['usertype']; ?>" readonly><br>

                <label for="updateUserType">New User Type:</label>
                <select id="updateUserType" name="updateUserType" required>
                    <option value="ADMINISTRATOR" <?php echo ($account['usertype'] == 'ADMINISTRATOR') ? 'selected' : ''; ?>>ADMINISTRATOR</option>
                    <option value="USER" <?php echo ($account['usertype'] == 'USER'||'user') ? 'selected' : ''; ?>>USER</option>
                </select><br>

                <button type="submit" name="btnUpdateAccount" style="font-weight: bold; background-color: green;">Update Account</button>
                <button type="button" onclick="closeUpdateAccountModal();" style="font-weight: bold; background-color: red;">Cancel</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
// Function to open the modal and store state
function openUpdateModal(username) {
    var modal = document.getElementById("updateAccountModal");
    modal.style.display = "block";
    localStorage.setItem("updateModalOpen", "true");
}

// Function to close the modal and store state
function closeUpdateAccountModal() {
    var modal = document.getElementById("updateAccountModal");
    modal.style.display = "none";
    localStorage.setItem("updateModalOpen", "false");
    window.location.href = "manage-user.php";
}

// Function to check and reopen modal if needed
window.onload = function() {
    var isOpen = localStorage.getItem("updateModalOpen");
    if (isOpen === "true") {
        var modal = document.getElementById("updateAccountModal");
        modal.style.display = "block";
    }
}
document.getElementById("showCurrentPasswordCheckbox").addEventListener("change", function() {
            var passwordField = document.getElementById("txtpassword");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
        document.getElementById("showNewPasswordCheckbox").addEventListener("change", function() {
            var passwordField = document.getElementById("updatePassword");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });
</script>

<?php
if (isset($message)) {
    echo $message;
}
if (isset($error)) {
    echo $error;
}
?>


<script>
    document.getElementById("btnLogSearch").addEventListener("click", fetchLogs);
    document.getElementById("fileInputLabel").addEventListener("click", handleFileDownload);
    function fetchLogs() {
        var searchText = document.getElementById("txtlogsearch").value;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetch_logs.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById('logsContent').innerHTML = xhr.responseText;
            }
        };
        xhr.send('txtlogsearch=' + searchText);
    }
    function handleFileDownload() {
    var logsContent = document.getElementById("logsContent").innerHTML;
    var logsTable = "<table>" + logsContent + "</table>";
    var blob = new Blob([logsTable], { type: 'application/vnd.ms-excel' });
    var link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = 'Reportlogs.xls'; 
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

    <script>
        function openDeleteModal(username) {
        var modal = document.getElementById("deleteformmodal");
        modal.style.display = "block";
        var deleteForm = document.getElementById("deleteForm");
        deleteForm.action = "manage-user.php";
        var usernameInput = document.getElementById("deleteUsername");
        usernameInput.value = username;
    }
    function closeDeleteModal() {
        var modal = document.getElementById("deleteformmodal");
        modal.style.display = "none";
    }

        window.onclick = function(event) {
            var modal = document.querySelector(".modal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function openDeleteAllLogsModal() {
            var modal = document.getElementById('deleteAllLogsModal');
            modal.style.display = 'block';
        }

        function closeDeleteAllLogsModal() {
            var modal = document.getElementById('deleteAllLogsModal');
            modal.style.display = 'none';
        }
        function toggleDropdown() {
        var dropdownContent = document.getElementById("dropdownContent");
        if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
        } else {
            dropdownContent.style.display = "block";
        }
    }
    function openLogsModal() {
            var modal = document.getElementById('logsModal');
            modal.style.display = 'block';
            fetchLogs();
        }

        function closeLogsModal() {
            var modal = document.getElementById('logsModal');
            modal.style.display = 'none';
        }
        function openAddAccountModal() {
        var modal = document.getElementById('addAccountModal');
        modal.style.display = 'block';
    }

    function closeAddAccountModal() {
        var modal = document.getElementById('addAccountModal');
        modal.style.display = 'none';
    }


        function fetchLogs() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_logs.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('logsContent').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
       
    </script>

 <?php
echo '<div class="main">';
if (!function_exists('buildTable')) {
    function buildTable($result) {
        if(mysqli_num_rows($result) > 0) {
            echo "<table>";
            echo "<tr>";
            echo "<th>Username</th> <th>Password</th> <th>Usertype</th> <th>User Savings Type</th> <th> Savings ID Number</th> <th>Balance</th> <th>Created by</th> <th>Date Created</th> <th>Action</th>";
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
                echo "<td><strong>" . $row['createdby'] . "</td></strong>";
                echo "<td><strong>" . $row['datecreated'] . "</td></strong>";
                echo "<td>";
                echo "<button onclick='openDeleteModal(\"" . $row['username'] . "\")' class='btndelete' style='width: 70px; height: 30px;'><strong>Delete</strong></button>";
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
?>

        <script>
            document.getElementById("showPassword").addEventListener("change", function() {
            var passwordField = document.getElementById("password");
            if (this.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        });

    </script>
        <!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('statsPieChart').getContext('2d');
    var statsPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Total Users', 'Total Administrators'],
            datasets: [{
                label: 'System Statistics',
                data: [<?php echo $total_users; ?>, <?php echo $total_admins; ?>],
                backgroundColor: ['#3498db', '#f39c12'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' users';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            },
            cutout: '50%', // Doughnut effect for a cleaner look
        }
    });
</script>
</body>

</html>
