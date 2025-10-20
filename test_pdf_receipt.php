<?php
// Test PDF Receipt Generation - 420 Vallarta
require_once('settings/db.php');
require_once('settings/pdf_receipt_functions.php');
session_start();

// Only allow admin users to run this test
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    die('Access denied. Admin login required.');
}

$test_result = '';
$pdf_generated = false;

// Handle form submission
if ($_POST['action'] === 'generate_test_pdf') {
    // Create test order data
    $test_order_data = array(
        'id' => 9999, // Test order ID
        'name' => $_POST['customer_name'] ?: 'John Doe',
        'number' => $_POST['customer_phone'] ?: '+52 322 123 4567',
        'email' => $_POST['customer_email'] ?: 'test@example.com',
        'method' => $_POST['payment_method'] ?: 'Cash on Delivery',
        'adresse' => $_POST['delivery_address'] ?: 'Test Address, Puerto Vallarta, Jalisco, Mexico',
        'pin_code' => '12345',
        'total_products' => $_POST['order_products'] ?: 'OG Kush Premium (2), Sour Diesel (1), Live Resin Cart - Wedding Cake (1)',
        'total_price' => $_POST['total_price'] ?: '850',
        'dat' => date('Y-m-d H:i:s')
    );

    // Custom settings for test receipt
    $custom_settings = array(
        'delivery_fee' => floatval($_POST['delivery_fee'] ?: 100),
        'discount' => floatval($_POST['discount'] ?: 0),
        'refund' => floatval($_POST['refund'] ?: 0),
        'delivery_address' => $_POST['delivery_address'] ?: $test_order_data['adresse'],
        'eta' => $_POST['eta'] ?: '60-90 minutes',
        'complimentary_items' => array(
            'Rolling Papers' => 'Free',
            'Lighter' => 'Free',
            'Mints' => 'Free'
        )
    );

    try {
        // Generate PDF receipt
        $pdf_result = generatePDFReceipt($test_order_data, $custom_settings);

        if ($pdf_result && $pdf_result['pdf_content']) {
            $pdf_generated = true;
            $test_result = "<div class='alert alert-success'>✅ PDF Receipt generated successfully!</div>";
            $test_result .= "<div class='alert alert-info'>";
            $test_result .= "<strong>Receipt Details:</strong><br>";
            $test_result .= "• Receipt ID: " . $pdf_result['receipt_id'] . "<br>";
            $test_result .= "• Filename: " . $pdf_result['filename'] . "<br>";
            $test_result .= "• PDF Size: " . number_format(strlen($pdf_result['pdf_content']) / 1024, 2) . " KB<br>";
            $test_result .= "</div>";

            // Save PDF for download
            $temp_filename = 'test_receipt_' . time() . '.pdf';
            file_put_contents(__DIR__ . '/temp_' . $temp_filename, $pdf_result['pdf_content']);
            $pdf_download_link = 'temp_' . $temp_filename;

            // Test email sending if requested
            if (isset($_POST['send_email']) && $_POST['send_email'] === '1') {
                $email_result = sendEmailWithPDFReceipt($test_order_data, $pdf_result['pdf_content'], $pdf_result['filename'], $custom_settings);

                if ($email_result['success']) {
                    $test_result .= "<div class='alert alert-success'>✅ Email sent successfully to " . $test_order_data['email'] . "!</div>";
                } else {
                    $test_result .= "<div class='alert alert-danger'>❌ Email failed: " . $email_result['message'] . "</div>";
                }
            }

        } else {
            $test_result = "<div class='alert alert-danger'>❌ Failed to generate PDF receipt</div>";
        }

    } catch (Exception $e) {
        $test_result = "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Receipt Test - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header-logo { text-align: center; margin-bottom: 30px; }
        .test-section { margin-bottom: 30px; }
        .form-section { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="header-logo">
            <img src="images/PV emblem round.png" alt="420 Vallarta" width="80" height="80">
            <h1 class="text-success mt-2">PDF Receipt Test System</h1>
            <p class="text-muted">Test and preview the PDF receipt generation functionality</p>
        </div>

        <?php if ($test_result): ?>
            <div class="test-result mb-4">
                <?= $test_result ?>
                <?php if ($pdf_generated && isset($pdf_download_link)): ?>
                    <div class="mt-3">
                        <a href="<?= $pdf_download_link ?>" class="btn btn-primary" target="_blank">📄 Download Test PDF</a>
                        <a href="<?= $pdf_download_link ?>" class="btn btn-secondary" download>💾 Save PDF</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="test-section">
            <input type="hidden" name="action" value="generate_test_pdf">

            <div class="form-section">
                <h3 class="text-success mb-3">🛒 Order Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name"
                                   value="<?= $_POST['customer_name'] ?? 'Maria Rodriguez' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Phone</label>
                            <input type="text" class="form-control" name="customer_phone"
                                   value="<?= $_POST['customer_phone'] ?? '+52 322 271 7643' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Email</label>
                            <input type="email" class="form-control" name="customer_email"
                                   value="<?= $_POST['customer_email'] ?? 'test@420vallarta.com' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" name="payment_method">
                                <option value="Cash on Delivery" <?= ($_POST['payment_method'] ?? '') === 'Cash on Delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
                                <option value="Visa MasterCard Via Stripe" <?= ($_POST['payment_method'] ?? '') === 'Visa MasterCard Via Stripe' ? 'selected' : '' ?>>Credit Card</option>
                                <option value="PayPal" <?= ($_POST['payment_method'] ?? '') === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                                <option value="Bank Transfer" <?= ($_POST['payment_method'] ?? '') === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Price (MXN)</label>
                            <input type="number" class="form-control" name="total_price" step="0.01"
                                   value="<?= $_POST['total_price'] ?? '1250.00' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <textarea class="form-control" name="delivery_address" rows="3"><?= $_POST['delivery_address'] ?? 'Calle Francisco Villa 123, Colonia Centro, Puerto Vallarta, Jalisco, Mexico 48300' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="text-success mb-3">📦 Products & Pricing</h3>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Order Products</label>
                            <small class="form-text text-muted">Format: Product Name (Quantity), Product Name (Quantity)</small>
                            <textarea class="form-control" name="order_products" rows="3"><?= $_POST['order_products'] ?? 'OG Kush Premium (2), Sour Diesel Vape Cart (1), Live Resin - Wedding Cake (1), Glass Water Pipe - 12 inch (1)' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Delivery Fee (MXN)</label>
                            <input type="number" class="form-control" name="delivery_fee" step="0.01"
                                   value="<?= $_POST['delivery_fee'] ?? '100.00' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount (MXN)</label>
                            <input type="number" class="form-control" name="discount" step="0.01"
                                   value="<?= $_POST['discount'] ?? '50.00' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund (MXN)</label>
                            <input type="number" class="form-control" name="refund" step="0.01"
                                   value="<?= $_POST['refund'] ?? '0.00' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="text-success mb-3">🚚 Delivery Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Estimated Delivery Time</label>
                            <input type="text" class="form-control" name="eta"
                                   value="<?= $_POST['eta'] ?? '45-60 minutes' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Options</label><br>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_email" value="1"
                                       <?= isset($_POST['send_email']) ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    📧 Send test email with PDF attachment
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success btn-lg">
                    📄 Generate Test PDF Receipt
                </button>
                <a href="admin.php" class="btn btn-secondary btn-lg ms-3">
                    ⬅️ Back to Admin
                </a>
            </div>
        </form>

        <div class="form-section">
            <h3 class="text-info mb-3">ℹ️ Test Information</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>PDF Features Included:</h5>
                    <ul class="list-unstyled">
                        <li>✅ Company branding and logo</li>
                        <li>✅ Unique receipt ID generation</li>
                        <li>✅ Customer information display</li>
                        <li>✅ Itemized product listing</li>
                        <li>✅ Delivery fee, discount, refund handling</li>
                        <li>✅ Dual currency totals (MXN/USD)</li>
                        <li>✅ Complimentary items section</li>
                        <li>✅ Delivery address and ETA</li>
                        <li>✅ Payment options link</li>
                        <li>✅ Social media handles</li>
                        <li>✅ Professional footer with terms</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Email Features:</h5>
                    <ul class="list-unstyled">
                        <li>✅ HTML email template</li>
                        <li>✅ PDF attachment</li>
                        <li>✅ Customer and admin notification</li>
                        <li>✅ Mobile-responsive design</li>
                        <li>✅ Professional branding</li>
                        <li>✅ Order summary in email</li>
                        <li>✅ Contact information</li>
                        <li>✅ Next steps instructions</li>
                    </ul>

                    <h5 class="mt-4">Exchange Rate Info:</h5>
                    <p class="text-muted">
                        Current rate: <strong>1 USD = <?= getCurrentExchangeRate() ?> MXN</strong><br>
                        <small>Configurable via receipt settings</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>