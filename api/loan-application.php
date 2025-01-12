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

// Fetch user's usertype from the users table
$userQuery = "SELECT usertype FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($usertype);
$stmt->fetch();
$stmt->close();

// Handle loan application form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_loan'])) {
    $loan_type = trim($_POST["loan_type"]);
    $amount = trim($_POST["amount"]);

    // Determine loan status based on usertype
    $status = ($usertype === "ADMINISTRATOR") ? "Approved" : "Pending";

    // Prepare an insert statement for the loan application
    $loanQuery = "INSERT INTO loans (username, loan_type, amount, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($loanQuery);
    $stmt->bind_param("ssds", $username, $loan_type, $amount, $status);
    if ($stmt->execute()) {
        $successMessage = "Loan application submitted successfully.";
    } else {
        $errorMessage = "Error submitting loan application: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing loan applications
$loanQuery = "SELECT * FROM loans";
if ($usertype !== "ADMINISTRATOR") {
    $loanQuery .= " WHERE username = ?";
}
$stmt = $conn->prepare($loanQuery);
if ($usertype !== "ADMINISTRATOR") {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$loanResult = $stmt->get_result();
$loans = [];
while ($row = $loanResult->fetch_assoc()) {
    $loans[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
</head>
<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 style="margin-top:8px;">
                    <span id="logo" class="oi" data-glyph="flag"></span>
                    Wealth Finance Management
                </h2>
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
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Account type: <?php echo htmlspecialchars($usertype); ?>. You are now logged in.</p>
        <h3>Loan Application</h3>
        <?php if (isset($successMessage)) { echo "<div class='alert alert-success'>$successMessage</div>"; } ?>
        <?php if (isset($errorMessage)) { echo "<div class='alert alert-danger'>$errorMessage</div>"; } ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="loan_type">Loan Type</label>
                    <select id="loan_type" name="loan_type" class="form-control" required>
                    <option value="Personal Loan">Personal Loan</option>
                    <option value="Home Loan">Home Loan</option>
                    <option value="Car Loan">Car Loan</option>
                    <option value="Student Loan">Student Loan</option>
                    <option value="Business Loan">Business Loan</option>
                    <option value="Mortgage Loan">Mortgage Loan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" name="amount" id="amount" class="form-control" required>
                </div>
                <button type="submit" name="apply_loan" class="btn btn-primary">Apply for Loan</button>
            </form>
            <h3>My Loan Applications</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Loan Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Application Date</th>
                    <?php if ($usertype === "ADMINISTRATOR"): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['loan_type']); ?></td>
                        <td><?php echo htmlspecialchars($loan['amount']); ?></td>
                        <td><?php echo htmlspecialchars($loan['status']); ?></td>
                        <td><?php echo htmlspecialchars($loan['application_date']); ?></td>
                        <?php if ($usertype === "ADMINISTRATOR"): ?>
                            <td>
                                <?php if ($loan['status'] === 'Pending'): ?>
                                    <a href="approve_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-success">Approve</a>
                                    <a href="reject_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-danger">Reject</a>
                                <?php else: ?>
                                    <span>---</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>