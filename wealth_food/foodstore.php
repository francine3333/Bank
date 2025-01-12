<?php
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

// Fetch food menu items with category names
$query = "SELECT * FROM foodmenu";
$food_items_result = $conn->query($query);
if (!$food_items_result) {
    die("Error fetching food menu items: " . $conn->error);
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

// Handle payment processing from modal submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['food_item_id'], $_POST['savingstype'])) {
    $food_item_id = $_POST['food_item_id'];
    $food_item_price = $_POST['price'];
    $savingstype_id = $_POST['savingstype'];

    // Fetch the selected savingstype details
    $savingsQuery = "SELECT id, balance FROM accbalance WHERE id = ? AND username = ?";
    $stmt = $conn->prepare($savingsQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("is", $savingstype_id, $username);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->bind_result($savingstype_id, $balance);
    $stmt->fetch();
    $stmt->close();

    // Check if the user has enough balance in the selected savings type
    if ($balance >= $food_item_price) {
        // Deduct the amount from the savings balance
        $new_balance = $balance - $food_item_price;
        $updateQuery = "UPDATE accbalance SET balance = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("di", $new_balance, $savingstype_id);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        // Process the order (store order details in the orders table)
        $orderQuery = "INSERT INTO foodorders (username, food_item_id, price, order_date) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($orderQuery);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sid", $username, $food_item_id, $food_item_price);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        // Redirect or show success message
        echo "<script>
                Swal.fire('Purchase Successful!', 'Your order has been placed.', 'success').then(() => {
                    window.location.href = 'orders.php';
                });
              </script>";
        exit;
    } else {
        echo "<script>
                Swal.fire('Insufficient Funds', 'You do not have enough balance in this savings type to make this purchase.', 'error');
              </script>";
    }
}

// Handle form submission for savings type creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["savingstype"]) && isset($_POST["balance"])) {
    // Validate input
    $savingstype = trim($_POST["savingstype"]);
    $balance = trim($_POST["balance"]);

    // Prepare an insert statement
    $insertQuery = "INSERT INTO accbalance (username, savingstype, balance) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssd", $username, $savingstype, $balance);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Redirect back to the dashboard
    header("location: dashboard.php");
    exit;
}

