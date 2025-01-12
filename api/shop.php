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

$username = $_SESSION['username'];

// Query to fetch user's email and usertype
$userQuery = "SELECT usertype, email FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($usertype, $email);
$stmt->fetch();
$stmt->close();

if (!isset($email) || !isset($usertype)) {
    die("User details not found.");
}

// Fetch featured products from the database
function fetchFeaturedProducts($conn) {
    $query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
    $result = $conn->query($query);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Fetch categories from the database
function fetchCategories($conn) {
    $query = "SELECT DISTINCT category FROM products";
    $result = $conn->query($query);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

// Fetch account balance
$accbalanceQuery = "SELECT * FROM accbalance WHERE username = ?";
$stmt = $conn->prepare($accbalanceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$accbalanceresult = $stmt->get_result();
$stmt->close();

// Fetch the user's recent login device
$deviceQuery = "SELECT ip_address FROM login_attempts WHERE username = ? ORDER BY timestamp DESC LIMIT 1";
$stmt = $conn->prepare($deviceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($lastDevice);
$stmt->fetch();
$stmt->close();

// Retrieve data for categories and featured products
$featuredProducts = fetchFeaturedProducts($conn);
$categories = fetchCategories($conn);

// Fetch cart count (Assuming cart items are stored in the session)
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth Finance Store - eCommerce</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="shop.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> 
</head>
<body>
<header class="bg-dark text-white py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Wealth Finance Store</h1>
            <nav>
                <!-- Hamburger icon -->
                <div class="hamburger" onclick="toggleMenu()">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <!-- Navigation links -->
                <ul id="navLinks" class="nav">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Back</a></li>
                    <li class="nav-item"><a href="shop.php" class="nav-link text-white">Home</a></li>
                    <?php if ($usertype === "ADMINISTRATOR"): ?>
                        <li class="nav-item"><a href="shopmanageprod.php" class="nav-link text-white">Manage Products</a></li>
                        <li class="nav-item"><a href="shopordermanage.php" class="nav-link text-white">Manage Orders</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="shopcart.php" class="nav-link text-white">
                            <i class="fas fa-shopping-cart"></i>
                            Cart <span id="cart-count"><?php echo $cartCount; ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>


    <main>
        <section class="hero bg-light py-5">
            <div class="container text-center">
                <h2>Welcome to Wealth Finance Management's eCommerce Platform</h2>
                <p>Secure your financial future and shop for exclusive products.</p>
                <a href="#shop" class="btn btn-primary">Explore Now</a>
            </div>
        </section>

        <section id="categories" class="py-5">
            <div class="container">
                <h3 class="text-center">Shop by Categories</h3>
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($category['category']); ?></h5>
                                    <a href="shopcategory.php?category=<?php echo urlencode($category['category']); ?>" class="btn btn-secondary">View Products</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p>&copy; 2024 Wealth Finance Management. All rights reserved.</p>
        </div>
    </footer>
    <script>
        function toggleMenu() {
            var nav = document.getElementById("navLinks");
            nav.classList.toggle("active");
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
