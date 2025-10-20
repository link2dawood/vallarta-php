<?php
// 420 Vallarta PDF Receipt Generation Functions

require_once('receipt_config.php');
require_once('inventory_functions.php'); // For parseOrderProducts function
require_once(__DIR__ . '/../vendor/autoload.php'); // Composer autoload for TCPDF

/**
 * Generate PDF receipt for an order
 */
function generatePDFReceipt($order_data, $custom_settings = array()) {
    global $con;

    // Load receipt configuration
    $config = getReceiptConfig();

    // Merge custom settings
    $settings = array_merge($config, $custom_settings);

    // Generate receipt ID
    $receipt_id = generateReceiptID($order_data['id']);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('420 Vallarta');
    $pdf->SetAuthor('420 Vallarta');
    $pdf->SetTitle('Order Receipt - ' . $receipt_id);
    $pdf->SetSubject('Order Receipt');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 20);

    // Add a page
    $pdf->AddPage();

    // Set font for main content
    $pdf->SetFont('helvetica', '', 10);

    // Build HTML content
    $html = buildReceiptHTML($order_data, $settings, $receipt_id);

    // Output HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Generate PDF as string
    $pdf_content = $pdf->Output('', 'S');

    return array(
        'pdf_content' => $pdf_content,
        'filename' => 'Receipt_' . $receipt_id . '.pdf',
        'receipt_id' => $receipt_id
    );
}

/**
 * Build HTML content for PDF receipt
 */
