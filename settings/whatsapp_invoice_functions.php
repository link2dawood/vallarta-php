<?php
// 420 Vallarta WhatsApp Invoice Generation Functions

require_once('db.php');
require_once('receipt_config.php');
require_once('inventory_functions.php');

/**
 * Generate WhatsApp-friendly text invoice
 * Plain text format that can be copy/pasted to WhatsApp
 */
function generateWhatsAppInvoice($order_id, $custom_settings = array()) {
    global $con;
    
    // Get order data
    $order_query = mysqli_query($con, "SELECT * FROM ordere WHERE id = '$order_id'");
    if (!$order_query || mysqli_num_rows($order_query) == 0) {
        return array('success' => false, 'error' => 'Order not found');
    }
    
    $order = mysqli_fetch_array($order_query);
    $config = getReceiptConfig();
    
    // Parse products from order
    $products = parseOrderProducts($order['total_products']);
    
    // Calculate totals
    $subtotal = 0;
    foreach ($products as $product) {
        $subtotal += $product['price'] * $product['quantity'];
    }
    
    // Get custom settings or defaults
    $delivery_fee = isset($custom_settings['delivery_fee']) ? $custom_settings['delivery_fee'] :
                    (!empty($order['delivery_fee']) ? $order['delivery_fee'] : $config['default_delivery_fee']);
    $discount = isset($custom_settings['discount']) ? $custom_settings['discount'] :
                (!empty($order['discount']) ? $order['discount'] : $config['default_discount']);
    $refund = isset($custom_settings['refund']) ? $custom_settings['refund'] :
              (!empty($order['refund']) ? $order['refund'] : $config['default_refund']);
    $eta = isset($custom_settings['eta']) ? $custom_settings['eta'] :
           (!empty($order['eta']) ? $order['eta'] : $config['default_eta']);
    
    // Handle complimentary items
    if (isset($custom_settings['complimentary_items'])) {
        $complimentary_items = $custom_settings['complimentary_items'];
    } elseif (!empty($order['complimentary_items'])) {
        $decoded = json_decode($order['complimentary_items'], true);
        $complimentary_items = is_array($decoded) ? $decoded : array();
    } else {
        $complimentary_items = array();
    }
    
    // Handle delivery address
    $delivery_address = isset($custom_settings['delivery_address']) ? $custom_settings['delivery_address'] :
                        (!empty($order['delivery_address_final']) ? $order['delivery_address_final'] : $order['adresse']);
    
    // Calculate final totals
    $final_total_mxn = $subtotal + $delivery_fee - $discount - $refund;
    $final_total_usd = convertMXNtoUSD($final_total_mxn);
    
    // Generate client ID
    $client_number = !empty($order['client_number']) ? $order['client_number'] : (100000 + $order_id);
    $client_id = 'CL-' . $client_number;
    
    // Generate receipt ID
    $receipt_id = generateReceiptID($order_id);
    
    // Build WhatsApp message
    $message = buildWhatsAppMessage(
        $order,
        $products,
        $subtotal,
        $delivery_fee,
        $discount,
        $refund,
        $final_total_mxn,
        $final_total_usd,
        $complimentary_items,
        $delivery_address,
        $eta,
        $client_id,
        $receipt_id
    );
    
    return array(
        'success' => true,
        'message' => $message,
        'receipt_id' => $receipt_id,
        'client_id' => $client_id
    );
}

/**
 * Build the WhatsApp message text
 */
