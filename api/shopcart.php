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

// Fetch the user's email and usertype (optional for cart, but useful for user details)
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

// Fetch cart items from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Calculate total price
$totalPrice = 0;
foreach ($cart as $productId => $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Remove a product from the cart
if (isset($_GET['remove'])) {
    $productIdToRemove = $_GET['remove'];
    unset($_SESSION['cart'][$productIdToRemove]);
    header("Location: cart.php");
    exit();
}

// Update the quantity of a product
if (isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    }
    header("Location: cart.php");
    exit();
}
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Wealth Finance Store</title>
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
        <section class="py-5">
            <div class="container">
                <h2>Your Cart</h2>
                <?php if (empty($cart)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <form method="POST" action="cart.php">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $productId => $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <input type="number" name="quantity[<?php echo $productId; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="form-control" style="width: 80px;">
                                        </td>
                                        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td>
                                            <a href="cart.php?remove=<?php echo $productId; ?>" class="btn btn-danger btn-sm">Remove</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="update" class="btn btn-primary">Update Cart</button>
                    </form>

                    <div class="mt-4">
                        <h4>Total: ₱<?php echo number_format($totalPrice, 2); ?></h4>
                        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

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
