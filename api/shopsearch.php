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

// Fetch and sanitize the search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';  // Trim to remove leading/trailing spaces

// Prepare SQL query
$productQuery = "SELECT * FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($productQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Use wildcards for partial match, if the query is not empty
$searchTerm = $query ? "%" . $query . "%" : "%";  // Show all products if no query is provided
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Wealth Finance Store</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1>Wealth Finance Store</h1>
            <nav>
                <ul class="nav">
                    <li class="nav-item"><a href="shop.php" class="nav-link text-white">Home</a></li>
                    <li class="nav-item">
                        <form action="shopsearch.php" method="GET" class="form-inline">
                            <input class="form-control mr-2" type="text" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($query); ?>" required>
                            <button class="btn btn-outline-light" type="submit">Search</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="py-5">
            <div class="container">
                <h2 class="text-center">Search Results for: "<?php echo htmlspecialchars($query); ?>"</h2>
                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <div class="col-md-3">
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text">₱<?php echo number_format($product['price'], 2); ?></p>
                                        <a href="shopproduct-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center">No products found matching your search.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <p>&copy; 2024 Wealth Finance Management. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
