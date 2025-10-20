<?php
// 420 Vallarta E-Receipt Generation Functions

require_once('db.php');
require_once('receipt_config.php');
require_once('inventory_functions.php');

// Initialize receipt settings
initializeReceiptSettings();
loadReceiptSettings();

/**
 * Generate complete e-receipt data structure
 */
function generateReceiptData($order_id, $custom_settings = array()) {
    global $con;

    // Get order data
    $order_query = mysqli_query($con, "SELECT * FROM ordere WHERE id = '$order_id'");
    if (!$order_query || mysqli_num_rows($order_query) == 0) {
        return false;
    }

    $order = mysqli_fetch_array($order_query);
    $config = getReceiptConfig();

    // Parse products from order
    $products = parseOrderProducts($order['total_products']);

    // Calculate totals from individual products
    $calculated_subtotal = 0;
    $itemized_products = array();

    foreach ($products as $product) {
        $total_price = $product['price'] * $product['quantity'];
        $calculated_subtotal += $total_price;

        $itemized_products[] = array(
            'name' => $product['name'],
            'quantity' => $product['quantity'],
            'unit_price_mxn' => $product['price'],
            'unit_price_usd' => convertMXNtoUSD($product['price']),
            'total_price_mxn' => $total_price,
            'total_price_usd' => convertMXNtoUSD($total_price)
        );
    }

    // Use the database total_price as the authoritative subtotal (in case order was manually edited)
    // This ensures that if an admin edits the order total, the receipt reflects the edited amount
    $subtotal = !empty($order['total_price']) ? floatval($order['total_price']) : $calculated_subtotal;

    // Apply custom settings, or use saved values from database, or fall back to defaults
    // Priority: custom_settings > database saved values > defaults
    $delivery_fee = isset($custom_settings['delivery_fee']) ? $custom_settings['delivery_fee'] :
                    (!empty($order['delivery_fee']) ? $order['delivery_fee'] : $config['default_delivery_fee']);
    $discount = isset($custom_settings['discount']) ? $custom_settings['discount'] :
                (!empty($order['discount']) ? $order['discount'] : $config['default_discount']);
    $refund = isset($custom_settings['refund']) ? $custom_settings['refund'] :
              (!empty($order['refund']) ? $order['refund'] : $config['default_refund']);
    $eta = isset($custom_settings['eta']) ? $custom_settings['eta'] :
           (!empty($order['eta']) ? $order['eta'] : $config['default_eta']);

    // Handle complimentary items (may be JSON in database)
    if (isset($custom_settings['complimentary_items'])) {
        // Use custom settings passed during finalization
        $complimentary_items = $custom_settings['complimentary_items'];
    } elseif (!empty($order['complimentary_items'])) {
        // Use saved complimentary items from database
        $decoded = json_decode($order['complimentary_items'], true);
        $complimentary_items = is_array($decoded) ? $decoded : array();
    } else {
        // No complimentary items saved
        $complimentary_items = array();
    }

    // Handle delivery address
    $delivery_address = isset($custom_settings['delivery_address']) ? $custom_settings['delivery_address'] :
                        (!empty($order['delivery_address_final']) ? $order['delivery_address_final'] : $order['adresse']);

    // Calculate final totals
    $total_before_adjustments = $subtotal + $delivery_fee;
    $final_total_mxn = $total_before_adjustments - $discount - $refund;
    $final_total_usd = convertMXNtoUSD($final_total_mxn);

    // Generate client ID from client_number field if available, otherwise use order_id
    $client_number = !empty($order['client_number']) ? $order['client_number'] : (100000 + $order_id);
    $client_id = 'CL-' . $client_number;

    // Generate receipt data
    $receipt_data = array(
        'receipt_id' => generateReceiptID($order_id),
        'order_id' => $order_id,
        'client_info' => array(
            'name' => $order['name'],
            'email' => $order['email'],
            'phone' => $order['number'],
            'client_id' => $client_id
        ),
        'order_details' => array(
            'order_date' => $order['dat'],
            'payment_method' => $order['method'],
            'delivery_address' => $delivery_address,
            'eta' => $eta,
            'status' => $order['valid']
        ),
        'itemized_products' => $itemized_products,
        'financial_summary' => array(
            'subtotal_mxn' => $subtotal,
            'subtotal_usd' => convertMXNtoUSD($subtotal),
            'delivery_fee_mxn' => $delivery_fee,
            'delivery_fee_usd' => convertMXNtoUSD($delivery_fee),
            'discount_mxn' => $discount,
            'discount_usd' => convertMXNtoUSD($discount),
            'refund_mxn' => $refund,
            'refund_usd' => convertMXNtoUSD($refund),
            'total_mxn' => $final_total_mxn,
            'total_usd' => $final_total_usd
        ),
        'complimentary_items' => $complimentary_items,
        'company_info' => array(
            'name' => $config['company_name'],
            'address' => $config['company_address'],
            'phone' => $config['company_phone'],
            'email' => $config['company_email'],
            'website' => $config['company_website'],
            'whatsapp' => $config['whatsapp_number']
        ),
        'social_media' => $config['social_handles'],
        'payment_options_link' => 'https://420vallarta.com/420%20Vallarta%20Payment%20Options.php',
        'payment_information' => getPaymentInformation($order['method'], $client_id),
        'exchange_rate' => getCurrentExchangeRate(),
        'generated_at' => date('Y-m-d H:i:s')
    );

    return $receipt_data;
}

