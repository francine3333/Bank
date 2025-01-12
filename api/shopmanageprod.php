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

// Ensure the uploads directory exists
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Add a new product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $description = $_POST['description'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        // Validate file type and size (limit size to 2MB)
        $fileType = mime_content_type($imageTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($fileType, $allowedTypes) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, category, image, featured, description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssis", $name, $price, $category, $imagePath, $featured, $description);
                $stmt->execute();
                $stmt->close();
                $message = "Product added successfully!";
            } else {
                $message = "Error uploading the image.";
            }
        } else {
            $message = "Invalid image type or size exceeds 2MB.";
        }
    } else {
        $message = "Image upload failed.";
    }
}

// Edit a product
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $imagePath = $_POST['image']; // Keep the current image path if no new image uploaded

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        // Validate file type and size (limit size to 2MB)
        $fileType = mime_content_type($imageTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($fileType, $allowedTypes) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                // Image upload succeeded
            } else {
                $message = "Error uploading the image.";
            }
        } else {
            $message = "Invalid image type or size exceeds 2MB.";
        }
    }

    // Update the product in the database
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ?, image = ?, featured = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sdssisi", $name, $price, $category, $imagePath, $featured, $description, $id);
    $stmt->execute();
    $stmt->close();
    $message = "Product updated successfully!";
}

// Handle delete request
if (isset($_GET['delete'])) {
    $productId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
    header("Location: shopmanageprod.php"); // Redirect to refresh the page after delete
    exit;
}

// Fetch all products
$result = $conn->query("SELECT * FROM products");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Wealth Finance Store</title>
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="shop.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container">
            <h1>Manage Products</h1>
            <a href="shop.php" class="btn btn-secondary">Back</a>
        </div>
    </header>

    <main class="container my-5">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <section>
            <h2>Add New Product</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" name="category" id="category" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" name="image" id="image" class="form-control" required>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="featured" id="featured" class="form-check-input">
                    <label for="featured" class="form-check-label">Featured</label>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary mt-3">Add Product</button>
            </form>
        </section>

        <section class="mt-5">
            <h2>Manage Existing Products</h2>
            <div class="table-wrapper">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50">
                            </td>
                            <td><?php echo $product['featured'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" 
                                    data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-category="<?php echo htmlspecialchars($product['category']); ?>"
                                    data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>"
                                    data-featured="<?php echo $product['featured']; ?>"
                                >Edit</button>
                                <br> <br> 
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                >Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
                    </div>
        </section>
    </main>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <input type="hidden" name="image_path" id="editImagePath">
                        <div class="form-group">
                            <label for="editName">Name</label>
                            <input type="text" name="name" id="editName" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="editPrice">Price</label>
                            <input type="number" name="price" id="editPrice" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <input type="text" name="category" id="editCategory" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea name="description" id="editDescription" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editImage">Image</label>
                            <input type="file" name="image" id="editImage" class="form-control">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="featured" id="editFeatured" class="form-check-input">
                            <label for="editFeatured" class="form-check-label">Featured</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="GET" action="" class="d-inline">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="delete" id="deleteId">
                        <p id="deleteMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $('#editId').val(button.data('id'));
            $('#editName').val(button.data('name'));
            $('#editPrice').val(button.data('price'));
            $('#editCategory').val(button.data('category'));
            $('#editDescription').val(button.data('description'));
            $('#editImagePath').val(button.data('image'));
            $('#editFeatured').prop('checked', button.data('featured') === 1);
        });

        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $('#deleteId').val(button.data('id'));
            $('#deleteMessage').text("Are you sure you want to delete the product: " + button.data('name') + "?");
        });
    </script>
</body>
</html>
