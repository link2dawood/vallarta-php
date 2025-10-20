<?php
// 420 Vallarta E-Receipt Test & Preview System

require_once('settings/db.php');
require_once('settings/receipt_functions.php');
session_start();

// Allow public access to preview and text modes
// Only require login for the test interface
$is_preview = isset($_GET['preview']) || isset($_GET['text']);
if (!$is_preview) {
    // Check if user is logged in (admin access) for test interface
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        die('Access denied. Admin login required for test interface.');
    }
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$preview_mode = isset($_GET['preview']) ? true : false;

if ($order_id <= 0) {
    die('Invalid order ID');
}

// Custom settings for testing
// Only override database values if explicitly provided in URL
$custom_settings = array();
if (isset($_GET['delivery_fee'])) {
    $custom_settings['delivery_fee'] = floatval($_GET['delivery_fee']);
}
if (isset($_GET['discount'])) {
    $custom_settings['discount'] = floatval($_GET['discount']);
}
if (isset($_GET['refund'])) {
    $custom_settings['refund'] = floatval($_GET['refund']);
}
if (isset($_GET['eta'])) {
    $custom_settings['eta'] = $_GET['eta'];
}
if (isset($_GET['delivery_address'])) {
    $custom_settings['delivery_address'] = $_GET['delivery_address'];
}

// Generate receipt data
$receipt_data = generateReceiptData($order_id, $custom_settings);

if (!$receipt_data) {
    die('Order not found or error generating receipt');
}

// If preview mode, show the HTML receipt directly
if ($preview_mode) {
    echo generateReceiptHTML($receipt_data);
    exit();
}

