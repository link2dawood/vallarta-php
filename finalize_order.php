<?php
// 420 Vallarta Order Finalization & E-Receipt System

require_once('settings/db.php');
require_once('settings/receipt_functions.php');
require_once('settings/whatsapp_invoice_functions.php');
session_start();

// Check if user is logged in (admin access)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('location: LogReg/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = array();
$success_message = '';

// Process order finalization
if (isset($_POST['finalize_order'])) {
    $order_id = intval($_POST['order_id']);

    // Custom receipt settings
    $custom_settings = array(
        'delivery_fee' => floatval($_POST['delivery_fee']),
        'discount' => floatval($_POST['discount']),
        'refund' => floatval($_POST['refund']),
        'eta' => trim($_POST['eta']),
        'delivery_address' => trim($_POST['delivery_address']),
        'client_number' => trim($_POST['client_number'])
    );

    // Process complimentary items
    $complimentary_items = array();
    if (isset($_POST['comp_item_names']) && isset($_POST['comp_item_values']) &&
        is_array($_POST['comp_item_names']) && is_array($_POST['comp_item_values'])) {
        $names = $_POST['comp_item_names'];
        $values = $_POST['comp_item_values'];

        // Combine names and values into key-value pairs
        for ($i = 0; $i < count($names); $i++) {
            $item_name = trim($names[$i]);
            $item_value = isset($values[$i]) ? trim($values[$i]) : '';

            if (!empty($item_name)) {
                $complimentary_items[$item_name] = $item_value;
            }
        }
    }
    $custom_settings['complimentary_items'] = $complimentary_items;

    // Debug log (can be removed later)
    error_log("Order $order_id - Complimentary items being saved: " . json_encode($complimentary_items));

    // Validation
    if (empty($custom_settings['delivery_address'])) {
        $errors[] = "Delivery address is required";
    }
    if (empty($custom_settings['eta'])) {
        $errors[] = "ETA is required";
    }
    if (empty($custom_settings['client_number'])) {
        $errors[] = "Client ID is required";
    }

    if (empty($errors)) {
        // Calculate final total
        $order_query = mysqli_query($con, "SELECT total_price FROM ordere WHERE id = $order_id");
        $order_data = mysqli_fetch_array($order_query);
        $subtotal = $order_data['total_price'];
        $final_total = $subtotal + $custom_settings['delivery_fee'] - $custom_settings['discount'] - $custom_settings['refund'];

        // Serialize complimentary items for storage
        $complimentary_json = json_encode($custom_settings['complimentary_items']);

        // Update order with all receipt details (force update even if already finalized)
        $finalize_query = "UPDATE ordere SET
            valid = 'Finalized',
            finalized_date = NOW(),
            delivery_fee = '{$custom_settings['delivery_fee']}',
            discount = '{$custom_settings['discount']}',
            refund = '{$custom_settings['refund']}',
            final_total = '$final_total',
            eta = '" . mysqli_real_escape_string($con, $custom_settings['eta']) . "',
            complimentary_items = '" . mysqli_real_escape_string($con, $complimentary_json) . "',
            delivery_address_final = '" . mysqli_real_escape_string($con, $custom_settings['delivery_address']) . "',
            client_number = '" . mysqli_real_escape_string($con, $custom_settings['client_number']) . "'
            WHERE id = $order_id";

        // Execute the query
        $finalize_result = mysqli_query($con, $finalize_query);

        if ($finalize_result) {
            // Check if the update actually modified any rows
            $affected_rows = mysqli_affected_rows($con);

            // Log the update
            error_log("Order $order_id - Update executed. Affected rows: $affected_rows. Query: $finalize_query");

            // Send e-receipt
            $receipt_result = sendEReceipt($order_id, $custom_settings);

            if ($receipt_result['success']) {
                $success_message = "Order finalized successfully! E-receipt sent to " . $receipt_result['email'] .
                                 " (Receipt ID: " . $receipt_result['receipt_id'] . "). Database updated: $affected_rows row(s) affected.";

                // Redirect to admin page with success message
                header("Location: admin.php?success=" . urlencode("Order #$order_id finalized and receipt sent to " . $receipt_result['email']));
                exit();
            } else {
                $errors[] = "Order finalized in database but e-receipt failed: " . $receipt_result['error'];
            }
        } else {
            error_log("Order $order_id - Update failed: " . mysqli_error($con));
            $errors[] = "Failed to finalize order: " . mysqli_error($con);
        }
    }
}

