<?php
// Send E-Receipt Handler

require_once('settings/db.php');
require_once('settings/receipt_functions.php');
session_start();

// Check if user is logged in (admin access)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('location: LogReg/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);

    // Custom settings from form
    $custom_settings = array(
        'delivery_fee' => floatval($_POST['delivery_fee']),
        'discount' => floatval($_POST['discount']),
        'refund' => floatval($_POST['refund']),
        'eta' => trim($_POST['eta'])
    );

    // Send the receipt
    $result = sendEReceipt($order_id, $custom_settings);

    if ($result['success']) {
        header('location: receipt.php?order_id=' . $order_id . '&success=' . urlencode('Receipt sent successfully to ' . $result['email']));
    } else {
        header('location: receipt.php?order_id=' . $order_id . '&error=' . urlencode('Failed to send receipt: ' . $result['error']));
    }
} else {
    header('location: admin.php');
}
?>