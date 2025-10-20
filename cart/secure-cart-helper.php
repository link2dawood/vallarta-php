<?php
/**
 * Secure Cart Helper Functions
 * Contains reusable, secure cart operations
 */

// Prevent direct access
if (!defined('CART_HELPER_LOADED')) {
    define('CART_HELPER_LOADED', true);
} else {
    exit('Direct access not allowed');
}

/**
 * Get cart statistics for user
 */
function get_cart_stats($user_id, $con) {
    $stmt = $con->prepare("SELECT COUNT(*) as item_count, COALESCE(SUM(price * quantity), 0) as total_value FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Check if product exists and is available
 */
function validate_product($product_id, $con) {
    $stmt = $con->prepare("SELECT id, unit FROM movies WHERE id = ? AND unit > 0");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

/**
 * Get cart total with tax calculation
 */
function calculate_cart_total($user_id, $con, $tax_rate = 0.0) {
    $stmt = $con->prepare("SELECT SUM(price * quantity) as subtotal FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $subtotal = $data['subtotal'] ?? 0;
    $tax = $subtotal * $tax_rate;
    $total = $subtotal + $tax;
    
    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total
    ];
}

/**
 * Validate cart item ownership
 */
function validate_cart_ownership($cart_id, $user_id, $con) {
    $stmt = $con->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Log cart activity (for audit trail)
 */
function log_cart_activity($user_id, $action, $details, $con) {
    // Only log if logging table exists (optional)
    $stmt = $con->prepare("INSERT IGNORE INTO cart_logs (user_id, action, details, timestamp) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $action, $details);
        $stmt->execute();
    }
}

/**
 * Clean expired cart items (call periodically)
 */
function clean_expired_carts($con, $days_old = 30) {
    $stmt = $con->prepare("DELETE FROM cart WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->bind_param("i", $days_old);
    return $stmt->execute();
}

/**
 * Get recommended products based on cart contents
 */
function get_recommendations($user_id, $con, $limit = 5) {
    $stmt = $con->prepare("
        SELECT DISTINCT m.* FROM movies m 
        WHERE m.id NOT IN (SELECT movie_id FROM cart WHERE user_id = ?) 
        AND m.unit > 0 
        ORDER BY RAND() 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}
?>