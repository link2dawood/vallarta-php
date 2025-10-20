<?php
// Inventory management functions for 420 Vallarta

require_once('db.php');

/**
 * Parse order details string to extract products and quantities
 * Expected format: "Product Name (quantity), Product Name (quantity)"
 */
function parseOrderProducts($order_string) {
    global $con;
    $products = array();

    if (empty($order_string)) {
        return $products;
    }

    // Split by comma and clean up each product entry
    $product_strings = explode(',', $order_string);

    foreach ($product_strings as $product_string) {
        $product_string = trim($product_string);

        // Extract product name and quantity using regex
        // Matches: "Product Name (number)" or "Product Name(number)"
        if (preg_match('/^(.+?)\s*\((\d+)\)\s*$/', $product_string, $matches)) {
            $product_name = trim($matches[1]);
            $quantity = intval($matches[2]);

            // Get product ID and current stock from database
            $product_name_escaped = mysqli_real_escape_string($con, $product_name);
            $product_query = mysqli_query($con, "SELECT movie_id, unit, price FROM movies WHERE title = '$product_name_escaped'");

            if ($product_query && mysqli_num_rows($product_query) > 0) {
                $product_data = mysqli_fetch_array($product_query);
                $products[] = array(
                    'id' => $product_data['movie_id'],
                    'name' => $product_name,
                    'quantity' => $quantity,
                    'current_stock' => $product_data['unit'],
                    'price' => $product_data['price']
                );
            } else {
                // Log unknown product
                error_log("Unknown product in order: " . $product_name);
            }
        }
    }

    return $products;
}

/**
 * Update inventory levels when an order is modified
 */
function updateInventoryOnOrderChange($order_id, $old_order_string, $new_order_string, $user_id) {
    global $con;

    // Debug logging
    error_log("updateInventoryOnOrderChange called with Order ID: $order_id, User ID: $user_id");
    error_log("Old order string: " . $old_order_string);
    error_log("New order string: " . $new_order_string);

    $old_products = parseOrderProducts($old_order_string);
    $new_products = parseOrderProducts($new_order_string);

    // Debug: Log parsed products
    error_log("Old products parsed: " . json_encode($old_products));
    error_log("New products parsed: " . json_encode($new_products));

    // Create maps for easier comparison
    $old_quantities = array();
    $new_quantities = array();

    foreach ($old_products as $product) {
        $old_quantities[$product['id']] = $product['quantity'];
    }

    foreach ($new_products as $product) {
        $new_quantities[$product['id']] = $product['quantity'];
    }

    // Get all unique product IDs
    $all_product_ids = array_unique(array_merge(array_keys($old_quantities), array_keys($new_quantities)));

    $inventory_changes = array();

    foreach ($all_product_ids as $product_id) {
        $old_qty = isset($old_quantities[$product_id]) ? $old_quantities[$product_id] : 0;
        $new_qty = isset($new_quantities[$product_id]) ? $new_quantities[$product_id] : 0;

        $difference = $old_qty - $new_qty; // Positive = return to stock, Negative = take from stock

        if ($difference != 0) {
            error_log("Stock change needed for Product ID $product_id: difference = $difference (old: $old_qty, new: $new_qty)");
            $result = updateProductStock($product_id, $difference, $user_id, "Order #$order_id edit");
            error_log("updateProductStock result: " . json_encode($result));
            if ($result['success']) {
                $inventory_changes[] = array(
                    'product_id' => $product_id,
                    'change' => $difference,
                    'new_stock' => $result['new_stock']
                );
            }
        }
    }

    return $inventory_changes;
}

/**
 * Update stock for a specific product
 */
function updateProductStock($product_id, $quantity_change, $user_id, $reason = "Manual adjustment") {
    global $con;

    // Get current stock
    $stock_query = mysqli_query($con, "SELECT unit, title FROM movies WHERE movie_id = $product_id");
    if (!$stock_query || mysqli_num_rows($stock_query) == 0) {
        return array('success' => false, 'error' => 'Product not found');
    }

    $product_data = mysqli_fetch_array($stock_query);
    $current_stock = $product_data['unit'];
    $product_name = $product_data['title'];
    $new_stock = $current_stock + $quantity_change;

    // Prevent negative stock
    if ($new_stock < 0) {
        $new_stock = 0;
    }

    // Update stock in movies table
    $update_query = "UPDATE movies SET unit = $new_stock WHERE movie_id = $product_id";
    $update_result = mysqli_query($con, $update_query);

    if (!$update_result) {
        return array('success' => false, 'error' => 'Failed to update stock: ' . mysqli_error($con));
    }

    // Log inventory change only for registered users (positive user_id)
    if ($user_id > 0) {
        $date = date('Y-m-d H:i:s');
        $log_query = "INSERT INTO inventory (user_id, product_id, qnt_add, date) VALUES ('$user_id', '$product_id', '$quantity_change', '$date')";
        $log_result = mysqli_query($con, $log_query);

        if (!$log_result) {
            error_log("Failed to log inventory change: " . mysqli_error($con));
        }
    } else {
        // For guest users, just log to error log without database entry
        error_log("Guest user inventory change: Product ID $product_id, Quantity change: $quantity_change, Reason: $reason");
    }

    return array(
        'success' => true,
        'old_stock' => $current_stock,
        'new_stock' => $new_stock,
        'change' => $quantity_change,
        'product_name' => $product_name
    );
}

/**
 * Process order creation and deduct inventory
 */
function processOrderInventory($order_string, $user_id, $order_id) {
    $products = parseOrderProducts($order_string);
    $inventory_changes = array();

    foreach ($products as $product) {
        // Deduct from inventory (negative quantity change)
        $result = updateProductStock($product['id'], -$product['quantity'], $user_id, "Order #$order_id placed");
        if ($result['success']) {
            $inventory_changes[] = $result;
        }
    }

    return $inventory_changes;
}

/**
 * Validate that sufficient stock exists for an order
 */
function validateOrderStock($order_string) {
    $products = parseOrderProducts($order_string);
    $stock_issues = array();

    foreach ($products as $product) {
        if ($product['current_stock'] < $product['quantity']) {
            $stock_issues[] = array(
                'product' => $product['name'],
                'requested' => $product['quantity'],
                'available' => $product['current_stock']
            );
        }
    }

    return $stock_issues;
}

/**
 * Get inventory history for a product
 */
function getProductInventoryHistory($product_id, $limit = 50) {
    global $con;

    $query = "SELECT i.*, u.name as user_name, m.title as product_name
              FROM inventory i
              LEFT JOIN user_info u ON i.user_id = u.id
              LEFT JOIN movies m ON i.product_id = m.movie_id
              WHERE i.product_id = $product_id
              ORDER BY i.date DESC
              LIMIT $limit";

    $result = mysqli_query($con, $query);
    $history = array();

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $history[] = $row;
        }
    }

    return $history;
}

/**
 * Get low stock alerts
 */
function getLowStockAlerts($threshold = 5) {
    global $con;

    $query = "SELECT movie_id, title, unit, price FROM movies WHERE unit <= $threshold ORDER BY unit ASC";
    $result = mysqli_query($con, $query);
    $low_stock = array();

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $low_stock[] = $row;
        }
    }

    return $low_stock;
}
?>