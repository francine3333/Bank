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
$query = "SELECT * FROM foodmenu WHERE id";
$food_items_result = $conn->query($query);
if (!$food_items_result) {
    die("Error fetching food menu items: " . $conn->error);
}

// Add food item processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addFoodItem'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    // Check if food item with the same name already exists
    $checkQuery = "SELECT COUNT(*) FROM foodmenu WHERE name = ?";
    $stmt = $conn->prepare($checkQuery);
    if ($stmt) {
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($itemCount);
        $stmt->fetch();
        $stmt->close();

        if ($itemCount > 0) {
            // If the food item already exists, stop further execution without showing any notifications
            exit;
        }
    } else {
        // If there's an error checking the food item
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: 'Error checking for existing food item: " . $conn->error . "',
                    confirmButtonText: 'OK'
                });
              </script>";
        exit;
    }

    // Handle file upload
    $target_dir = "foodimage/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $imagePath = $target_file; // Correctly set the path of the uploaded file

        // Insert data into the database
        $insertQuery = "INSERT INTO foodmenu (name, category_id, price, description, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("ssdss", $name, $category, $price, $description, $imagePath);
            if ($stmt->execute()) {
                // Success: Food item added successfully
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Food item added successfully!',
                            confirmButtonText: 'OK'
                        });
                      </script>";
            } else {
                // Error: Failed to add food item
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Adding Item',
                            text: 'Error adding food item: " . $stmt->error . "',
                            confirmButtonText: 'Try Again'
                        });
                      </script>";
            }
            $stmt->close();
        } else {
            // Error preparing statement
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Preparing Statement',
                        text: 'Error preparing statement: " . $conn->error . "',
                        confirmButtonText: 'OK'
                    });
                  </script>";
        }
    } else {
        // Error uploading file
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'File Upload Error',
                    text: 'Error uploading file.',
                    confirmButtonText: 'Try Again'
                });
              </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Food Menu</title>
    <link rel="icon" type="image/png" sizes="50x50" href="../logo.png">
    <link rel="stylesheet" href="foodstore.css">
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

<div class="main-content">
    <div class="user-info">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>User Type:</strong> <?php echo htmlspecialchars($usertype); ?></p>
    </div>
    
    <h2>Food Menu</h2>
    <div class="food-table-wrapper">
    <table class="food-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Description</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($food_item = $food_items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($food_item['name']); ?></td>
                    <td><?php echo htmlspecialchars($food_item['category_id']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($food_item['price'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($food_item['description']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($food_item['image_url']); ?>" alt="Food Image"></td>
                    <td>
                        <button onclick="openModal()">Add</button> <br><br>
                        <button onclick="openModal()">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Adding Food Item -->
<div id="addFoodItemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add Food Item</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Food Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="category">Category ID:</label>
                <input type="text" id="category" name="category" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" name="addFoodItem">Add Item</button>
        </form>
    </div>
</div>

<script>
// Function to open the modal for adding food item
function openModal() {
    // Open the modal when the "Add" button is clicked
    document.getElementById("addFoodItemModal").style.display = "block";
    
    // Set the sessionStorage flag after the modal is opened
    sessionStorage.setItem('modalOpened', 'true');
}

// Function to close the modal
function closeModal() {
    // Hide the modal
    document.getElementById("addFoodItemModal").style.display = "none";
    
    // Remove the sessionStorage flag when modal is closed
    sessionStorage.removeItem('modalOpened');
}

// Close the modal if clicked outside
window.onclick = function(event) {
    if (event.target == document.getElementById("addFoodItemModal")) {
        closeModal();
    }
}

// Ensure modal is not opened automatically on page load
window.onload = function() {
    // Check if the modal was opened earlier during this session
    if (sessionStorage.getItem('modalOpened')) {
        // If the modal was opened earlier, we don't automatically show it again
        document.getElementById("addFoodItemModal").style.display = 'none';
    }
}


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
        // Clear the menu when inactive to avoid redundancy
        menu.innerHTML = '';
    }
}
</script>

</body>
</html>
