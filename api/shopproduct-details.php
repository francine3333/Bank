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
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details based on product ID
$productQuery = "SELECT id, name, description,price, image, category FROM products WHERE id = ?";
$stmt = $conn->prepare($productQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->bind_result($id, $name, $description, $price, $image, $category);
$stmt->fetch();
$stmt->close();

if (!$name) {
    die("Product not found.");
}
// Query to fetch user's savings information
$savingsQuery = "SELECT id, savingstype, balance
FROM accbalance
JOIN users ON accbalance.username = users.username
WHERE users.username = ? AND users.account_status = 'active'";

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

// Query to fetch user's email
$emailQuery = "SELECT email FROM users WHERE username = ?";
$stmt = $conn->prepare($emailQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Check if email is retrieved
if (!isset($email)) {
    die("Email not found for the logged-in user.");
}

// Check if usertype is retrieved
if (!isset($usertype)) {
    die("Usertype not found for the logged-in user.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - Wealth Finance Store</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* Add this to your style.css */
.stars {
    font-size: 1.5em;
    color: gold;
}

.stars ☆ {
    color: #ccc;
}

.stars ★ {
    color: gold;
}

.media-body p {
    margin-top: 0.5em;
}

</style>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1>Wealth Finance Store</h1>
            <nav>
                <ul class="nav">
                <li class="nav-item"><a href="shop.php" class="nav-link text-white">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($name); ?>">
                    </div>
                    <div class="col-md-6">
                        <h2><?php echo htmlspecialchars($name); ?></h2>
                        <p class="h4">₱<?php echo number_format($price, 2); ?></p>
                        <td><?php echo htmlspecialchars($description); ?></td>
                        <a href="shopbuy.php?add=<?php echo $id; ?>" class="btn btn-primary">Buy</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <section id="product-reviews" class="py-5">
    <div class="container">
        <h3 class="text-center">Product Reviews</h3>

        <!-- Display average rating -->
        <div class="text-center mb-4">
            <?php
            // Calculate the average rating for the product
            $avgRatingQuery = "SELECT AVG(rating) AS average_rating FROM productreviews WHERE product_id = ?";
            $stmt = $conn->prepare($avgRatingQuery);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $avgRating = $result->fetch_assoc();
            $averageRating = $avgRating['average_rating'] ? round($avgRating['average_rating'], 1) : 0;
            ?>
            <h4>Average Rating: <?php echo $averageRating; ?> / 5</h4>
            <div class="stars">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $averageRating ? '★' : '☆';
                }
                ?>
            </div>
        </div>

        <!-- Review submission form -->
        <div class="row mb-5">
            <div class="col-md-6 offset-md-3">
                <h4>Leave a Review</h4>
                <form action="submit_review.php" method="POST">
                    <div class="form-group">
                        <label for="review">Your Review</label>
                        <textarea class="form-control" name="review" id="review" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <select class="form-control" name="rating" id="rating" required>
                            <option value="1">1 Star</option>
                            <option value="2">2 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        </div>

        <!-- Display existing reviews -->
        <h4 class="text-center">Recent Reviews</h4>
        <ul class="list-unstyled">
            <?php
            if ($productId > 0) {
                // Fetch reviews for the specific product
                $reviewQuery = "SELECT review, rating, username FROM productreviews WHERE product_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($reviewQuery);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $reviews = $stmt->get_result();
                while ($review = $reviews->fetch_assoc()):
            ?>
                <li class="media mb-4">
                    <div class="media-body">
                        <h5 class="mt-0 mb-1"><?php echo htmlspecialchars($review['username']); ?></h5>
                        <div class="stars">
                            <?php
                            // Display stars for each review rating
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $review['rating'] ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <p><?php echo htmlspecialchars($review['review']); ?></p>
                    </div>
                </li>
            <?php endwhile; ?>
            <?php
            } else {
                echo "<p class='text-center'>No reviews available for this product.</p>";
            }
            ?>
        </ul>
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
</st>