function buildWhatsAppMessage($order, $products, $subtotal, $delivery_fee, $discount, $refund, 
                               $final_total_mxn, $final_total_usd, $complimentary_items, 
                               $delivery_address, $eta, $client_id, $receipt_id) {
    $config = getReceiptConfig();
    
    // Start building message
    $msg = "🌿 *420 VALLARTA* 🌿\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Receipt ID and Client ID
    $msg .= "📋 *RECEIPT*\n";
    $msg .= "Receipt ID: `{$receipt_id}`\n";
    $msg .= "Client ID: `{$client_id}`\n";
    $msg .= "Date: " . date('M d, Y h:i A', strtotime($order['dat'])) . "\n\n";
    
    // Customer Information
    $msg .= "👤 *CUSTOMER INFO*\n";
    $msg .= "Name: {$order['name']}\n";
    $msg .= "Phone: {$order['number']}\n";
    $msg .= "Email: {$order['email']}\n\n";
    
    // Delivery Information
    $msg .= "📍 *DELIVERY INFO*\n";
    $msg .= "Address: {$delivery_address}\n";
    $msg .= "ETA: {$eta}\n";
    $msg .= "Payment: {$order['method']}\n\n";
    
    // Products
    $msg .= "🛒 *PRODUCTS*\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    
    foreach ($products as $product) {
        $unit_price_mxn = number_format($product['price'], 2);
        $unit_price_usd = number_format(convertMXNtoUSD($product['price']), 2);
        $total_price_mxn = number_format($product['price'] * $product['quantity'], 2);
        $total_price_usd = number_format(convertMXNtoUSD($product['price'] * $product['quantity']), 2);
        
        $msg .= "*{$product['name']}*\n";
        $msg .= "  Qty: {$product['quantity']} × \${$unit_price_mxn} MXN (\${$unit_price_usd} USD)\n";
        $msg .= "  Total: \${$total_price_mxn} MXN (\${$total_price_usd} USD)\n\n";
    }
    
    // Complimentary Items
    if (!empty($complimentary_items)) {
        $msg .= "🎁 *COMPLIMENTARY ITEMS*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        foreach ($complimentary_items as $item_name => $item_value) {
            $msg .= "• {$item_name}: {$item_value}\n";
        }
        $msg .= "\n";
    }
    
    // Pricing Breakdown
    $msg .= "💰 *PRICING*\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "Subtotal: \$" . number_format($subtotal, 2) . " MXN (\$" . number_format(convertMXNtoUSD($subtotal), 2) . " USD)\n";
    
    if ($delivery_fee > 0) {
        $msg .= "Delivery Fee: \$" . number_format($delivery_fee, 2) . " MXN (\$" . number_format(convertMXNtoUSD($delivery_fee), 2) . " USD)\n";
    }
    
    if ($discount > 0) {
        $msg .= "Discount: -\$" . number_format($discount, 2) . " MXN (\$" . number_format(convertMXNtoUSD($discount), 2) . " USD)\n";
    }
    
    if ($refund > 0) {
        $msg .= "Refund: -\$" . number_format($refund, 2) . " MXN (\$" . number_format(convertMXNtoUSD($refund), 2) . " USD)\n";
    }
    
    $msg .= "\n*TOTAL: \$" . number_format($final_total_mxn, 2) . " MXN*\n";
    $msg .= "*TOTAL: \$" . number_format($final_total_usd, 2) . " USD*\n\n";
    
    // Payment Instructions (based on method)
    $msg .= "💳 *PAYMENT INSTRUCTIONS*\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= getPaymentInstructions($order['method']) . "\n\n";
    
    // Contact & Social
    $msg .= "📞 *CONTACT US*\n";
    $msg .= "Phone/WhatsApp: {$config['whatsapp_number']}\n";
    $msg .= "Email: {$config['company_email']}\n";
    $msg .= "Website: {$config['company_website']}\n\n";
    
    $msg .= "Follow us:\n";
    if (!empty($config['social_handles']['instagram'])) {
        $msg .= "📷 Instagram: {$config['social_handles']['instagram']}\n";
    }
    if (!empty($config['social_handles']['facebook'])) {
        $msg .= "📘 Facebook: {$config['social_handles']['facebook']}\n";
    }
    
    $msg .= "\n━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🙏 *Thank you for choosing 420 Vallarta!*\n";
    $msg .= "Your satisfaction is our priority.\n";
    
    return $msg;
}

/**
 * Get payment instructions based on method
 */
function getPaymentInstructions($method) {
    $instructions = array(
        'Cash on Delivery' => 'Please have exact cash ready upon delivery. Our driver will collect payment.',
        'Bank Transfer' => "Please transfer to:\nBank: BBVA\nAccount: [Contact us for details]\nSend proof of transfer via WhatsApp.",
        'Visa MasterCard Via Stripe' => 'Payment link will be sent separately. Click to complete payment securely.',
        'PayPal' => 'PayPal invoice will be sent to your email. Please complete payment through PayPal.',
        'Crypto' => 'Cryptocurrency wallet address will be provided. Send payment and share transaction ID.'
    );
    
    return $instructions[$method] ?? 'Payment method: ' . $method;
}

// Note: generateReceiptID() function is already defined in receipt_config.php
?>

