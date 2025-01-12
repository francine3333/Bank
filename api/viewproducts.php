<?php
session_start();
require_once "config.php";

class ViewProducts {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getProducts() {
        $stmt = $this->db->query("SELECT * FROM products");
        return $this->db->fetchAll($stmt);
    }
}

$viewProducts = new ViewProducts($db);
$products = $viewProducts->getProducts();
$db->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
</head>
<body>
    <h1>Products</h1>
    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="150">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <a href="cart.php?product_id=<?php echo $product['id']; ?>">Add to Cart</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