// Get order for finalization
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_data = null;

if ($order_id > 0) {
    $order_query = mysqli_query($con, "SELECT * FROM ordere WHERE id = $order_id");
    if ($order_query && mysqli_num_rows($order_query) > 0) {
        $order_data = mysqli_fetch_array($order_query);
    }
}

$config = getReceiptConfig();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalize Order & Send E-Receipt - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .receipt-preview { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; }
        .currency-display { font-family: monospace; }
        .social-handle { color: #2a561f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>📧 Finalize Order & Send E-Receipt</h1>
                    <a href="admin.php" class="btn btn-secondary">← Back to Orders</a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5>Please fix the following errors:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <h5>✅ Success!</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!$order_data): ?>
                    <div class="alert alert-warning">
                        <h5>Order Not Found</h5>
                        <p>Please select a valid order to finalize.</p>
                        <a href="admin.php" class="btn btn-primary">View Orders</a>
                    </div>
                <?php else: ?>

                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">📋 Order Summary - #<?php echo $order_data['id']; ?>
                            <?php if ($order_data['valid'] === 'Finalized' && !empty($order_data['final_total'])): ?>
                                <span class="badge bg-warning text-dark float-end">✓ Already Finalized</span>
                            <?php endif; ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if ($order_data['valid'] === 'Finalized' && !empty($order_data['finalized_date'])): ?>
                                <div class="alert alert-info mb-3">
                                    <strong>ℹ️ This order was previously finalized on <?php echo date('M j, Y g:i A', strtotime($order_data['finalized_date'])); ?></strong><br>
                                    You can re-finalize to update the receipt or make changes to the delivery fee, discount, or refund amounts.
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Customer:</strong> <?php echo htmlspecialchars($order_data['name']); ?><br>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($order_data['email']); ?><br>
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($order_data['number']); ?><br>
                                    <strong>Payment:</strong> <?php echo htmlspecialchars($order_data['method']); ?><br>
                                    <strong>Current Client ID:</strong> <span class="text-muted"><?php echo !empty($order_data['client_number']) ? 'CL-' . $order_data['client_number'] : 'Not set'; ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Order Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order_data['dat'])); ?><br>
                                    <strong>Status:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($order_data['valid']); ?></span><br>
                                    <strong>Products:</strong> <?php echo htmlspecialchars($order_data['total_products']); ?><br>
                                    <strong>Current Total:</strong> <span class="currency-display">$<?php echo number_format($order_data['total_price'], 2); ?> MXN</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- E-Receipt Configuration Form -->
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                        <div class="row">
                            <!-- Receipt Settings -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>🧾 E-Receipt Configuration</h5>
                                    </div>
                                    <div class="card-body">

                                        <!-- Financial Adjustments -->
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="delivery_fee" class="form-label">Delivery Fee (MXN)</label>
                                                <input type="number" step="0.01" class="form-control" id="delivery_fee" name="delivery_fee"
                                                       value="<?php echo !empty($order_data['delivery_fee']) ? $order_data['delivery_fee'] : $config['default_delivery_fee']; ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="discount" class="form-label">Discount (MXN)</label>
                                                <input type="number" step="0.01" class="form-control" id="discount" name="discount"
                                                       value="<?php echo !empty($order_data['discount']) ? $order_data['discount'] : '0.00'; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="refund" class="form-label">Refund (MXN)</label>
                                                <input type="number" step="0.01" class="form-control" id="refund" name="refund"
                                                       value="<?php echo !empty($order_data['refund']) ? $order_data['refund'] : '0.00'; ?>">
                                            </div>
                                        </div>

                                        <!-- Client Information -->
                                        <div class="mb-3">
                                            <label for="client_number" class="form-label">Client ID</label>
                                            <input type="text" class="form-control" id="client_number" name="client_number"
                                                   value="<?php echo !empty($order_data['client_number']) ? $order_data['client_number'] : ''; ?>"
                                                   placeholder="e.g., CL-101200" required>
                                            <div class="form-text">This will be displayed as CV-XXXXXX in the receipt</div>
                                        </div>

                                        <!-- Delivery Information -->
                                        <div class="mb-3">
                                            <label for="delivery_address" class="form-label">Delivery Address</label>
                                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="2" required><?php echo htmlspecialchars($order_data['adresse']); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="eta" class="form-label">Estimated Delivery Time</label>
                                            <input type="text" class="form-control" id="eta" name="eta"
                                                   value="<?php echo $config['default_eta']; ?>"
                                                   placeholder="e.g., 60-90 minutes" required>
                                        </div>

                                        <!-- Complimentary Items -->
                                        <div class="mb-3">
                                            <label class="form-label">🎁 Complimentary Items</label>
                                            <div id="complimentary-items">
                                                <?php
                                                // Load existing complimentary items if order is already finalized
                                                $display_items = array();
                                                if (!empty($order_data['complimentary_items'])) {
                                                    $decoded = json_decode($order_data['complimentary_items'], true);
                                                    if (is_array($decoded) && !empty($decoded)) {
                                                        $display_items = $decoded;
                                                    }
                                                }
                                                // If no saved items, show defaults
                                                if (empty($display_items)) {
                                                    $display_items = $config['default_complimentary_items'];
                                                }

                                                foreach ($display_items as $item => $note):
                                                ?>
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control" name="comp_item_names[]"
                                                               value="<?php echo htmlspecialchars($item); ?>" placeholder="Item name">
                                                        <input type="text" class="form-control" name="comp_item_values[]"
                                                               value="<?php echo htmlspecialchars($note); ?>" placeholder="Value/Note">
                                                        <button class="btn btn-outline-danger" type="button" onclick="removeCompItem(this)">×</button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="addCompItem()">+ Add Item</button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Preview & Actions -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>💰 Receipt Preview</h5>
                                    </div>
                                    <div class="card-body receipt-preview">
                                        <div class="text-center mb-3">
                                            <img src="images/PV emblem round.png" alt="420 Vallarta" style="max-width: 60px;">
                                            <h6 class="mt-2">420 Vallarta</h6>
                                        </div>

                                        <div id="total-preview">
                                            <?php if (!empty($order_data['final_total'])): ?>
                                                <strong>Previously Finalized Total:</strong><br>
                                                <span class="currency-display text-success fs-5">$<?php echo number_format($order_data['final_total'], 2); ?> MXN</span><br>
                                                <span class="currency-display text-muted">$<?php echo number_format(convertMXNtoUSD($order_data['final_total']), 2); ?> USD</span>
                                                <hr>
                                                <small class="text-muted">
                                                    <strong>Breakdown:</strong><br>
                                                    Subtotal: $<?php echo number_format($order_data['total_price'], 2); ?><br>
                                                    <?php if (!empty($order_data['delivery_fee'])): ?>
                                                    + Delivery: $<?php echo number_format($order_data['delivery_fee'], 2); ?><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($order_data['discount'])): ?>
                                                    - Discount: $<?php echo number_format($order_data['discount'], 2); ?><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($order_data['refund'])): ?>
                                                    - Refund: $<?php echo number_format($order_data['refund'], 2); ?><br>
                                                    <?php endif; ?>
                                                </small>
                                            <?php else: ?>
                                                <strong>Current Order Total:</strong><br>
                                                <span class="currency-display text-success fs-5">$<?php echo number_format($order_data['total_price'], 2); ?> MXN</span><br>
                                                <span class="currency-display text-muted">$<?php echo number_format(convertMXNtoUSD($order_data['total_price']), 2); ?> USD</span>
                                            <?php endif; ?>
                                        </div>

                                        <hr>

                                        <div class="small">
                                            <strong>Exchange Rate:</strong><br>
                                            1 USD = <?php echo number_format($config['exchange_rate'], 2); ?> MXN
                                        </div>

                                        <hr>

                                        <div class="social-handle text-center">
                                            <small>Follow us: @420vallarta</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3 d-grid gap-2">
                                    <button type="submit" name="finalize_order" class="btn btn-success btn-lg">
                                        📧 Finalize & Send E-Receipt
                                    </button>
                                    <a href="receipt.php?order_id=<?php echo $order_id; ?>" class="btn btn-info" target="_blank">
                                        👁️ Preview Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- WhatsApp Invoice Section -->
                    <?php if ($order_data): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">📱 WhatsApp Invoice</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                This plain-text invoice can be copied and pasted directly into WhatsApp messages. 
                                Perfect for quick sharing with customers!
                            </p>
                            
                            <?php
                            // Generate WhatsApp invoice
                            $whatsapp_settings = array(
                                'delivery_fee' => !empty($order_data['delivery_fee']) ? $order_data['delivery_fee'] : $config['default_delivery_fee'],
                                'discount' => !empty($order_data['discount']) ? $order_data['discount'] : 0,
                                'refund' => !empty($order_data['refund']) ? $order_data['refund'] : 0,
                                'eta' => !empty($order_data['eta']) ? $order_data['eta'] : $config['default_eta'],
                                'delivery_address' => !empty($order_data['delivery_address_final']) ? $order_data['delivery_address_final'] : $order_data['adresse'],
                                'client_number' => !empty($order_data['client_number']) ? $order_data['client_number'] : (100000 + $order_id)
                            );
                            
                            // Add complimentary items if available
                            if (!empty($order_data['complimentary_items'])) {
                                $decoded = json_decode($order_data['complimentary_items'], true);
                                if (is_array($decoded)) {
                                    $whatsapp_settings['complimentary_items'] = $decoded;
                                }
                            }
                            
                            $whatsapp_result = generateWhatsAppInvoice($order_id, $whatsapp_settings);
                            
                            if ($whatsapp_result['success']):
                            ?>
                            
                            <div class="position-relative">
                                <textarea 
                                    id="whatsapp-invoice" 
                                    class="form-control font-monospace" 
                                    style="font-size: 0.85rem; white-space: pre-wrap; min-height: 500px; background-color: #f8f9fa;" 
                                    readonly><?php echo htmlspecialchars($whatsapp_result['message']); ?></textarea>
                                
                                <button 
                                    type="button" 
                                    class="btn btn-success mt-3" 
                                    id="copy-whatsapp-btn"
                                    onclick="copyWhatsAppInvoice()">
                                    📋 Copy to Clipboard
                                </button>
                                
                                <button 
                                    type="button" 
                                    class="btn btn-outline-success mt-3 ms-2" 
                                    onclick="openWhatsAppWeb()">
                                    💬 Open WhatsApp Web
                                </button>
                                
                                <div id="copy-success" class="alert alert-success mt-3" style="display:none;">
                                    ✅ WhatsApp invoice copied to clipboard! Now paste it in WhatsApp.
                                </div>
                            </div>
                            
                            <?php else: ?>
                            <div class="alert alert-danger">
                                ❌ Error generating WhatsApp invoice: <?php echo htmlspecialchars($whatsapp_result['error']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyWhatsAppInvoice() {
            const textarea = document.getElementById('whatsapp-invoice');
            const copyBtn = document.getElementById('copy-whatsapp-btn');
            const successAlert = document.getElementById('copy-success');
            
            // Select and copy
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                
                // Show success message
                successAlert.style.display = 'block';
                copyBtn.innerHTML = '✅ Copied!';
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-primary');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    copyBtn.innerHTML = '📋 Copy to Clipboard';
                    copyBtn.classList.remove('btn-primary');
                    copyBtn.classList.add('btn-success');
                    successAlert.style.display = 'none';
                }, 3000);
                
            } catch (err) {
                alert('Failed to copy: ' + err);
            }
            
            // Deselect
            window.getSelection().removeAllRanges();
        }
        
        function openWhatsAppWeb() {
            window.open('https://web.whatsapp.com/', '_blank');
        }
    </script>
    
    <script>
        // Dynamic complimentary items management
        function addCompItem() {
            const container = document.getElementById('complimentary-items');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="comp_item_names[]" placeholder="Item name">
                <input type="text" class="form-control" name="comp_item_values[]" placeholder="Value/Note">
                <button class="btn btn-outline-danger" type="button" onclick="removeCompItem(this)">×</button>
            `;
            container.appendChild(div);
        }

        function removeCompItem(button) {
            button.closest('.input-group').remove();
        }

        // Dynamic total calculation
        function updateTotalPreview() {
            const baseTotal = <?php echo $order_data ? $order_data['total_price'] : 0; ?>;
            const deliveryFee = parseFloat(document.getElementById('delivery_fee').value) || 0;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const refund = parseFloat(document.getElementById('refund').value) || 0;

            const newTotal = baseTotal + deliveryFee - discount - refund;
            const usdTotal = newTotal / <?php echo $config['exchange_rate']; ?>;

            document.getElementById('total-preview').innerHTML = `
                <strong>Updated Total:</strong><br>
                <span class="currency-display text-success fs-5">$${newTotal.toFixed(2)} MXN</span><br>
                <span class="currency-display text-muted">$${usdTotal.toFixed(2)} USD</span>
            `;
        }

        // Add event listeners for real-time calculation
        document.getElementById('delivery_fee').addEventListener('input', updateTotalPreview);
        document.getElementById('discount').addEventListener('input', updateTotalPreview);
        document.getElementById('refund').addEventListener('input', updateTotalPreview);
    </script>
</body>
</html>