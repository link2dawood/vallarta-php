<?php
// Test script for inventory management functionality
require_once('settings/db.php');
require_once('settings/inventory_functions.php');
session_start();

// Only allow admin users to run this test
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    die('Access denied. Admin login required.');
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Test - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Inventory Management Test</h1>

        <?php if (isset($_POST['test_action'])): ?>
            <div class="alert alert-info">
                <h4>Test Results:</h4>

                <?php
                switch ($_POST['test_action']) {
                    case 'parse_order':
                        $test_order = "OG Kush Premium (2), Sour Diesel (1), Test 1 (3)";
                        echo "<strong>Testing Order Parsing:</strong><br>";
                        echo "Input: " . htmlspecialchars($test_order) . "<br><br>";

                        $products = parseOrderProducts($test_order);
                        echo "<strong>Parsed Products:</strong><br>";
                        foreach ($products as $product) {
                            echo "- {$product['name']} (ID: {$product['id']}) - Qty: {$product['quantity']} - Stock: {$product['current_stock']}<br>";
                        }
                        break;

                    case 'test_stock_update':
                        $product_id = intval($_POST['product_id']);
                        $quantity_change = intval($_POST['quantity_change']);

                        echo "<strong>Testing Stock Update:</strong><br>";
                        echo "Product ID: $product_id<br>";
                        echo "Quantity Change: $quantity_change<br><br>";

                        $result = updateProductStock($product_id, $quantity_change, $user_id, "Manual test");

                        if ($result['success']) {
                            echo "<div class='text-success'>✓ Success!</div>";
                            echo "Product: {$result['product_name']}<br>";
                            echo "Old Stock: {$result['old_stock']}<br>";
                            echo "Change: {$result['change']}<br>";
                            echo "New Stock: {$result['new_stock']}<br>";
                        } else {
                            echo "<div class='text-danger'>✗ Error: {$result['error']}</div>";
                        }
                        break;

                    case 'simulate_order_edit':
                        $old_order = $_POST['old_order'];
                        $new_order = $_POST['new_order'];

                        echo "<strong>Testing Order Edit Simulation:</strong><br>";
                        echo "Old Order: " . htmlspecialchars($old_order) . "<br>";
                        echo "New Order: " . htmlspecialchars($new_order) . "<br><br>";

                        $changes = updateInventoryOnOrderChange(999, $old_order, $new_order, $user_id);

                        echo "<strong>Inventory Changes:</strong><br>";
                        if (empty($changes)) {
                            echo "No inventory changes required.<br>";
                        } else {
                            foreach ($changes as $change) {
                                echo "- Product ID {$change['product_id']}: Change {$change['change']}, New Stock: {$change['new_stock']}<br>";
                            }
                        }
                        break;

                    case 'check_low_stock':
                        $threshold = intval($_POST['threshold']);
                        echo "<strong>Low Stock Check (Threshold: $threshold):</strong><br>";

                        $low_stock = getLowStockAlerts($threshold);
                        if (empty($low_stock)) {
                            echo "No products with low stock.<br>";
                        } else {
                            echo "<table class='table table-sm'>";
                            echo "<tr><th>Product</th><th>Current Stock</th><th>Price</th></tr>";
                            foreach ($low_stock as $product) {
                                echo "<tr><td>{$product['title']}</td><td>{$product['unit']}</td><td>\${$product['price']}</td></tr>";
                            }
                            echo "</table>";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Test Order Parsing -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Order Parsing</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="test_action" value="parse_order">
                            <p>This will test parsing the order string format.</p>
                            <button type="submit" class="btn btn-primary">Run Test</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Test Stock Update -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Stock Update</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="test_action" value="test_stock_update">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product ID:</label>
                                <input type="number" class="form-control" id="product_id" name="product_id" value="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity_change" class="form-label">Quantity Change:</label>
                                <input type="number" class="form-control" id="quantity_change" name="quantity_change" value="5" required>
                                <small class="text-muted">Positive = add stock, Negative = remove stock</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Stock</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Test Order Edit -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Order Edit Simulation</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="test_action" value="simulate_order_edit">
                            <div class="mb-3">
                                <label for="old_order" class="form-label">Old Order:</label>
                                <input type="text" class="form-control" id="old_order" name="old_order" value="OG Kush Premium (2), Sour Diesel (1)" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_order" class="form-label">New Order:</label>
                                <input type="text" class="form-control" id="new_order" name="new_order" value="OG Kush Premium (1), Test 1 (2)" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simulate Edit</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Check Low Stock -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Check Low Stock</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="test_action" value="check_low_stock">
                            <div class="mb-3">
                                <label for="threshold" class="form-label">Stock Threshold:</label>
                                <input type="number" class="form-control" id="threshold" name="threshold" value="10" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Check Low Stock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Products -->
        <div class="mt-5">
            <h3>Current Products & Stock Levels</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products_query = mysqli_query($con, "SELECT movie_id, title, unit, price FROM movies ORDER BY title");
                        while ($product = mysqli_fetch_array($products_query)) {
                            $stock_class = $product['unit'] <= 5 ? 'table-danger' : ($product['unit'] <= 10 ? 'table-warning' : '');
                            echo "<tr class='$stock_class'>";
                            echo "<td>{$product['movie_id']}</td>";
                            echo "<td>{$product['title']}</td>";
                            echo "<td>{$product['unit']}</td>";
                            echo "<td>\${$product['price']}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="admin.php" class="btn btn-secondary">Back to Admin</a>
            <a href="inventory/index.php" class="btn btn-info">View Inventory</a>
        </div>
    </div>
</body>
</html>