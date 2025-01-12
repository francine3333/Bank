<?php
session_start();

require_once "config.php";

// Redirect to login page if user is not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Constants for database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'demowealthdatabase');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection class
class Database {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $types = str_repeat('s', count($params)); // All parameters as strings
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    public function fetchAll($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as associative arrays
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// ShopManager class for user and product-related functionalities
class ShopManager {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getUserDetails($username) {
        $stmt = $this->db->query("SELECT username, usertype FROM users WHERE username = ?", [$username]);
        return $this->db->fetchAll($stmt)[0];
    }

    public function getProducts() {
        $stmt = $this->db->query("SELECT * FROM products");
        return $this->db->fetchAll($stmt); // Fetch all products
    }

    public function placeOrder($username, $productId, $quantity) {
        // Check if an order already exists for the same product and username
        $stmt = $this->db->query("SELECT * FROM orders WHERE username = ? AND product_id = ?", [$username, $productId]);
        if ($stmt->get_result()->num_rows > 0) {
            return "You have already placed an order for this product.";
        }
    
        // Insert the new order
        $stmt = $this->db->query("INSERT INTO orders (username, product_id, quantity) VALUES (?, ?, ?)", [$username, $productId, $quantity]);
        return $stmt;
    }    

    public function getOrders() {
        $stmt = $this->db->query("SELECT * FROM orders");
        return $this->db->fetchAll($stmt);  // Fetch all orders
    }
}

// Initialize database and manager
$db = new Database();
$shopManager = new ShopManager($db);

// Fetch user details from the database using the logged-in user's username
$username = $_SESSION['username'];  // Use 'username' consistently
$userDetails = $shopManager->getUserDetails($username);

// Check if user was found and fetch user type
if ($userDetails) {
    $userType = $userDetails['usertype'];
} else {
    $userType = 'USERS'; // Default to user if not found
}

// Handle order placement
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['place_order']) && $userType === 'USERS') {
    $productId = (int) trim($_POST['product_id']);
    $quantity = (int) trim($_POST['quantity']);
    $message = $shopManager->placeOrder($username, $productId, $quantity) ? "Order placed successfully!" : "Error placing order.";
}

// Fetch available products for the order form
$products = $shopManager->getProducts();
$orders = $shopManager->getOrders();
$db->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="shop.css">
</head>
<body>
<button class="sidebar-toggle-btn">☰</button>
<div class="sidebar">
    <h2><?php echo $userType === 'ADMINISTRATOR' ? 'Admin Dashboard' : 'User Dashboard'; ?></h2>
    <?php if ($userType === 'ADMINISTRATOR'): ?>
        <a href="shop.php">Dashboard</a>
        <a href="manageproducts.php">Manage Products</a>
        <a href="order.php">Manage Orders</a>
        <a href="#">Customers</a>
    <?php else: ?>
        <a href="viewproducts.php">View Products</a>
        <a href="order.php">My Orders</a>
    <?php endif; ?>
    <a href="dashboard.php">back</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <div class="header">
        <h1>Welcome to Our Online Shop</h1>
        <p>User: <?php echo htmlspecialchars($username); ?></p>
    </div>

    <?php if ($userType === 'USERS'): ?>
        <div class="form-container">
            <h2>Place an Order</h2>
            <?php if (!empty($message)) echo "<p>$message</p>"; ?>
            <form action="orders.php" method="post">
                <label for="product_id">Product:</label>
                <select id="product_id" name="product_id" required>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required><br><br>
                <input type="submit" name="place_order" value="Place Order">
            </form>
        </div>
    <?php endif; ?>

    <?php if ($userType === 'ADMINISTRATOR'): ?>
        <div class="order-list">
            <h2>All Orders</h2>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <p>Username: <?php echo htmlspecialchars($order['username']); ?></p>
                    <p>Product ID: <?php echo htmlspecialchars($order['product_id']); ?></p>
                    <p>Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.querySelector('.sidebar-toggle-btn').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
</script>
</body>
</html>
