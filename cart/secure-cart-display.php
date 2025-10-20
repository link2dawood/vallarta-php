<?php
/**
 * Secure Cart Display Component
 * Replace the vulnerable SQL queries in cart.php with this secure version
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include secure helper functions
define('CART_HELPER_LOADED', true);
require_once 'secure-cart-helper.php';

// Require authentication
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/**
 * Secure function to get product data
 */
function get_product_data($movie_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Process cart updates securely
 */
function handle_cart_updates($con, $user_id) {
    $message = [];
    
    if (isset($_POST['update_cart'])) {
        $update_quantity = (int) ($_POST['cart_quantity'] ?? 0);
        $update_id = (int) ($_POST['cart_id'] ?? 0);
        
        if ($update_quantity > 0 && $update_id > 0) {
            // Validate ownership
            if (validate_cart_ownership($update_id, $user_id, $con)) {
                $stmt = $con->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $update_quantity, $update_id, $user_id);
                
                if ($stmt->execute()) {
                    $message[] = 'Cart quantity updated successfully!';
                    log_cart_activity($user_id, 'update', "Cart ID: $update_id, Quantity: $update_quantity", $con);
                } else {
                    $message[] = 'Failed to update cart quantity.';
                }
            } else {
                $message[] = 'Invalid cart item.';
            }
        } else {
            $message[] = 'Invalid quantity or cart item.';
        }
    }
    
    if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
        $remove_id = (int) $_GET['remove'];
        
        if (validate_cart_ownership($remove_id, $user_id, $con)) {
            $stmt = $con->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $remove_id, $user_id);
            
            if ($stmt->execute()) {
                log_cart_activity($user_id, 'remove', "Cart ID: $remove_id", $con);
                header('Location: cart.php?success=removed');
                exit();
            }
        }
    }
    
    if (isset($_GET['delete_all'])) {
        $stmt = $con->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            log_cart_activity($user_id, 'clear', "All items cleared", $con);
            header('Location: cart.php?success=cleared');
            exit();
        }
    }
    
    return $message;
}

/**
 * Generate secure cart table HTML
 */
function generate_cart_table($con, $user_id) {
    // Get cart items securely
    $stmt = $con->prepare("SELECT c.*, m.unit as available_stock FROM cart c LEFT JOIN movies m ON c.movie_id = m.id WHERE c.user_id = ? ORDER BY c.id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_query = $stmt->get_result();
    
    $grand_total = 0;
    $cart_html = '';
    
    if ($cart_query->num_rows > 0) {
        while ($fetch_cart = $cart_query->fetch_assoc()) {
            // Get product data securely
            $qnt_query = get_product_data($fetch_cart['movie_id'], $con);
            $qnt = $qnt_query->fetch_array();
            
            // Sanitize output
            $cart_id = (int) $fetch_cart['id'];
            $movie_id = (int) $fetch_cart['movie_id'];
            $name = htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8');
            $image = htmlspecialchars($fetch_cart['image'], ENT_QUOTES, 'UTF-8');
            $price = (float) $fetch_cart['price'];
            $quantity = (int) $fetch_cart['quantity'];
            $max_stock = (int) ($qnt['unit'] ?? 1);
            $sub_total = $price * $quantity;
            
            $cart_html .= '<tr>';
            $cart_html .= '<td data-label="image" class="imagpro"><img src="images/' . $image . '" height="100" alt="Product Image"></td>';
            $cart_html .= '<td class="imname"><img class="nim" src="images/' . $image . '" height="70" alt="Product Image">' . $name . '</td>';
            $cart_html .= '<td data-label="price">$' . number_format($price, 2) . '</td>';
            $cart_html .= '<td data-label="quantity">';
            $cart_html .= '<form action="" method="post">';
            $cart_html .= '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
            $cart_html .= '<input type="hidden" name="cart_id" value="' . $cart_id . '">';
            $cart_html .= '<input type="number" min="1" max="' . $max_stock . '" name="cart_quantity" value="' . $quantity . '">';
            $cart_html .= '<input type="submit" name="update_cart" value="update" class="option-btn">';
            $cart_html .= '</form>';
            $cart_html .= '</td>';
            $cart_html .= '<td data-label="total price">$' . number_format($sub_total, 2) . '</td>';
            $cart_html .= '<td><a href="cart.php?remove=' . $cart_id . '" class="delete-btn" onclick="return confirm(\'Remove item from cart?\');">remove</a></td>';
            $cart_html .= '</tr>';
            
            $grand_total += $sub_total;
        }
    } else {
        $cart_html .= '<tr><td style="padding:20px; text-transform:capitalize;" colspan="6">No items in cart</td></tr>';
    }
    
    // Grand total row
    $disabled_class = ($grand_total > 0) ? '' : 'disabled';
    $cart_html .= '<tr class="table-bottom">';
    $cart_html .= '<td data-label="Grand total" colspan="4">Grand Total:</td>';
    $cart_html .= '<td>$' . number_format($grand_total, 2) . '</td>';
    $cart_html .= '<td><a href="cart.php?delete_all" onclick="return confirm(\'Clear entire cart?\');" class="delete-btn ' . $disabled_class . '">Clear Cart</a></td>';
    $cart_html .= '</tr>';
    
    return [
        'html' => $cart_html,
        'total' => $grand_total,
        'disabled_class' => $disabled_class
    ];
}

/**
 * Display success/error messages
 */
function display_messages() {
    $output = '';
    
    if (isset($_SESSION['success_message'])) {
        $output .= '<div class="message success">' . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $output .= '<div class="message error">' . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['error_message']);
    }
    
    // Handle URL parameters
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'removed':
                $output .= '<div class="message success">Item removed from cart successfully!</div>';
                break;
            case 'cleared':
                $output .= '<div class="message success">Cart cleared successfully!</div>';
                break;
            case 'updated':
                $output .= '<div class="message success">Cart updated successfully!</div>';
                break;
        }
    }
    
    return $output;
}
?>