/**
 * Generate HTML e-receipt template
 */
function generateReceiptHTML($receipt_data) {
    $config = getReceiptConfig();

    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>420 Vallarta Receipt - ' . $receipt_data['receipt_id'] . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; background: #000000; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .header img { max-width: 100px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
        .thank-you-section { background: #f9f9f9; padding: 20px; text-align: center; border-left: 1px solid #ddd; border-right: 1px solid #ddd; }
        .thank-you-section h2 { color: #2a561f; margin: 0 0 10px 0; }
        .thank-you-section .receipt-number { font-size: 1.1em; font-weight: bold; color: #333; margin: 10px 0; }
        .thank-you-section .message { background: white; padding: 15px; border-radius: 5px; margin-top: 15px; text-align: left; }
        .receipt-content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .section { margin-bottom: 20px; }
        .section h3 { color: #2a561f; border-bottom: 2px solid #2a561f; padding-bottom: 5px; }
        .client-info { background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .products-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .products-table th, .products-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .products-table th { background: #000000; color: white; }
        .products-table .currency-mxn { font-weight: bold; color: #2a561f; }
        .products-table .currency-usd { color: #666; font-style: italic; }
        .totals-table { width: 100%; border-collapse: collapse; background: white; }
        .totals-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .totals-table .total-row { background: #000000; color: white; font-weight: bold; font-size: 1.2em; }
        .complimentary { background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
        .social-media a { color: #4CAF50; text-decoration: none; margin: 0 10px; }
        .social-media i { font-size: 16px; margin-right: 5px; color: #4CAF50; }
        .social-media .fa-instagram { color: #E4405F; }
        .social-media .fa-facebook { color: #1877F2; }
        .social-media .fa-twitter { color: #1DA1F2; }
        .social-media .fa-youtube { color: #FF0000; }
        .social-media .fa-pinterest { color: #E60023; }
        .payment-link { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        .eta-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; }
        .exchange-rate { font-size: 0.9em; color: #666; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://420vallarta.com/images/PV%20emblem%20round.png" alt="420 Vallarta Logo">
    </div>

    <div class="thank-you-section">
        <h2>Thank You for Your Order!</h2>
        <div class="receipt-number">Receipt #' . $receipt_data['receipt_id'] . '</div>
        <div class="message">
            <p>Dear ' . htmlspecialchars($receipt_data['client_info']['name']) . ',</p>
            <p>Thank you for your confidence in 420 Vallarta!</p>
            <p>We have received your order and will contact you soon via WhatsApp to confirm your order and make delivery arrangements.</p>
        </div>
    </div>

    <div class="receipt-content">
        <!-- Client Information -->
        <div class="section">
            <h3>📋 Client Information</h3>
            <div class="client-info">
                <strong>Client ID:</strong> ' . $receipt_data['client_info']['client_id'] . '<br>
                <strong>Name:</strong> ' . htmlspecialchars($receipt_data['client_info']['name']) . '<br>
                <strong>Email:</strong> ' . htmlspecialchars($receipt_data['client_info']['email']) . '<br>
                <strong>Phone:</strong> ' . htmlspecialchars($receipt_data['client_info']['phone']) . '<br>
                <strong>Payment Method:</strong> ' . htmlspecialchars($receipt_data['order_details']['payment_method']) . '
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="section">
            <h3>🚚 Delivery Information</h3>
            <div class="client-info">
                <strong>Address:</strong> ' . htmlspecialchars($receipt_data['order_details']['delivery_address']) . '<br>
                <div class="eta-box">
                    <strong>Estimated Delivery Time:</strong> ' . htmlspecialchars($receipt_data['order_details']['eta']) . '
                </div>
            </div>
        </div>

        <!-- Itemized Products -->
        <div class="section">
            <h3>🛒 Order Details</h3>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($receipt_data['itemized_products'] as $product) {
        $html .= '<tr>
            <td>' . htmlspecialchars($product['name']) . '</td>
            <td>' . $product['quantity'] . '</td>
            <td>
                <span class="currency-mxn">' . formatCurrency($product['unit_price_mxn'], 'MXN') . '</span><br>
                <span class="currency-usd">' . formatCurrency($product['unit_price_usd'], 'USD') . '</span>
            </td>
            <td>
                <span class="currency-mxn">' . formatCurrency($product['total_price_mxn'], 'MXN') . '</span><br>
                <span class="currency-usd">' . formatCurrency($product['total_price_usd'], 'USD') . '</span>
            </td>
        </tr>';
    }

    $html .= '</tbody>
            </table>
        </div>

        <!-- Complimentary Items -->';

    // Only show complimentary items section if there are items
    if (!empty($receipt_data['complimentary_items'])) {
        $html .= '
        <div class="section">
            <h3>🎁 Complimentary Items</h3>
            <div class="complimentary">';

        foreach ($receipt_data['complimentary_items'] as $item => $note) {
            $html .= '<strong>' . htmlspecialchars($item) . ':</strong> ' . htmlspecialchars($note) . '<br>';
        }

        $html .= '</div>
        </div>';
    }

    $html .= '

        <!-- Financial Summary -->
        <div class="section">
            <h3>💰 Financial Summary</h3>
            <table class="totals-table">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td>
                        <span class="currency-mxn">' . formatCurrency($receipt_data['financial_summary']['subtotal_mxn'], 'MXN') . '</span><br>
                        <span class="currency-usd">' . formatCurrency($receipt_data['financial_summary']['subtotal_usd'], 'USD') . '</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Delivery Fee:</strong></td>
                    <td>
                        <span class="currency-mxn">' . formatCurrency($receipt_data['financial_summary']['delivery_fee_mxn'], 'MXN') . '</span><br>
                        <span class="currency-usd">' . formatCurrency($receipt_data['financial_summary']['delivery_fee_usd'], 'USD') . '</span>
                    </td>
                </tr>';

    if ($receipt_data['financial_summary']['discount_mxn'] > 0) {
        $html .= '<tr>
            <td><strong>Discount:</strong></td>
            <td style="color: green;">
                -<span class="currency-mxn">' . formatCurrency($receipt_data['financial_summary']['discount_mxn'], 'MXN') . '</span><br>
                -<span class="currency-usd">' . formatCurrency($receipt_data['financial_summary']['discount_usd'], 'USD') . '</span>
            </td>
        </tr>';
    }

    if ($receipt_data['financial_summary']['refund_mxn'] > 0) {
        $html .= '<tr>
            <td><strong>Refund:</strong></td>
            <td style="color: blue;">
                -<span class="currency-mxn">' . formatCurrency($receipt_data['financial_summary']['refund_mxn'], 'MXN') . '</span><br>
                -<span class="currency-usd">' . formatCurrency($receipt_data['financial_summary']['refund_usd'], 'USD') . '</span>
            </td>
        </tr>';
    }

    $html .= '<tr class="total-row">
                    <td><strong>TOTAL:</strong></td>
                    <td>
                        <strong>' . formatCurrency($receipt_data['financial_summary']['total_mxn'], 'MXN') . '</strong><br>
                        <strong>' . formatCurrency($receipt_data['financial_summary']['total_usd'], 'USD') . '</strong>
                    </td>
                </tr>
            </table>
            <div class="exchange-rate">
                Dollar totals are approximated based on today\'s rates from Google. Your financial institution will provide you the exact exchange rate.
            </div>
        </div>

        <!-- Payment Information -->
        <div class="section">
            <h3>💳 ' . htmlspecialchars($receipt_data['payment_information']['title']) . '</h3>
            <div class="client-info">
                <p>' . htmlspecialchars($receipt_data['payment_information']['instructions']) . '</p>';
    
    if (!empty($receipt_data['payment_information']['details'])) {
        $html .= '<div style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        foreach ($receipt_data['payment_information']['details'] as $key => $value) {
            $html .= '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
        }
        $html .= '</div>';
    }

    // Only show warning if there's a reference note (PayPal only)
    if (!empty($receipt_data['payment_information']['reference_note'])) {
        $html .= '<p style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px; font-weight: bold; color: #856404;">
                        ⚠️ ' . htmlspecialchars($receipt_data['payment_information']['reference_note']) . '
                    </p>';
    }

    $html .= '</div>
        </div>
    </div>

    <div class="footer">
        <h3 style="color: white; margin: 0 0 10px 0;">' . $config['company_name'] . '</h3>
        <p style="color: white; margin: 5px 0;">' . $config['company_address'] . '</p>
        <p style="color: white; margin: 5px 0;">📞 ' . $config['company_phone'] . ' | 📧 ' . $config['company_email'] . '</p>
        <p style="color: white; margin: 5px 0;">🌐 ' . $config['company_website'] . '</p>

        <p style="color: white; margin: 15px 0 5px 0;"><strong>Follow us on social media:</strong></p>
        <div class="social-media" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin: 15px 0; color: white;">';

    // Social media Font Awesome icons mapping
    $social_icons = array(
        'instagram' => '<i class="fab fa-instagram"></i>',
        'facebook' => '<i class="fab fa-facebook"></i>',
        'twitter' => '<i class="fab fa-twitter"></i>',
        'youtube' => '<i class="fab fa-youtube"></i>',
        'pinterest' => '<i class="fab fa-pinterest"></i>'
    );

    foreach ($receipt_data['social_media'] as $platform => $handle) {
        $icon = isset($social_icons[$platform]) ? $social_icons[$platform] : '<i class="fas fa-globe"></i>';
        $html .= '<span style="white-space: nowrap; color: white;">' . $icon . ' <strong>' . ucfirst($platform) . ':</strong> ' . htmlspecialchars($handle) . '</span>';
    }

    $html .= '</div>

        <p style="margin-top: 20px; font-size: 0.9em; color: white;">
            ' . $config['receipt_footer_text'] . '
        </p>

        <p style="font-size: 0.8em; margin-top: 15px; color: white;">
            Receipt generated on ' . date('F j, Y g:i A', strtotime($receipt_data['generated_at'])) . '
        </p>
    </div>
</body>
</html>';

    return $html;
}

/**
 * Generate PDF receipt
 */
function generateReceiptPDF($receipt_data) {
    // Load TCPDF library
    require_once(dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/tcpdf.php');

    $config = getReceiptConfig();

    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('420 Vallarta');
    $pdf->SetAuthor('420 Vallarta');
    $pdf->SetTitle('Receipt - ' . $receipt_data['receipt_id']);
    $pdf->SetSubject('Order Receipt');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Logo
    $logo_path = dirname(__DIR__) . '/images/PV emblem round.png';
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 85, 15, 40, 0, 'PNG');
    }

    $pdf->Ln(45);

    // Title
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(42, 86, 31);
    $pdf->Cell(0, 10, 'E-RECEIPT', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Receipt #' . $receipt_data['receipt_id'], 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, date('F j, Y g:i A', strtotime($receipt_data['order_details']['order_date'])), 0, 1, 'C');

    $pdf->Ln(5);

    // Client Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, ' Client Information', 0, 1, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->Cell(40, 6, 'Client ID:', 0, 0);
    $pdf->Cell(0, 6, $receipt_data['client_info']['client_id'], 0, 1);

    $pdf->Cell(40, 6, 'Name:', 0, 0);
    $pdf->Cell(0, 6, $receipt_data['client_info']['name'], 0, 1);

    $pdf->Cell(40, 6, 'Phone:', 0, 0);
    $pdf->Cell(0, 6, $receipt_data['client_info']['phone'], 0, 1);

    $pdf->Cell(40, 6, 'Payment Method:', 0, 0);
    $pdf->Cell(0, 6, $receipt_data['order_details']['payment_method'], 0, 1);

    $pdf->Ln(5);

    // Delivery Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, ' Delivery Information', 0, 1, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->Cell(40, 6, 'Address:', 0, 0);
    $pdf->MultiCell(0, 6, $receipt_data['order_details']['delivery_address'], 0, 'L');

    $pdf->Cell(40, 6, 'ETA:', 0, 0);
    $pdf->Cell(0, 6, $receipt_data['order_details']['eta'], 0, 1);

    $pdf->Ln(5);

    // Order Details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, ' Order Details', 0, 1, 'L', true);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    // Products table header
    $pdf->Cell(80, 6, 'Product', 1, 0, 'L', false);
    $pdf->Cell(15, 6, 'Qty', 1, 0, 'C', false);
    $pdf->Cell(40, 6, 'Unit Price', 1, 0, 'R', false);
    $pdf->Cell(45, 6, 'Total', 1, 1, 'R', false);

    $pdf->SetFont('helvetica', '', 9);

    // Products
    foreach ($receipt_data['itemized_products'] as $product) {
        $pdf->Cell(80, 6, $product['name'], 1, 0, 'L');
        $pdf->Cell(15, 6, $product['quantity'], 1, 0, 'C');
        $pdf->Cell(40, 6, formatCurrency($product['unit_price_mxn'], 'MXN'), 1, 0, 'R');
        $pdf->Cell(45, 6, formatCurrency($product['total_price_mxn'], 'MXN'), 1, 1, 'R');
    }

    $pdf->Ln(5);

    // Complimentary Items
    if (!empty($receipt_data['complimentary_items'])) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(232, 245, 232);
        $pdf->SetTextColor(42, 86, 31);
        $pdf->Cell(0, 8, ' Complimentary Items', 0, 1, 'L', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);

        foreach ($receipt_data['complimentary_items'] as $item => $note) {
            $pdf->Cell(60, 6, $item . ':', 0, 0);
            $pdf->Cell(0, 6, $note, 0, 1);
        }

        $pdf->Ln(5);
    }

    // Financial Summary
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, ' Financial Summary', 0, 1, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    // Subtotal
    $pdf->Cell(100, 6, 'Subtotal:', 0, 0);
    $pdf->Cell(0, 6, formatCurrency($receipt_data['financial_summary']['subtotal_mxn'], 'MXN'), 0, 1, 'R');

    // Delivery Fee
    $pdf->Cell(100, 6, 'Delivery Fee:', 0, 0);
    $pdf->Cell(0, 6, formatCurrency($receipt_data['financial_summary']['delivery_fee_mxn'], 'MXN'), 0, 1, 'R');

    // Discount
    if ($receipt_data['financial_summary']['discount_mxn'] > 0) {
        $pdf->SetTextColor(0, 128, 0);
        $pdf->Cell(100, 6, 'Discount:', 0, 0);
        $pdf->Cell(0, 6, '-' . formatCurrency($receipt_data['financial_summary']['discount_mxn'], 'MXN'), 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);
    }

    // Refund
    if ($receipt_data['financial_summary']['refund_mxn'] > 0) {
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Cell(100, 6, 'Refund:', 0, 0);
        $pdf->Cell(0, 6, '-' . formatCurrency($receipt_data['financial_summary']['refund_mxn'], 'MXN'), 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);
    }

    // Total
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(100, 8, 'TOTAL:', 0, 0, 'L', true);
    $pdf->Cell(0, 8, formatCurrency($receipt_data['financial_summary']['total_mxn'], 'MXN'), 0, 1, 'R', true);

    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Dollar totals are approximated based on today\'s rates from Google.', 0, 1, 'R');

    $pdf->Ln(5);

    // Payment Information
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(42, 86, 31);
    $pdf->Cell(0, 6, $receipt_data['payment_information']['title'], 0, 1);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 5, $receipt_data['payment_information']['instructions'], 0, 'L');

    if (!empty($receipt_data['payment_information']['details'])) {
        $pdf->Ln(2);
        foreach ($receipt_data['payment_information']['details'] as $key => $value) {
            $pdf->Cell(40, 5, $key . ':', 0, 0);
            $pdf->Cell(0, 5, $value, 0, 1);
        }
    }

    if (!empty($receipt_data['payment_information']['reference_note'])) {
        $pdf->Ln(2);
        $pdf->SetFillColor(255, 243, 205);
        $pdf->MultiCell(0, 5, $receipt_data['payment_information']['reference_note'], 0, 'L', true);
    }

    // Footer
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(42, 86, 31);
    $pdf->Cell(0, 6, $config['company_name'], 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 5, $config['company_address'], 0, 1, 'C');
    $pdf->Cell(0, 5, $config['company_phone'] . ' | ' . $config['company_email'], 0, 1, 'C');

    // Social Media - Centered
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Follow us on social media:', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $social_text = '';
    foreach ($receipt_data['social_media'] as $platform => $handle) {
        if ($social_text != '') $social_text .= ' | ';
        $social_text .= ucfirst($platform) . ': ' . $handle;
    }
    $pdf->Cell(0, 5, $social_text, 0, 1, 'C');

    // Return PDF as string
    return $pdf->Output('receipt.pdf', 'S');
}

/**
 * Send e-receipt via email
 */
function sendEReceipt($order_id, $custom_settings = array()) {
    global $con;

    // Generate receipt data
    $receipt_data = generateReceiptData($order_id, $custom_settings);
    if (!$receipt_data) {
        return array('success' => false, 'error' => 'Order not found');
    }

    // Generate HTML receipt
    $receipt_html = generateReceiptHTML($receipt_data);

    // Only generate PDF if order is FINALIZED
    $pdf_content = null;
    if ($receipt_data['order_details']['status'] === 'Finalized') {
        $pdf_content = generateReceiptPDF($receipt_data);
    }

    // Setup PHPMailer
    require dirname(__DIR__) . '/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $config = getReceiptConfig();

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['receipt_from_email'];
    $mail->Password = 'Darialheli12!'; // Should be moved to config
    $mail->SMTPSecure = 'TLS';
    $mail->Port = 587;

    $mail->setFrom($config['receipt_from_email'], $config['receipt_from_name']);
    $mail->addAddress($receipt_data['client_info']['email'], $receipt_data['client_info']['name']);
    $mail->addBCC('420vallarta@gmail.com'); // Admin copy

    $mail->isHTML(true);
    $mail->Subject = str_replace('{order_id}', $receipt_data['receipt_id'], $config['receipt_subject']);
    $mail->Body = $receipt_html;
    $mail->AltBody = 'Please view this email in HTML format to see your 420 Vallarta receipt.';

    // Attach PDF receipt only if order is finalized
    if ($pdf_content !== null) {
        $mail->addStringAttachment($pdf_content, 'Receipt_' . $receipt_data['receipt_id'] . '.pdf', 'base64', 'application/pdf');
    }

    if ($mail->send()) {
        // Log receipt generation
        $log_query = "INSERT INTO receipt_log (order_id, receipt_id, email_sent, generated_at)
                     VALUES ('{$order_id}', '{$receipt_data['receipt_id']}', 1, NOW())";
        mysqli_query($con, $log_query);

        return array(
            'success' => true,
            'receipt_id' => $receipt_data['receipt_id'],
            'email' => $receipt_data['client_info']['email']
        );
    } else {
        return array(
            'success' => false,
            'error' => 'Email send failed: ' . $mail->ErrorInfo
        );
    }
}

/**
 * Create receipt log table if it doesn't exist
 */
function initializeReceiptLog() {
    global $con;

    $create_log_table = "CREATE TABLE IF NOT EXISTS receipt_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        receipt_id VARCHAR(50) NOT NULL,
        email_sent TINYINT DEFAULT 0,
        custom_settings TEXT,
        generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_order_id (order_id),
        KEY idx_receipt_id (receipt_id)
    )";

    mysqli_query($con, $create_log_table);
}

/**
 * Generate simple text receipt for WhatsApp
 */
function generateTextReceipt($receipt_data) {
    $config = getReceiptConfig();

    $text = "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "🌿 420 VALLARTA E-RECEIPT\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

    // Logo
    $text .= "📷 Logo: " . $config['logo_url'] . "\n\n";

    // Receipt Info
    $text .= "📄 *Receipt #" . $receipt_data['receipt_id'] . "*\n";
    $text .= "📅 " . date('F j, Y g:i A', strtotime($receipt_data['order_details']['order_date'])) . "\n\n";

    // Client Information
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "📋 CLIENT INFORMATION\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "🆔 Client ID: " . $receipt_data['client_info']['client_id'] . "\n";
    $text .= "👤 Name: " . $receipt_data['client_info']['name'] . "\n";
    $text .= "💬 WhatsApp: " . $receipt_data['client_info']['phone'] . "\n";
    $text .= "💳 Payment: " . $receipt_data['order_details']['payment_method'] . "\n\n";

    // Delivery Information
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "🚚 DELIVERY INFORMATION\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "📍 Address: " . $receipt_data['order_details']['delivery_address'] . "\n";
    $text .= "⏱️ ETA: " . $receipt_data['order_details']['eta'] . "\n\n";

    // Order Details
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "🛒 ORDER DETAILS\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($receipt_data['itemized_products'] as $product) {
        $text .= "\n" . $product['name'] . "\n";
        $text .= "  Qty: " . $product['quantity'] . " x " . formatCurrency($product['unit_price_mxn'], 'MXN') . "\n";
        $text .= "  Subtotal: " . formatCurrency($product['total_price_mxn'], 'MXN') . "\n";
        $text .= "  (USD " . formatCurrency($product['total_price_usd'], 'USD') . ")\n";
    }
    $text .= "\n";

    // Complimentary Items
    if (!empty($receipt_data['complimentary_items'])) {
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🎁 COMPLIMENTARY ITEMS\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        foreach ($receipt_data['complimentary_items'] as $item => $note) {
            $text .= "• " . $item . ": " . $note . "\n";
        }
        $text .= "\n";
    }

    // Financial Summary
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "💰 FINANCIAL SUMMARY\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "Subtotal:\n";
    $text .= "  " . formatCurrency($receipt_data['financial_summary']['subtotal_mxn'], 'MXN') . "\n";
    $text .= "  (" . formatCurrency($receipt_data['financial_summary']['subtotal_usd'], 'USD') . ")\n\n";

    $text .= "Delivery Fee:\n";
    $text .= "  " . formatCurrency($receipt_data['financial_summary']['delivery_fee_mxn'], 'MXN') . "\n";
    $text .= "  (" . formatCurrency($receipt_data['financial_summary']['delivery_fee_usd'], 'USD') . ")\n\n";

    if ($receipt_data['financial_summary']['discount_mxn'] > 0) {
        $text .= "Discount:\n";
        $text .= "  -" . formatCurrency($receipt_data['financial_summary']['discount_mxn'], 'MXN') . "\n";
        $text .= "  (-" . formatCurrency($receipt_data['financial_summary']['discount_usd'], 'USD') . ")\n\n";
    }

    if ($receipt_data['financial_summary']['refund_mxn'] > 0) {
        $text .= "Refund:\n";
        $text .= "  -" . formatCurrency($receipt_data['financial_summary']['refund_mxn'], 'MXN') . "\n";
        $text .= "  (-" . formatCurrency($receipt_data['financial_summary']['refund_usd'], 'USD') . ")\n\n";
    }

    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "💵 *TOTAL:*\n";
    $text .= "*" . formatCurrency($receipt_data['financial_summary']['total_mxn'], 'MXN') . "*\n";
    $text .= "*" . formatCurrency($receipt_data['financial_summary']['total_usd'], 'USD') . "*\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "Exchange Rate: 1 USD = " . number_format($receipt_data['exchange_rate'], 2) . " MXN\n";
    $text .= "(Updated: " . date('M j, Y') . ")\n\n";
    $text .= "ℹ️ Dollar totals are approximated based on today's rates from Google. Your financial institution will provide you the exact exchange rate.\n\n";

    // Payment Information
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "💳 " . strtoupper($receipt_data['payment_information']['title']) . "\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= $receipt_data['payment_information']['instructions'] . "\n\n";

    if (!empty($receipt_data['payment_information']['details'])) {
        foreach ($receipt_data['payment_information']['details'] as $key => $value) {
            $text .= $key . ": " . $value . "\n";
        }
        $text .= "\n";
    }

    $text .= "⚠️ " . $receipt_data['payment_information']['reference_note'] . "\n\n";

    // Company Info
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "📞 CONTACT US\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "🌿 " . $receipt_data['company_info']['name'] . "\n";
    $text .= "📍 " . $receipt_data['company_info']['address'] . "\n";
    $text .= "💬 WhatsApp: " . $receipt_data['company_info']['whatsapp'] . "\n\n";

    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "📱 FOLLOW US @420vallarta\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";

    // Social media icons for text receipt
    $social_icons = array(
        'instagram' => '📷',
        'facebook' => '👥',
        'twitter' => '🐦',
        'youtube' => '📺',
        'pinterest' => '📌'
    );

    foreach ($receipt_data['social_media'] as $platform => $handle) {
        $icon = isset($social_icons[$platform]) ? $social_icons[$platform] : '🌐';
        $text .= $icon . " " . ucfirst($platform) . ": " . $handle . "\n";
    }
    $text .= "\n";

    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "Thank you for choosing 420 Vallarta!\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";

    return $text;
}

/**
 * Get payment-specific information based on payment method
 */
function getPaymentInformation($payment_method, $client_id) {
    $payment_info = array();
    
    switch ($payment_method) {
        case 'Oxxo Transfer':
            $payment_info = array(
                'title' => 'Oxxo Transfer',
                'instructions' => 'You can make a deposit from any Oxxo location using this payment information.',
                'details' => array(
                    'Card Number' => '4152 3142 6559 7115',
                    'Bank' => 'BBVA',
                    'Reference' => $client_id
                ),
                'reference_note' => ''
            );
            break;

        case 'Bank Transfer':
            $payment_info = array(
                'title' => 'Bank Transfer',
                'instructions' => 'You can make a deposit from your banking app or any BBVA ATM using this payment information.',
                'details' => array(
                    'Card Number' => '4152 3142 6559 7115',
                    'Bank' => 'BBVA',
                    'Reference' => $client_id
                ),
                'reference_note' => ''
            );
            break;

        case 'Visa/MasterCard/American Express':
            $payment_info = array(
                'title' => 'Visa/MasterCard/American Express',
                'instructions' => 'You will receive a secure email from Stripe with payment instructions, there is no registration or login required.',
                'details' => array(),
                'reference_note' => ''
            );
            break;
            
        case 'Paypal':
            $payment_info = array(
                'title' => 'Paypal',
                'instructions' => 'Please send the total amount to this email:',
                'details' => array(
                    'PayPal Email' => 'pvblessings1@gmail.com'
                ),
                'reference_note' => 'Please do not mention 420 or anything cannabis related as payment platforms are sensitive to this. Refer to your client number (' . $client_id . ') as a reference.'
            );
            break;
            
        case 'ApplePay/Google Pay':
            $payment_info = array(
                'title' => 'ApplePay/Google Pay',
                'instructions' => 'You will receive an email from Stripe with payment instructions.',
                'details' => array(),
                'reference_note' => ''
            );
            break;

        default:
            $payment_info = array(
                'title' => 'Payment Information',
                'instructions' => 'Please contact us for payment instructions.',
                'details' => array(
                    'Client ID' => $client_id
                ),
                'reference_note' => ''
            );
            break;
    }
    
    return $payment_info;
}

// Initialize receipt log table
initializeReceiptLog();
?>