// If text mode, show the text receipt
if (isset($_GET['text'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo generateTextReceipt($receipt_data);
    exit();
}

// Otherwise, show the test interface
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Receipt Test System - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .receipt-frame { height: 600px; border: 2px solid #ddd; border-radius: 8px; }
        .test-controls { background: #f8f9fa; padding: 20px; border-radius: 8px; }
        .json-output { background: #f1f3f4; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Controls Panel -->
            <div class="col-md-4">
                <div class="test-controls">
                    <h3>🧪 E-Receipt Test System</h3>

                    <div class="mb-3">
                        <h5>Order Information</h5>
                        <p><strong>Order ID:</strong> #<?php echo $receipt_data['order_id']; ?></p>
                        <p><strong>Receipt ID:</strong> <?php echo $receipt_data['receipt_id']; ?></p>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($receipt_data['client_info']['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($receipt_data['client_info']['email']); ?></p>
                    </div>

                    <!-- Test Settings Form -->
                    <form method="GET" action="" class="mb-3">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                        <div class="mb-3">
                            <label for="delivery_fee" class="form-label">Delivery Fee (MXN)</label>
                            <input type="number" step="0.01" class="form-control" id="delivery_fee" name="delivery_fee"
                                   value="<?php echo $receipt_data['financial_summary']['delivery_fee_mxn']; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label">Discount (MXN)</label>
                            <input type="number" step="0.01" class="form-control" id="discount" name="discount"
                                   value="<?php echo $receipt_data['financial_summary']['discount_mxn']; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="refund" class="form-label">Refund (MXN)</label>
                            <input type="number" step="0.01" class="form-control" id="refund" name="refund"
                                   value="<?php echo $receipt_data['financial_summary']['refund_mxn']; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="eta" class="form-label">ETA</label>
                            <input type="text" class="form-control" id="eta" name="eta"
                                   value="<?php echo htmlspecialchars($receipt_data['order_details']['eta']); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Update Preview</button>
                    </form>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="?order_id=<?php echo $order_id; ?>&preview=1<?php
                           if (!empty($custom_settings)) {
                               echo '&' . http_build_query($custom_settings);
                           }
                        ?>"
                           target="_blank" class="btn btn-success">
                            👁️ Full Preview
                        </a>

                        <button onclick="viewTextReceipt()" class="btn btn-primary">
                            📱 View Text Receipt (WhatsApp)
                        </button>

                        <button onclick="sendTestReceipt()" class="btn btn-warning">
                            📧 Send Test Receipt
                        </button>

                        <a href="finalize_order.php?order_id=<?php echo $order_id; ?>" class="btn btn-info">
                            ⚙️ Finalize Order
                        </a>

                        <a href="admin.php" class="btn btn-secondary">
                            ← Back to Orders
                        </a>
                    </div>

                    <!-- Financial Summary -->
                    <div class="mt-4">
                        <h5>💰 Financial Summary</h5>
                        <table class="table table-sm">
                            <tr>
                                <td>Subtotal:</td>
                                <td>
                                    <?php echo formatCurrency($receipt_data['financial_summary']['subtotal_mxn'], 'MXN'); ?><br>
                                    <small class="text-muted"><?php echo formatCurrency($receipt_data['financial_summary']['subtotal_usd'], 'USD'); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <td>Delivery Fee:</td>
                                <td>
                                    <?php echo formatCurrency($receipt_data['financial_summary']['delivery_fee_mxn'], 'MXN'); ?><br>
                                    <small class="text-muted"><?php echo formatCurrency($receipt_data['financial_summary']['delivery_fee_usd'], 'USD'); ?></small>
                                </td>
                            </tr>
                            <?php if ($receipt_data['financial_summary']['discount_mxn'] > 0): ?>
                            <tr>
                                <td>Discount:</td>
                                <td class="text-success">
                                    -<?php echo formatCurrency($receipt_data['financial_summary']['discount_mxn'], 'MXN'); ?><br>
                                    <small class="text-muted">-<?php echo formatCurrency($receipt_data['financial_summary']['discount_usd'], 'USD'); ?></small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($receipt_data['financial_summary']['refund_mxn'] > 0): ?>
                            <tr>
                                <td>Refund:</td>
                                <td class="text-info">
                                    -<?php echo formatCurrency($receipt_data['financial_summary']['refund_mxn'], 'MXN'); ?><br>
                                    <small class="text-muted">-<?php echo formatCurrency($receipt_data['financial_summary']['refund_usd'], 'USD'); ?></small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-success">
                                <td><strong>TOTAL:</strong></td>
                                <td>
                                    <strong><?php echo formatCurrency($receipt_data['financial_summary']['total_mxn'], 'MXN'); ?></strong><br>
                                    <strong><?php echo formatCurrency($receipt_data['financial_summary']['total_usd'], 'USD'); ?></strong>
                                </td>
                            </tr>
                        </table>
                        <small class="text-muted">Exchange Rate: 1 USD = <?php echo number_format($receipt_data['exchange_rate'], 2); ?> MXN</small>
                    </div>
                </div>
            </div>

            <!-- Receipt Preview -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>📄 Receipt Preview</h5>
                        <small class="text-muted">Receipt ID: <?php echo $receipt_data['receipt_id']; ?></small>
                    </div>
                    <div class="card-body p-0">
                        <iframe src="?order_id=<?php echo $order_id; ?>&preview=1<?php
                           if (!empty($custom_settings)) {
                               echo '&' . http_build_query($custom_settings);
                           }
                        ?>"
                                class="receipt-frame w-100"></iframe>
                    </div>
                </div>

                <!-- Receipt Data (for debugging) -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6>🔍 Receipt Data (JSON)</h6>
                    </div>
                    <div class="card-body">
                        <div class="json-output">
                            <?php echo json_encode($receipt_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewTextReceipt() {
            const orderId = <?php echo $order_id; ?>;
            const settings = {
                delivery_fee: document.getElementById('delivery_fee').value,
                discount: document.getElementById('discount').value,
                refund: document.getElementById('refund').value,
                eta: document.getElementById('eta').value
            };

            // Build URL with parameters
            let url = '?order_id=' + orderId + '&text=1';
            Object.keys(settings).forEach(key => {
                if (settings[key]) {
                    url += '&' + key + '=' + encodeURIComponent(settings[key]);
                }
            });

            // Open in new window
            window.open(url, '_blank');
        }

        function sendTestReceipt() {
            const orderId = <?php echo $order_id; ?>;
            const settings = {
                delivery_fee: document.getElementById('delivery_fee').value,
                discount: document.getElementById('discount').value,
                refund: document.getElementById('refund').value,
                eta: document.getElementById('eta').value
            };

            if (confirm('Send test e-receipt to customer email?')) {
                // Create a form and submit it to send the receipt
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'send_receipt.php';

                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = 'order_id';
                orderInput.value = orderId;
                form.appendChild(orderInput);

                Object.keys(settings).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = settings[key];
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>