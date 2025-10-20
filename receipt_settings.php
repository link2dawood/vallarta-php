<?php
// 420 Vallarta E-Receipt Settings Panel

require_once('settings/db.php');
require_once('settings/receipt_config.php');
session_start();

// Check if user is logged in (admin access)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('location: LogReg/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user is an administrator
// First check the users table (admin panel users)
$user_query = mysqli_query($con, "SELECT `admin?` as is_admin FROM users WHERE id = '$user_id'");
if ($user_query && mysqli_num_rows($user_query) > 0) {
    // Admin panel user
    $user_data = mysqli_fetch_array($user_query);
    if ($user_data['is_admin'] != 'yes') {
        header('location: admin.php?error=' . urlencode('Access denied. Only administrators can access receipt settings.'));
        exit();
    }
} else {
    // Check user_info table (regular users with roles)
    $user_query = mysqli_query($con, "SELECT role FROM user_info WHERE id = '$user_id'");
    if (!$user_query || mysqli_num_rows($user_query) == 0) {
        header('location: LogReg/login.php');
        exit();
    }

    $user_data = mysqli_fetch_array($user_query);
    $user_role = $user_data['role'];

    // Only allow administrators (role 1 or 2) to access this page
    if ($user_role != 1 && $user_role != 2) {
        header('location: admin.php?error=' . urlencode('Access denied. Only administrators can access receipt settings.'));
        exit();
    }
}

$errors = array();
$success_message = '';

// Process settings update
if (isset($_POST['update_settings'])) {
    $exchange_rate = floatval($_POST['exchange_rate']);
    $default_delivery_fee = floatval($_POST['default_delivery_fee']);
    $default_eta = trim($_POST['default_eta']);

    // Validation
    if ($exchange_rate <= 0) {
        $errors[] = "Exchange rate must be greater than 0";
    }
    if ($default_delivery_fee < 0) {
        $errors[] = "Delivery fee cannot be negative";
    }
    if (empty($default_eta)) {
        $errors[] = "Default ETA is required";
    }

    if (empty($errors)) {
        $update_query = "UPDATE receipt_settings SET
                        exchange_rate = '$exchange_rate',
                        default_delivery_fee = '$default_delivery_fee',
                        default_eta = '" . mysqli_real_escape_string($con, $default_eta) . "'
                        WHERE id = 1";

        if (mysqli_query($con, $update_query)) {
            $success_message = "Receipt settings updated successfully!";
            // Reload settings
            loadReceiptSettings();
        } else {
            $errors[] = "Database error: " . mysqli_error($con);
        }
    }
}

// Get current settings
$config = getReceiptConfig();
$settings_query = mysqli_query($con, "SELECT * FROM receipt_settings WHERE id = 1");
$current_settings = mysqli_fetch_array($settings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Receipt Settings - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>⚙️ E-Receipt System Settings</h1>
                    <a href="admin/" class="btn btn-secondary">← Back to Admin</a>
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

                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>📋 Receipt Configuration</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">

                                    <!-- Currency Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="text-primary">💱 Currency Settings</h5>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="exchange_rate" class="form-label">Exchange Rate (MXN per USD)</label>
                                            <input type="number" step="0.0001" class="form-control" id="exchange_rate" name="exchange_rate"
                                                   value="<?php echo $current_settings['exchange_rate']; ?>" required>
                                            <small class="text-muted">Current: 1 USD = <?php echo number_format($current_settings['exchange_rate'], 4); ?> MXN</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Primary Currency</label>
                                            <input type="text" class="form-control" value="MXN (Mexican Peso)" readonly>
                                            <small class="text-muted">Secondary: USD (US Dollar)</small>
                                        </div>
                                    </div>

                                    <!-- Default Values -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="text-primary">🚚 Default Values</h5>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="default_delivery_fee" class="form-label">Default Delivery Fee (MXN)</label>
                                            <input type="number" step="0.01" class="form-control" id="default_delivery_fee" name="default_delivery_fee"
                                                   value="<?php echo $current_settings['default_delivery_fee']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="default_eta" class="form-label">Default ETA</label>
                                            <input type="text" class="form-control" id="default_eta" name="default_eta"
                                                   value="<?php echo htmlspecialchars($current_settings['default_eta']); ?>" required>
                                        </div>
                                    </div>

                                    <!-- Company Information (Read-only) -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="text-primary">🏢 Company Information</h5>
                                            <small class="text-muted">These values are configured in the system files</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" value="<?php echo $config['company_name']; ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="text" class="form-control" value="<?php echo $config['company_email']; ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" value="<?php echo $config['company_phone']; ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Website</label>
                                            <input type="text" class="form-control" value="<?php echo $config['company_website']; ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- Social Media -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="text-primary">📱 Social Media Handles</h5>
                                            <small class="text-muted">These appear in the receipt footer</small>
                                        </div>
                                        <?php foreach ($config['social_handles'] as $platform => $handle): ?>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label"><?php echo ucfirst($platform); ?></label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($handle); ?>" readonly>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" name="update_settings" class="btn btn-success btn-lg">
                                            💾 Update Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Info -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>🎯 Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="receipt.php?order_id=1" class="btn btn-info" target="_blank">
                                        👁️ Receipt Preview
                                    </a>
                                    <a href="admin.php" class="btn btn-primary">
                                        📋 View Orders
                                    </a>
                                    <a href="receipt_log.php" class="btn btn-secondary">
                                        📜 Receipt Log
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Current Settings Summary -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>📊 Current Settings</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Exchange Rate:</strong></td>
                                        <td><?php echo number_format($current_settings['exchange_rate'], 4); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Delivery Fee:</strong></td>
                                        <td><?php echo formatCurrency($current_settings['default_delivery_fee'], 'MXN'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Default ETA:</strong></td>
                                        <td><?php echo htmlspecialchars($current_settings['default_eta']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($current_settings['last_updated'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Exchange Rate Calculator -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>🧮 Currency Calculator</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="mxn_amount" class="form-label">MXN Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="mxn_amount" placeholder="Enter MXN amount">
                                </div>
                                <div class="mb-3">
                                    <label for="usd_result" class="form-label">USD Equivalent</label>
                                    <input type="text" class="form-control" id="usd_result" readonly>
                                </div>
                                <button onclick="calculateCurrency()" class="btn btn-outline-primary btn-sm">Calculate</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calculateCurrency() {
            const mxnAmount = parseFloat(document.getElementById('mxn_amount').value);
            const exchangeRate = parseFloat(document.getElementById('exchange_rate').value);

            if (!isNaN(mxnAmount) && !isNaN(exchangeRate) && exchangeRate > 0) {
                const usdAmount = mxnAmount / exchangeRate;
                document.getElementById('usd_result').value = '$' + usdAmount.toFixed(2) + ' USD';
            } else {
                document.getElementById('usd_result').value = 'Invalid input';
            }
        }

        // Auto-calculate when MXN amount changes
        document.getElementById('mxn_amount').addEventListener('input', calculateCurrency);
        document.getElementById('exchange_rate').addEventListener('input', calculateCurrency);
    </script>
</body>
</html>