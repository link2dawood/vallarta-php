<?php
// cartfunction.php
session_start();
require_once __DIR__ . '/../settings/db.php';

// Helper: set a flash message and redirect
function go(string $url, string $msg = null): void {
    if ($msg !== null) { $_SESSION['flash'] = $msg; }
    header('Location: ' . $url);
    exit;
}

// Get or create user_id for cart (guest users allowed)
if (empty($_SESSION['user_id'])) {
    // Create guest user session (negative ID for guests)
    $_SESSION['user_id'] = -(abs(crc32(uniqid())));
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Collect & validate input
    $product_id = (int) ($_POST['movie_id'] ?? 0);
    $name       = trim((string)($_POST['product_name'] ?? ''));
    $price_in   = (string)($_POST['product_price'] ?? '0');
    $image      = trim((string)($_POST['product_image'] ?? ''));
    $qty        = 1;

    $price = is_numeric($price_in) ? (float)$price_in : 0.0;

    if ($product_id <= 0 || $name === '' || $image === '' || $price <= 0) {
        go('../cart.php', 'Missing or invalid product data.');
    }

    if ($user_id > 0) {
        // Logged in user - use database cart
        // Check if already in cart (by user_id + movie_id)
        $check = $con->prepare('SELECT 1 FROM cart WHERE user_id = ? AND movie_id = ? LIMIT 1');
        $check->bind_param('ii', $user_id, $product_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            go('../cart.php', 'Product already added to cart!');
        }
        $check->close();

        // Insert into database cart
        $ins = $con->prepare('INSERT INTO cart (user_id, movie_id, name, price, image, quantity) VALUES (?,?,?,?,?,?)');
        $ins->bind_param('iisdsi', $user_id, $product_id, $name, $price, $image, $qty);
        $ins->execute();
        $ins->close();
        
    } else {
        // Guest user - use session cart
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        
        // Check if product already in session cart
        $found = false;
        foreach ($_SESSION['guest_cart'] as $item) {
            if ($item['movie_id'] == $product_id) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            go('../cart.php', 'Product already added to cart!');
        }
        
        // Add to session cart
        $_SESSION['guest_cart'][] = [
            'movie_id' => $product_id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $qty
        ];
    }

    go('../cart.php', 'Product added to cart!');
}

// If accessed directly without POST, just go back to cart
go('../cart.php');
