<?php
// Start the session and include the database connection
session_start();
require_once "../config.php";

// Check login or auth token
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (isset($_COOKIE['auth_token']) && filter_var($_COOKIE['auth_token'], FILTER_SANITIZE_STRING)) {
        $_SESSION['username'] = $_COOKIE['auth_token'];
    } else {
        header("location: login.php");
        exit;
    }
}

$username = $_SESSION['username'];

// Fetch food orders for the logged-in user
$query = "SELECT * FROM foodorders WHERE username = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $username); // Bind the username parameter to the query
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$result = $stmt->get_result();

// Fetch user's email and usertype
$userQuery = "SELECT usertype, email FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($usertype, $email);
    $stmt->fetch();
    $stmt->close();

    if (empty($email) || empty($usertype)) {
        die("User details not found.");
    }
} else {
    die("Error preparing statement: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth Finance Orders</title>
    <link rel="icon" type="image/png" sizes="50x50" href="../logo.png">
    <link rel="stylesheet" href="foodstore.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="topnav">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
    <div class="hamburger-menu" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="menu">
        <li><a href="foodstore.php">Home</a></li>
        <?php if ($usertype === "ADMINISTRATOR"): ?>
        <li><a href="foodmenu.php">Manage Food Menu</a></li>
        <?php endif; ?>
        <li><a href="Vieworders.php">View Order History</a></li>
        <li><a href="../dashboard.php">Back</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div class="food-table-wrapper">
    <h1>My Orders</h1>
    <table class="food-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Food ID No. </th>
                <th>Price</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['food_item_id']);?></td>
                        <td><?php echo number_format($row['price'], 2); ?> ₱</td>
                        <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <a href="foodstore.php" class="button">Back to Menu</a> <!-- Link to menu page -->
</div>
<script>
function toggleMenu() {
    const menu = document.querySelector('.topnav ul');
    menu.classList.toggle('active');

    // Populate the menu dynamically when it becomes active
    if (menu.classList.contains('active')) {
        menu.innerHTML = `
            <li><a href="foodstore.php">Home</a></li>
            ${"<?php if ($usertype === 'ADMINISTRATOR'): ?>"}
            <li><a href="foodmenu.php">Manage Food Menu</a></li>
            ${"<?php endif; ?>"}
             <li><a href="Vieworders.php">View Order History</a></li>
            <li><a href="../dashboard.php">Back</a></li>
            <li><a href="logout.php">Logout</a></li>
        `;
    } else {
        // Clear the menu when inactive to avoid redundancy
        menu.innerHTML = '';
    }
}
</script>
</body>
</html>

<?php
$conn->close();
?>