function buildReceiptHTML($order_data, $settings, $receipt_id) {
    // Parse order products
    $products = parseOrderProducts($order_data['total_products']);

    // Calculate totals
    $subtotal = calculateSubtotal($products);

    // Priority: custom_settings > database saved values > defaults
    $delivery_fee = isset($settings['delivery_fee']) ? $settings['delivery_fee'] :
                    (!empty($order_data['delivery_fee']) ? $order_data['delivery_fee'] : $settings['default_delivery_fee']);
    $discount = isset($settings['discount']) ? $settings['discount'] :
                (!empty($order_data['discount']) ? $order_data['discount'] : $settings['default_discount']);
    $refund = isset($settings['refund']) ? $settings['refund'] :
              (!empty($order_data['refund']) ? $order_data['refund'] : $settings['default_refund']);

    $total_mxn = $subtotal + $delivery_fee - $discount - $refund;
    $total_usd = convertMXNtoUSD($total_mxn);

    // Build complimentary items (handle JSON from database)
    if (isset($settings['complimentary_items'])) {
        $complimentary_items = $settings['complimentary_items'];
    } elseif (!empty($order_data['complimentary_items'])) {
        $decoded = json_decode($order_data['complimentary_items'], true);
        $complimentary_items = is_array($decoded) ? $decoded : $settings['default_complimentary_items'];
    } else {
        $complimentary_items = $settings['default_complimentary_items'];
    }

    $html = '
    <style>
        .header { text-align: center; margin-bottom: 20px; }
        .logo { text-align: center; margin-bottom: 10px; }
        .company-info { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
        .receipt-id { font-size: 18px; font-weight: bold; color: #2c5530; margin: 15px 0; }
        .section-title { font-size: 14px; font-weight: bold; color: #2c5530; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .customer-info { margin-bottom: 15px; }
        .info-row { margin-bottom: 5px; }
        .label { font-weight: bold; display: inline-block; width: 120px; }
        .products-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .products-table th, .products-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .products-table th { background-color: #f8f9fa; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #f0f7f0; }
        .currency-note { font-size: 9px; color: #666; text-align: center; margin: 5px 0; }
        .complimentary { background-color: #fff8dc; }
        .footer { margin-top: 30px; font-size: 9px; color: #666; text-align: center; }
        .social-handles { margin-top: 10px; text-align: center; font-size: 9px; }
        .eta-notice { background-color: #e6f3ff; padding: 10px; border-radius: 5px; margin: 15px 0; text-align: center; }
        .payment-link { background-color: #f0f7f0; padding: 10px; border-radius: 5px; margin: 15px 0; text-align: center; }
    </style>

    <div class="header">
        <div class="logo">
            <img src="' . $settings['logo_url'] . '" width="80" height="80" alt="420 Vallarta">
        </div>
        <h1 style="color: #2c5530; margin: 10px 0;">' . $settings['company_name'] . '</h1>
        <div class="company-info">
            ' . $settings['company_address'] . '<br>
            Phone: ' . $settings['company_phone'] . ' | Email: ' . $settings['company_email'] . '<br>
            Website: ' . $settings['company_website'] . '
        </div>
        <div class="receipt-id">Receipt ID: ' . $receipt_id . '</div>
        <div style="font-size: 10px; color: #666;">Order Date: ' . date('F j, Y - g:i A', strtotime($order_data['dat'])) . '</div>
    </div>

    <div class="section-title">Customer Information</div>
    <div class="customer-info">
        <div class="info-row"><span class="label">Name:</span>' . htmlspecialchars($order_data['name']) . '</div>
        <div class="info-row"><span class="label">Phone:</span>' . htmlspecialchars($order_data['number']) . '</div>
        <div class="info-row"><span class="label">Email:</span>' . htmlspecialchars($order_data['email']) . '</div>
        <div class="info-row"><span class="label">Payment Method:</span>' . htmlspecialchars($order_data['method']) . '</div>
        <div class="info-row"><span class="label">Client ID:</span>CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . '</div>
    </div>

    <div class="section-title">Delivery Address</div>
    <div style="margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
        ' . nl2br(htmlspecialchars(
            isset($settings['delivery_address']) ? $settings['delivery_address'] :
            (!empty($order_data['delivery_address_final']) ? $order_data['delivery_address_final'] : $order_data['adresse'])
        )) . '
    </div>';

    // Add products table
    $html .= '<div class="section-title">Ordered Products</div>
    <table class="products-table">
        <tr>
            <th width="50%">Product</th>
            <th width="15%">Quantity</th>
            <th width="17%">Unit Price (MXN)</th>
            <th width="18%">Total (MXN)</th>
        </tr>';

    foreach ($products as $product) {
        $line_total = $product['price'] * $product['quantity'];
        $html .= '<tr>
            <td>' . htmlspecialchars($product['name']) . '</td>
            <td>' . $product['quantity'] . '</td>
            <td>$' . number_format($product['price'], 2) . '</td>
            <td>$' . number_format($line_total, 2) . '</td>
        </tr>';
    }

    // Add financial summary
    $html .= '<tr class="total-row">
        <td colspan="3"><strong>Subtotal:</strong></td>
        <td><strong>$' . number_format($subtotal, 2) . ' MXN</strong></td>
    </tr>';

    if ($delivery_fee > 0) {
        $html .= '<tr>
            <td colspan="3">Delivery Fee:</td>
            <td>$' . number_format($delivery_fee, 2) . ' MXN</td>
        </tr>';
    }

    if ($discount > 0) {
        $html .= '<tr style="color: #d9534f;">
            <td colspan="3">Discount:</td>
            <td>-$' . number_format($discount, 2) . ' MXN</td>
        </tr>';
    }

    if ($refund > 0) {
        $html .= '<tr style="color: #d9534f;">
            <td colspan="3">Refund:</td>
            <td>-$' . number_format($refund, 2) . ' MXN</td>
        </tr>';
    }

    $html .= '<tr class="total-row" style="background-color: #2c5530; color: white;">
        <td colspan="3"><strong>GRAND TOTAL:</strong></td>
        <td><strong>$' . number_format($total_mxn, 2) . ' MXN</strong></td>
    </tr>
    </table>';

    // Add currency conversion
    $html .= '<div class="currency-note">
        <strong>USD Equivalent:</strong> $' . number_format($total_usd, 2) . ' USD
        (Exchange Rate: 1 USD = ' . $settings['exchange_rate'] . ' MXN)
    </div>';

    // Add complimentary items if any
    if (!empty($complimentary_items)) {
        $html .= '<div class="section-title">Complimentary Items</div>
        <table class="products-table">
            <tr class="complimentary">
                <th width="70%">Item</th>
                <th width="30%">Value</th>
            </tr>';

        foreach ($complimentary_items as $item => $value) {
            $html .= '<tr class="complimentary">
                <td>' . htmlspecialchars($item) . '</td>
                <td>' . htmlspecialchars($value) . '</td>
            </tr>';
        }

        $html .= '</table>';
    }

    // Add ETA information
    $eta = isset($settings['eta']) ? $settings['eta'] :
           (!empty($order_data['eta']) ? $order_data['eta'] : $settings['default_eta']);
    $html .= '<div class="eta-notice">
        <strong>📍 Estimated Delivery Time:</strong> ' . htmlspecialchars($eta) . '
    </div>';

    // Add payment-specific instructions based on selected method
    $payment_method = $order_data['method'];
    $html .= '<div class="section-title">Payment Instructions</div>';
    $html .= '<div class="payment-link">';

    switch($payment_method) {
        case 'Oxxo Transfer':
            $html .= '<strong>💳 Oxxo Transfer Instructions</strong><br>
                <div style="margin: 10px 0; text-align: left;">
                    <strong>You can make a deposit from any Oxxo location using this payment information:</strong><br><br>
                    <strong>Account Number:</strong> 4152 3142 6559 7115<br>
                    <strong>Bank:</strong> BBVA<br>
                    <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                    Send receipt photo via WhatsApp: ' . $settings['whatsapp_number'] . '
                </div>';
            break;

        case 'Bank Transfer':
            $html .= '<strong>🏦 Bank Transfer Instructions</strong><br>
                <div style="margin: 10px 0; text-align: left;">
                    <strong>You can make a deposit from your banking app or any BBVA ATM using this payment information:</strong><br><br>
                    <strong>Account Number:</strong> 4152 3142 6559 7115<br>
                    <strong>Bank:</strong> BBVA<br>
                    <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                    Send receipt to: info@420vallarta.com or WhatsApp: ' . $settings['whatsapp_number'] . '
                </div>';
            break;

        case 'Visa/MasterCard/American Express':
            $html .= '<strong>💳 Visa/MasterCard/American Express</strong><br>
                <div style="margin: 10px 0; text-align: left;">
                    <strong>You will receive a secure email from Stripe with payment instructions.</strong><br><br>
                    ✅ No registration or login required<br>
                    ✅ Simply click the link in the email and complete payment<br><br>
                    Questions? Contact us via WhatsApp: ' . $settings['whatsapp_number'] . '
                </div>';
            break;

        case 'Paypal':
            $html .= '<strong>💙 PayPal Payment</strong><br>
                <div style="margin: 10px 0; text-align: left;">
                    <strong>Please send the total amount to this email:</strong><br>
                    <strong>pvblessings1@gmail.com</strong><br><br>
                    <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                    <div style="background: #fff3cd; padding: 8px; border-radius: 5px;">
                        <strong>⚠️ Important:</strong> Please do not mention 420 or anything cannabis related as payment platforms are sensitive to this. Refer to your client number as a reference.
                    </div>
                </div>';
            break;

        case 'ApplePay/Google Pay':
            $html .= '<strong>📱 ApplePay/Google Pay</strong><br>
                <div style="margin: 10px 0; text-align: left;">
                    <strong>You will receive an email from Stripe with payment instructions.</strong><br><br>
                    ✅ It\'s all part of the same project<br>
                    ✅ No registration or login required<br>
                    ✅ One-tap payment with biometric authentication<br><br>
                    Questions? Contact us via WhatsApp: ' . $settings['whatsapp_number'] . '
                </div>';
            break;

        default:
            $html .= '<strong>💳 Payment Options:</strong><br>
                Visit: <a href="https://420vallarta.com/420 Vallarta Payment Options.php">420vallarta.com/payment-options</a><br>
                Or contact us via WhatsApp: ' . $settings['whatsapp_number'] . '';
            break;
    }

    $html .= '</div>';

    // Add footer
    $html .= '<div class="footer">
        <div>' . $settings['receipt_footer_text'] . '</div>
        <div class="social-handles">
            <strong>Follow us:</strong> ' .
            implode(' | ', array_map(function($platform, $handle) {
                return $platform . ': ' . $handle;
            }, array_keys($settings['social_handles']), $settings['social_handles'])) . '
        </div>
        <div style="margin-top: 10px; font-size: 8px;">
            Generated on: ' . date('Y-m-d H:i:s') . ' |
            Terms: ' . $settings['terms_url'] . ' |
            Privacy: ' . $settings['privacy_url'] . '
        </div>
    </div>';

    return $html;
}

/**
 * Calculate subtotal from products array
 */
function calculateSubtotal($products) {
    $subtotal = 0;
    foreach ($products as $product) {
        $subtotal += $product['price'] * $product['quantity'];
    }
    return $subtotal;
}

/**
 * Save PDF receipt to file system
 */
function savePDFReceipt($pdf_content, $filename) {
    $receipts_dir = __DIR__ . '/../receipts/';

    // Create receipts directory if it doesn't exist
    if (!file_exists($receipts_dir)) {
        mkdir($receipts_dir, 0755, true);
    }

    $file_path = $receipts_dir . $filename;
    $result = file_put_contents($file_path, $pdf_content);

    return $result !== false ? $file_path : false;
}

/**
 * Send email with PDF receipt attachment
 */
function sendEmailWithPDFReceipt($order_data, $pdf_content, $filename, $custom_settings = array()) {
    require_once(__DIR__ . '/../PHPMailer/PHPMailerAutoload.php');

    $config = getReceiptConfig();
    $settings = array_merge($config, $custom_settings);

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'order@420vallarta.com';
    $mail->Password = 'Darialheli12!';
    $mail->SMTPSecure = 'TLS';
    $mail->Port = 587;

    // Set sender and recipient
    $mail->setFrom($settings['receipt_from_email'], $settings['receipt_from_name']);
    $mail->addAddress($order_data['email'], $order_data['name']);
    $mail->addBCC('420vallarta@gmail.com'); // Admin copy

    // Email content
    $receipt_id = generateReceiptID($order_data['id']);
    $mail->Subject = str_replace('{order_id}', $receipt_id, $settings['receipt_subject']);

    // HTML email body
    $mail->isHTML(true);
    $mail->Body = buildEmailBody($order_data, $settings, $receipt_id);

    // Attach PDF
    $mail->addStringAttachment($pdf_content, $filename, 'base64', 'application/pdf');

    // Send email
    if ($mail->send()) {
        return array('success' => true, 'message' => 'Receipt sent successfully');
    } else {
        return array('success' => false, 'message' => 'Email send failed: ' . $mail->ErrorInfo);
    }
}

/**
 * Build email body HTML
 */
function buildEmailBody($order_data, $settings, $receipt_id) {
    $total_mxn = $order_data['total_price'];
    $total_usd = convertMXNtoUSD($total_mxn);

    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; background-color: #2c5530; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .footer { background-color: #e9ecef; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; }
            .highlight { color: #2c5530; font-weight: bold; }
            .total { font-size: 18px; color: #2c5530; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Thank You for Your Order!</h1>
                <h2>Receipt #' . $receipt_id . '</h2>
            </div>

            <div class="content">
                <p>Dear ' . htmlspecialchars($order_data['name']) . ',</p>

                <p>Thank you for your confidence in <strong>' . $settings['company_name'] . '</strong>!</p>

                <p>We have received your order and will contact you soon via WhatsApp to confirm your order and make delivery arrangements.</p>

                <h3>Order Summary:</h3>
                <p><strong>Order Total:</strong> <span class="total">$' . number_format($total_mxn, 2) . ' MXN / $' . number_format($total_usd, 2) . ' USD</span></p>
                <p><strong>Payment Method:</strong> ' . htmlspecialchars($order_data['method']) . '</p>
                <p><strong>Client ID:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . '</p>

                <h3>📄 Your Receipt</h3>
                <p>Please find your detailed receipt attached as a PDF document. This receipt includes:</p>
                <ul>
                    <li>✅ Complete itemized product list</li>
                    <li>✅ Delivery fee and address details</li>
                    <li>✅ Dual currency totals (MXN/USD)</li>
                    <li>✅ Complimentary items included</li>
                    <li>✅ Estimated delivery time</li>
                    <li>✅ Payment options and contact information</li>
                </ul>

                <h3>📱 Next Steps</h3>
                <p>Our team will contact you via WhatsApp at <strong>' . htmlspecialchars($order_data['number']) . '</strong> to:</p>
                <ul>
                    <li>Confirm your order details</li>
                    <li>Arrange delivery time and location</li>
                    <li>Process payment if needed</li>
                </ul>

                <h3>💳 Payment Instructions - ' . htmlspecialchars($order_data['method']) . '</h3>
                <div style="background: #f0f7f0; padding: 15px; border-radius: 5px; margin: 10px 0;">';

    // Add payment-specific instructions to email based on selected method
    switch($order_data['method']) {
        case 'Oxxo Transfer':
            $email_payment_instructions = '
                <strong>You can make a deposit from any Oxxo location using this payment information:</strong><br><br>
                <strong>Account Number:</strong> 4152 3142 6559 7115<br>
                <strong>Bank:</strong> BBVA<br>
                <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                Send receipt photo via WhatsApp: ' . $settings['whatsapp_number'];
            break;

        case 'Bank Transfer':
            $email_payment_instructions = '
                <strong>You can make a deposit from your banking app or any BBVA ATM using this payment information:</strong><br><br>
                <strong>Account Number:</strong> 4152 3142 6559 7115<br>
                <strong>Bank:</strong> BBVA<br>
                <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                Send receipt to: info@420vallarta.com or WhatsApp: ' . $settings['whatsapp_number'];
            break;

        case 'Visa/MasterCard/American Express':
            $email_payment_instructions = '
                <strong>You will receive a secure email from Stripe with payment instructions.</strong><br><br>
                ✅ No registration or login required<br>
                ✅ Simply click the link in the email and complete payment';
            break;

        case 'Paypal':
            $email_payment_instructions = '
                <strong>Please send the total amount to this email:</strong><br>
                <strong>pvblessings1@gmail.com</strong><br><br>
                <strong>Reference:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . ' (Your Client Number)<br><br>
                <div style="background: #fff3cd; padding: 10px; border-radius: 5px;">
                    <strong>⚠️ Important:</strong> Please do not mention 420 or anything cannabis related as payment platforms are sensitive to this. Refer to your client number as a reference.
                </div>';
            break;

        case 'ApplePay/Google Pay':
            $email_payment_instructions = '
                <strong>You will receive an email from Stripe with payment instructions.</strong><br><br>
                ✅ It\'s all part of the same project<br>
                ✅ No registration or login required<br>
                ✅ One-tap payment with biometric authentication';
            break;

        default:
            $email_payment_instructions = '
                <p>For payment options, visit: <a href="https://420vallarta.com/420 Vallarta Payment Options.php">420vallarta.com/payment-options</a></p>
                <p>Or contact us via WhatsApp: ' . $settings['whatsapp_number'] . '</p>';
            break;
    }

    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; background-color: #2c5530; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .footer { background-color: #e9ecef; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; }
            .highlight { color: #2c5530; font-weight: bold; }
            .total { font-size: 18px; color: #2c5530; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Thank You for Your Order!</h1>
                <h2>Receipt #' . $receipt_id . '</h2>
            </div>

            <div class="content">
                <p>Dear ' . htmlspecialchars($order_data['name']) . ',</p>

                <p>Thank you for your confidence in <strong>' . $settings['company_name'] . '</strong>!</p>

                <p>We have received your order and will contact you soon via WhatsApp to confirm your order and make delivery arrangements.</p>

                <h3>Order Summary:</h3>
                <p><strong>Order Total:</strong> <span class="total">$' . number_format($total_mxn, 2) . ' MXN / $' . number_format($total_usd, 2) . ' USD</span></p>
                <p><strong>Payment Method:</strong> ' . htmlspecialchars($order_data['method']) . '</p>
                <p><strong>Client ID:</strong> CV-' . str_pad($order_data['id'], 6, '0', STR_PAD_LEFT) . '</p>

                <h3>📄 Your Receipt</h3>
                <p>Please find your detailed receipt attached as a PDF document. This receipt includes:</p>
                <ul>
                    <li>✅ Complete itemized product list</li>
                    <li>✅ Delivery fee and address details</li>
                    <li>✅ Dual currency totals (MXN/USD)</li>
                    <li>✅ Complimentary items included</li>
                    <li>✅ Estimated delivery time</li>
                    <li>✅ Payment instructions for your selected method</li>
                </ul>

                <h3>💳 Payment Instructions - ' . htmlspecialchars($order_data['method']) . '</h3>
                <div style="background: #f0f7f0; padding: 15px; border-radius: 5px; margin: 10px 0;">
                    ' . $email_payment_instructions . '
                </div>

                <h3>📱 Next Steps</h3>
                <p>Our team will contact you via WhatsApp at <strong>' . htmlspecialchars($order_data['number']) . '</strong> to:</p>
                <ul>
                    <li>Confirm your order details</li>
                    <li>Arrange delivery time and location</li>
                    <li>Process payment if needed</li>
                </ul>

                <p>If you have any questions, please don\'t hesitate to contact us:</p>
                <ul>
                    <li><strong>WhatsApp:</strong> ' . $settings['whatsapp_number'] . '</li>
                    <li><strong>Email:</strong> ' . $settings['company_email'] . '</li>
                    <li><strong>Website:</strong> ' . $settings['company_website'] . '</li>
                </ul>
            </div>

            <div class="footer">
                <p><strong>' . $settings['company_name'] . '</strong><br>
                ' . $settings['company_address'] . '<br>
                Phone: ' . $settings['company_phone'] . '</p>

                <p><strong>Follow us on social media:</strong><br>
                ' . implode(' | ', $settings['social_handles']) . '</p>

                <p style="font-size: 12px; color: #666;">
                    This is an automated message. Please do not reply directly to this email.
                </p>
            </div>
        </div>
    </body>
    </html>';
}
?>