<?php
/**
 * Secure Cart Management System
 * Replaces malicious file with clean, secure cart functionality
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Start session securely
session_start();

// Include database connection
require_once '../settings/db.php';

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function require_login() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['error_message'] = 'Please log in to access your cart.';
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Get user cart items
 */
function get_cart_items($user_id, $con) {
    $stmt = $con->prepare("SELECT c.*, m.unit as available_stock FROM cart c LEFT JOIN movies m ON c.movie_id = m.id WHERE c.user_id = ? ORDER BY c.id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Add item to cart
 */
function add_to_cart($user_id, $product_id, $name, $price, $image, $con) {
    // Validate inputs
    if (empty($name) || $price <= 0 || empty($image) || $product_id <= 0) {
        return ['success' => false, 'message' => 'Invalid product data'];
    }
    
    // Check if item already exists in cart
    $check_stmt = $con->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND movie_id = ?");
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Item already in cart'];
    }
    
    // Add to cart
    $stmt = $con->prepare("INSERT INTO cart (user_id, movie_id, name, price, image, quantity) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("iisds", $user_id, $product_id, $name, $price, $image);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Item added to cart successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add item to cart'];
    }
}

/**
 * Update cart item quantity
 */
function update_cart_quantity($cart_id, $quantity, $user_id, $con) {
    if ($quantity <= 0) {
        return ['success' => false, 'message' => 'Invalid quantity'];
    }
    
    $stmt = $con->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Cart updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update cart'];
    }
}

/**
 * Remove item from cart
 */
function remove_from_cart($cart_id, $user_id, $con) {
    $stmt = $con->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Item removed from cart'];
    } else {
        return ['success' => false, 'message' => 'Failed to remove item'];
    }
}

/**
 * Clear all cart items for user
 */
function clear_cart($user_id, $con) {
    $stmt = $con->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Cart cleared successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to clear cart'];
    }
}

// Require user to be logged in
require_login();
$user_id = (int) $_SESSION['user_id'];

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid request. Please try again.';
        header('Location: ../cart.php');
        exit();
    }
    
    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $product_id = (int) ($_POST['movie_id'] ?? 0);
        $name = sanitize_input($_POST['product_name'] ?? '');
        $price = (float) ($_POST['product_price'] ?? 0);
        $image = sanitize_input($_POST['product_image'] ?? '');
        
        $result = add_to_cart($user_id, $product_id, $name, $price, $image, $con);
        $_SESSION[($result['success'] ? 'success_message' : 'error_message')] = $result['message'];
    }
    
    // Update cart quantity
    elseif (isset($_POST['update_cart'])) {
        $cart_id = (int) ($_POST['cart_id'] ?? 0);
        $quantity = (int) ($_POST['cart_quantity'] ?? 0);
        
        $result = update_cart_quantity($cart_id, $quantity, $user_id, $con);
        $_SESSION[($result['success'] ? 'success_message' : 'error_message')] = $result['message'];
    }
    
    // Clear entire cart
    elseif (isset($_POST['clear_cart'])) {
        $result = clear_cart($user_id, $con);
        $_SESSION[($result['success'] ? 'success_message' : 'error_message')] = $result['message'];
    }
    
    // Redirect to prevent form resubmission
    header('Location: ../cart.php');
    exit();
}

// Process GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Remove single item
    if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
        $cart_id = (int) $_GET['remove'];
        $result = remove_from_cart($cart_id, $user_id, $con);
        $_SESSION[($result['success'] ? 'success_message' : 'error_message')] = $result['message'];
        header('Location: ../cart.php');
        exit();
    }
    
    // Clear all items
    elseif (isset($_GET['delete_all'])) {
        $result = clear_cart($user_id, $con);
        $_SESSION[($result['success'] ? 'success_message' : 'error_message')] = $result['message'];
        header('Location: ../cart.php');
        exit();
    }
}

// If accessed directly without action, redirect to cart page
header('Location: ../cart.php');
exit();
?>