// Function to retrieve existing savings types from the database
function getExistingSavingsTypes($conn, $username) {
    $sql = "SELECT savingstype, balance, id FROM accbalance WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingSavingsTypes = [];
    while ($row = $result->fetch_assoc()) {
        $existingSavingsTypes[] = $row;
    }
    return $existingSavingsTypes;
}
// Fetch account balance for the logged-in user only (this part is handled above already)
$accbalanceQuery = "SELECT * FROM accbalance WHERE username = ?";
$stmt = $conn->prepare($accbalanceQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$accbalanceresult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth Finance Food Store</title>
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

<!-- Main Content Area -->
<div class="main-content">
    <!-- User Information Section -->
    <div class="user-info">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>User Type:</strong> <?php echo htmlspecialchars($usertype); ?></p>
    </div>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="searchform">
        <input type="text" name="txtsearch" placeholder="Search:">
        <button type="submit" name="btnsearch" class="btn btn-primary">Search</button>
    </div>
</form>

<?php
// Check if the search button is clicked
if (isset($_POST['btnsearch'])) {
    $searchvalue = trim($_POST['txtsearch']);
    
    if ($searchvalue === "") {
        // If the search field is empty, show all food items
        $resulthistoryQuery = "SELECT * FROM foodmenu ORDER BY id";
        $params = [];
        $types = '';
    } else {
        // Sanitize the input and use LIKE for partial match
        $searchvalue = '%' . $searchvalue . '%';
        $resulthistoryQuery = "SELECT * FROM foodmenu WHERE name LIKE ? ORDER BY id";
        $params = [$searchvalue];
        $types = 's'; // s stands for string type in prepared statement
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare($resulthistoryQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameters for search
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params); // Bind parameters dynamically
    }
    $stmt->execute();
} else {
    // Default query to fetch all food items when no search is done
    $resulthistoryQuery = "SELECT * FROM foodmenu ORDER BY id";
    $stmt = $conn->prepare($resulthistoryQuery);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->execute();
}

$result = $stmt->get_result();

// Check if there are any results
if ($result->num_rows > 0) {
    // Display the food menu items
    echo '<h2 class="section-title">Food Menu</h2>';
    echo '<div class="food-item-container">';

    while ($food_item = $result->fetch_assoc()) { ?>
        <div class="food-item-card">
            <img src="<?php echo htmlspecialchars($food_item['image_url']); ?>" alt="Food Image">
            <h3><?php echo htmlspecialchars($food_item['name']); ?></h3>
            <p class="category">
                <?php echo htmlspecialchars($food_item['category_id']) . ' (ID ' . htmlspecialchars($food_item['id']) . ')'; ?>
            </p>
            <p class="price">₱<?php echo htmlspecialchars(number_format($food_item['price'], 2)); ?></p>
            <p><?php echo htmlspecialchars($food_item['description']); ?></p>
            <!-- Buy button with data attributes -->
            <button type="button" class="buy-button" 
                    data-food-id="<?php echo htmlspecialchars($food_item['id']); ?>"
                    data-name="<?php echo htmlspecialchars($food_item['name']); ?>"
                    data-description="<?php echo htmlspecialchars($food_item['description']); ?>" 
                    data-price="<?php echo htmlspecialchars($food_item['price']); ?>">
                Buy
            </button>
        </div>
    <?php } 
    echo '</div>';
} else {
    // Display "Search not found" if no results
    echo "<h2>Search not found</h2>";
}

$stmt->close();
?>


<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <!-- Display food item details in the modal -->
        <div id="foodDetails">
            <h3 id="foodItemName"></h3>
            <p id="foodItemDescription"></p>
            <p><strong>Price:</strong> ₱<span id="foodItemPriceDisplay"></span></p>
        </div>

        <form id="paymentForm" method="POST" action="">
            <input type="hidden" name="food_item_id" id="food_item_id">
            <input type="hidden" name="price" id="food_item_price">
            <label for="savingstype">Choose a savings type:</label>
            <br> <br>
            <select name="savingstype" id="savingstype" required>
                <?php 
                $savingsTypes = getExistingSavingsTypes($conn, $username); 
                foreach ($savingsTypes as $type) {
                    echo "<option value='{$type['id']}'>{$type['savingstype']} - ₱" . number_format($type['balance'], 2) . "</option>";
                }
                ?>
            </select> <br><br>
            <button type="submit" class="submit-btn">Pay Now</button>
        </form>
    </div>
</div>

<script>
// Function to show the modal and display food item details
function showModal(foodItemId, name, description, price) {
    // Set a flag in sessionStorage to indicate the modal was opened
    sessionStorage.setItem('modalOpened', 'true');

    document.getElementById('foodItemName').innerText = name;
    document.getElementById('foodItemDescription').innerText = description;
    document.getElementById('foodItemPriceDisplay').innerText = price.toFixed(2);
    document.getElementById('food_item_id').value = foodItemId;
    document.getElementById('food_item_price').value = price;
    document.getElementById('paymentModal').style.display = 'block';
}

// Check if the modal flag is set in sessionStorage and show the modal only if triggered by a click
if (sessionStorage.getItem('modalOpened') === 'true') {
    document.getElementById('paymentModal').style.display = 'none'; // Do not show modal when reloading or navigating
    sessionStorage.removeItem('modalOpened'); // Remove flag after checking
} else {
    document.getElementById('paymentModal').style.display = 'none'; // Ensure modal is hidden on page load
}

// Close the modal function
function closeModal() {
    document.getElementById('paymentModal').style.display = 'none';
    sessionStorage.removeItem('modalOpened'); // Reset flag
}

// Handle food item purchase
const buyButtons = document.querySelectorAll('.buy-button');
buyButtons.forEach(button => {
    button.addEventListener('click', function () {
        const foodItemId = this.getAttribute('data-food-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        const price = parseFloat(this.getAttribute('data-price'));
        showModal(foodItemId, name, description, price);
    });
});
</script>
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
        menu.innerHTML = '';
    }
}
</script>



</body>
</html>
