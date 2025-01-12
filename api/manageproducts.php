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

    public function fetchSingle($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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
        return $this->db->fetchSingle($stmt);
    }

    public function getProducts() {
        $stmt = $this->db->query("SELECT * FROM products");
        return $this->db->fetchAll($stmt);  // Fetch all products
    }

    public function addProduct($productData, $file) {
        // Check if the product ID already exists
        $stmt = $this->db->query("SELECT id FROM products WHERE id = ?", [$productData['id']]);
        if ($stmt->get_result()->num_rows > 0) {
            return "Product ID already exists. Please choose a different ID.";
        }

        // Handle image upload
        $imagePath = $this->uploadImage($file);
        if (!$imagePath) {
            return "Error uploading image.";
        }

        // Insert new product
        $stmt = $this->db->query(
            "INSERT INTO products (id, name, price, description, image) VALUES (?, ?, ?, ?, ?)",
            [
                $productData['id'],
                $productData['name'],
                $productData['price'],
                $productData['description'],
                $imagePath  // Store relative image path like 'uploads/image_name.jpg'
            ]
        );

        if ($stmt) {
            return "Product added successfully!";
        } else {
            return "Error adding product.";
        }
    }

    private function uploadImage($file) {
        $target_dir = "uploads/";

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Creates the directory with write permissions
        }

        // Generate a unique file name to avoid overwriting
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . uniqid('', true) . '.' . $imageFileType;

        // Validate image
        if (!getimagesize($file["tmp_name"])) {
            return false; // Not a valid image
        }
        if ($file["size"] > 5000000) {
            return false; // File is too large
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "jfif"])) {
            return false; // Invalid image format
        }

        // Move the uploaded file
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file; // Return the relative path
        } else {
            return false; // Error uploading image
        }
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

// Handle product addition
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_product']) && $userType === 'ADMINISTRATOR') {
    $productData = [
        'id' => (int) trim($_POST['product_id']),
        'name' => trim($_POST['product_name']),
        'price' => (float) trim($_POST['product_price']),
        'description' => trim($_POST['product_description'])
    ];
    $message = $shopManager->addProduct($productData, $_FILES['product_image']);
}

// Fetch products
$products = $shopManager->getProducts();
$db->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="shop.css">
</head>
<body>
<button class="sidebar-toggle-btn">☰</button>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="shop.php">Dashboard</a>
        <a href="manageproducts.php">Manage Products</a>
        <a href="order.php">Manage Orders</a>
        <a href="#">Customers</a>
        <a href="dashboard.php">back</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <div class="header">
            <h1>Manage Products</h1>
            <p>User: <?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="product-list">
            <h2>Product List</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
                <?php
                if (count($products) > 0) {
                    foreach ($products as $product) {
                        echo "<tr>
                            <td>{$product['id']}</td>
                            <td>{$product['name']}</td>
                            <td>{$product['price']}</td>
                            <td>{$product['description']}</td>
                            <td><img src='{$product['image']}' alt='{$product['name']}' style='width: 100px;'></td>
                            <td>
                              <a class='edit-btn' href='editproduct.php?id={$product['id']}' style='width: 40px;'>Edit</a> 
                              <a class='delete-btn' href='?delete={$product['id']}' style='width: 40px;'>Delete</a>

                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No products found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
    <script>
        document.querySelector('.sidebar-toggle-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
