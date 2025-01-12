<?php
session_start();
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


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demowealthdatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['username'];

// Fetch savingstype and id from accbalance table
$savingstype_options = [];
$id_options = [];

$stmt = $conn->prepare("SELECT DISTINCT savingstype FROM accbalance WHERE username = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $savingstype_options[] = $row['savingstype'];
}

$stmt->close();

$stmt = $conn->prepare("SELECT DISTINCT id, balance FROM accbalance WHERE username = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$savingIDs = [];
while ($row = $result->fetch_assoc()) {
    $savingIDs[] = $row;
}
$stmt->close();

// Querying portfolio
$sql = "SELECT asset, amount FROM portfolio WHERE username=?";
$portfolio_stmt = $conn->prepare($sql);
$portfolio_stmt->bind_param("s", $user_id);
$portfolio_stmt->execute();
$portfolio = $portfolio_stmt->get_result();
$portfolio_stmt->close();

// Initialize $selected_id and $selected_balance
$selected_id = "";
$selected_balance = "";

if (!empty($savingIDs)) {
    $selected_id = $savingIDs[0]['id']; // Default to the first ID
    $selected_balance = $savingIDs[0]['balance']; // Default to the first balance
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset = $_POST['asset'];
    $amount = $_POST['amount'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $total = $_POST['total'];
}
$sql = "SELECT * FROM transactions WHERE username=?";
$transaction_stmt = $conn->prepare($sql);
$transaction_stmt->bind_param("s", $user_id);
$transaction_stmt->execute();
$transaction = $transaction_stmt->get_result();
$transaction_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        async function fetchCryptoAssets() {
            try {
                const response = await fetch('https://api.coingecko.com/api/v3/coins/list?include_platform=false');
                if (!response.ok) {
                    throw new Error('Failed to fetch assets');
                }
                const data = await response.json();
                return data.slice(0, 20); // Limit results to 20 assets
            } catch (error) {
                console.error(error);
                return [];
            }
        }

        async function populateAssetOptions() {
            const assetSelect = document.getElementById('asset');
            const assets = await fetchCryptoAssets();

            assets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.id;
                option.textContent = asset.symbol.toUpperCase();
                assetSelect.appendChild(option);
            });
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await populateAssetOptions(); // Populate asset options on page load
            updateBalanceDisplay(); // Initial balance update on page load

            // Event listeners for change in savingstype and id selects
            document.getElementById('id').addEventListener('change', function() {
                updateBalanceDisplay();
            });
        });

        function calculateTotal() {
            var amount = parseFloat(document.getElementById('amount').value);
            var price = parseFloat(document.getElementById('price').value);
            var total = amount * price;

            // Update the total field with the calculated total
            document.getElementById('total').value = total.toFixed(2);
        }

        function updateBalanceDisplay() {
            var select = document.getElementById("id");
            var selectedOption = select.options[select.selectedIndex];
            var balance = selectedOption.getAttribute('data-balance');

            document.getElementById("balance_display").textContent = "₱" + balance;
        }
    </script>
</head>
<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 style="margin-top:8px;">Wealth Finance Management</h2>
                <div class="header-menu">
                    <nav>
                        <ul class="nav mx-auto">
                            <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link" id="log" href="logout.php">Logout</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<section>
    <h3>Trade Digital Assets</h3>
    <form action="cryptrade.php" method="post" id="tradeForm">
        <label for="asset">Asset:</label>
        <select id="asset" name="asset" required>
            <!-- Options will be added dynamically -->
        </select><br><br>
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required oninput="calculateTotal()"><br><br>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required oninput="calculateTotal()"><br><br>
        <label for="id">Select Your Saving ID Number:</label>
        <select id="id" name="id" onchange="updateBalanceDisplay()" required>
            <?php foreach ($savingIDs as $id_balance): ?>
                <option value="<?php echo $id_balance['id']; ?>" 
                        data-balance="<?php echo htmlspecialchars($id_balance['balance']); ?>"
                        <?php if ($id_balance['id'] == $selected_id) echo 'selected'; ?>>
                    <?php echo $id_balance['id']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p>Your current balance: <span id="balance_display"><?php echo "₱" . htmlspecialchars($selected_balance); ?></span></p>

        <label for="type">Type:</label>
        <select id="type" name="type" required>
            <option value="buy">Buy</option>
            <option value="sell">Sell</option>
        </select><br><br>
        <label for="total">Total Price:</label>
        <input type="text" id="total" name="total" readonly><br><br>

        <input type="submit" value="Submit">
    </form>

    <section>
        <h3>Your Portfolio</h3>
        <table>
            <tr>
                <th>Asset</th>
                <th>Amount</th>
            </tr>
            <?php while ($row = $portfolio->fetch_assoc()): ?>
                <tr>
                    <td class="asset"><?php echo htmlspecialchars($row['asset']); ?></td>
                    <td class="amount"><?php echo htmlspecialchars($row['amount']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
</section>
<br>
<section>
        <h3>Transaction Of Crypto</h3>
        <table>
            <tr>
                <th>Asset</th>
                <th>Amount</th>
                <th>Price</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            <?php while ($row = $transaction->fetch_assoc()): ?>
                <tr>
                    <td class="asset"><?php echo htmlspecialchars($row['asset']); ?></td>
                    <td class="amount"><?php echo htmlspecialchars($row['amount']); ?></td>
                    <td class="price"><?php echo htmlspecialchars($row['price']); ?></td>
                    <td class="type"><?php echo htmlspecialchars($row['type']); ?></td>
                    <td class="date"><?php echo htmlspecialchars($row['date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
    <br>
    <br>
</body>
</html>
