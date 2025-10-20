<?php
// Disable error display in production (errors are logged instead)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('settings/db.php');
require_once('settings/inventory_functions.php');
require_once('settings/pdf_receipt_functions.php');
session_start();

// Initialize variables
$errors = array();
$order_data = null;
$inventory_changes = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:LogReg/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate order ID parameter
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location:admin.php?error=invalid_order_id');
    exit();
}

$order_id = intval($_GET['id']);

// Check user permissions
$select = mysqli_query($con, "SELECT * FROM user_info WHERE id = '$user_id' OR role=1");
if(mysqli_num_rows($select) == 0) {
    header('location:LogReg/login.php'); 
    exit();
}

// Get order data
$query_get_data = mysqli_query($con, "SELECT * FROM ordere WHERE id = '$order_id'");
if(!$query_get_data || mysqli_num_rows($query_get_data) == 0) {
    header('location:admin.php?error=order_not_found');
    exit();
}
$order_data = mysqli_fetch_array($query_get_data);

// Fetch available products
$products = array();
$products_sql = "SELECT movie_id, title, price FROM movies ORDER BY title ASC";
$products_query = mysqli_query($con, $products_sql);
if (!$products_query) {
    die("Products query failed: " . mysqli_error($con) . "<br>SQL: " . $products_sql);
}
while ($product = mysqli_fetch_assoc($products_query)) {
    $products[] = $product;
}

// Process form submission
if(isset($_POST['done'])) {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $adresse = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';
    $order = isset($_POST['order']) ? trim($_POST['order']) : '';
    $method = isset($_POST['method']) ? trim($_POST['method']) : '';
    $total_price = isset($_POST['total_price']) ? trim($_POST['total_price']) : '';
    $client_number = isset($_POST['client_number']) ? intval($_POST['client_number']) : 0;

    // Validate new order stock levels
    $stock_issues = validateOrderStock($order);
    if (!empty($stock_issues)) {
        foreach ($stock_issues as $issue) {
            $errors[] = "Insufficient stock for {$issue['product']}: requested {$issue['requested']}, available {$issue['available']}";
        }
    }

    // Basic validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($adresse)) {
        $errors[] = "Address is required";
    }
    if (empty($order)) {
        $errors[] = "Order details are required";
    }
    if (empty($method)) {
        $errors[] = "Payment method is required";
    }
    if (empty($total_price)) {
        $errors[] = "Total price is required";
    }
    if ($client_number < 101200) {
        $errors[] = "Client number must be 101200 or higher";
    }

    // If no validation errors, proceed with update
    if (empty($errors)) {
        // Escape strings to prevent SQL injection
        $username = mysqli_real_escape_string($con, $username);
        $adresse = mysqli_real_escape_string($con, $adresse);
        $order = mysqli_real_escape_string($con, $order);
        $method = mysqli_real_escape_string($con, $method);
        $total_price = mysqli_real_escape_string($con, $total_price);

        $update_query = "UPDATE ordere SET name='$username', adresse='$adresse', total_products='$order', method='$method', total_price='$total_price', client_number='$client_number' WHERE id = $order_id";
        $update_result = mysqli_query($con, $update_query);

        if($update_result) {
            // Debug: Log the order change parameters
            error_log("Order Edit Debug - Order ID: $order_id, User ID: $user_id");
            error_log("Old Order: " . $order_data['total_products']);
            error_log("New Order: " . $order);

            // Update inventory based on order changes
            $inventory_changes = updateInventoryOnOrderChange($order_id, $order_data['total_products'], $order, $user_id);

            // Debug: Log inventory changes result
            error_log("Inventory Changes Result: " . json_encode($inventory_changes));

            // Get updated order data for email
            $updated_order_query = mysqli_query($con, "SELECT * FROM ordere WHERE id = '$order_id'");
            $updated_order_data = mysqli_fetch_array($updated_order_query);

            // Prepare custom settings for receipt
            // Only set values that should override database values
            $custom_settings = array(
                'delivery_address' => $adresse,
                'complimentary_items' => array(
                    'Rolling Papers' => 'Free',
                    'Lighter' => 'Free',
                    'Mints' => 'Free'
                )
            );

            // Only include delivery_fee, discount, refund if they exist in the database
            // This allows the receipt to use saved values from finalize_order
            if (!empty($updated_order_data['delivery_fee'])) {
                $custom_settings['delivery_fee'] = $updated_order_data['delivery_fee'];
            }
            if (!empty($updated_order_data['discount'])) {
                $custom_settings['discount'] = $updated_order_data['discount'];
            }
            if (!empty($updated_order_data['refund'])) {
                $custom_settings['refund'] = $updated_order_data['refund'];
            }
            if (!empty($updated_order_data['eta'])) {
                $custom_settings['eta'] = $updated_order_data['eta'];
            }

            // Send PDF receipt email
            $receipt_sent = false;
            try {
                require_once('PHPMailer/PHPMailerAutoload.php');

                // Generate PDF receipt
                $pdf_result = generatePDFReceipt($updated_order_data, $custom_settings);

                if ($pdf_result && $pdf_result['pdf_content']) {
                    // Send email with PDF attachment
                    $email_result = sendEmailWithPDFReceipt($updated_order_data, $pdf_result['pdf_content'], $pdf_result['filename'], $custom_settings);

                    if ($email_result['success']) {
                        $receipt_sent = true;
                        error_log("PDF Receipt sent for Order ID: " . $order_id);
                    } else {
                        error_log("Failed to send PDF receipt: " . $email_result['message']);
                    }
                }
            } catch (Exception $e) {
                error_log("PDF Receipt error: " . $e->getMessage());
            }

            if (mysqli_affected_rows($con) > 0 || !empty($inventory_changes)) {
                $success_message = "Order updated successfully";
                if (!empty($inventory_changes)) {
                    $success_message .= " and inventory automatically updated (" . count($inventory_changes) . " products affected)";
                }
                if ($receipt_sent) {
                    $success_message .= ". Updated receipt sent to " . $updated_order_data['email'];
                }
                header('location:admin.php?success=' . urlencode($success_message));
                exit();
            } else {
                $errors[] = "No changes were made to the order";
            }
        } else {
            $errors[] = "Database error: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Edit Order</title>
    <style type="text/css">
.style1 {color: #FFFFFF}
.style2 {color: #000000}
    </style>
</head>
<body>
   <table width="100%" border="1" bgcolor="#000000">
     <tr>
       <td height="226">
           <h2 align="center"><img src="/images/lotus flower.png" alt="420 Lotus" width="55" height="55" align="right"><br><br></h2>
           <h2 align="center" class="style1"><img src="/images/PV emblem round.png" alt="420 Vallarta" width="160" height="160" align="middle"><br><br></h2>
       </td>
     </tr>
   </table>
   <h2 align="center" class="style2">420 Vallarta Orders</h2>
   <br><br>
   <a href="inventory/index.php"><img src="images/420 vallarta inventory icon.png" alt="Product Inventory 420 Vallarta" width="84" height="84" border="0"></a> 
   <a href="admin.php"><img src="images/420 Vallarta Orders Icon.png" alt="Client Orders 420 Vallarta" width="83" height="83" border="0"></a> 
   <a href="http://420vallarta.com/movie.php" target="_blank"><img src="/images/cashier 420 vallarta.png" alt="Cashier 420 Vallarta" name="Cashier" width="85" height="85" border="0" id="Cashier"></a>
   <br><br>

<?php
// Display error messages
if (!empty($errors)) {
    echo '<div class="alert alert-danger" role="alert">';
    echo '<h5>Please fix the following errors:</h5>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Order #<?php echo htmlspecialchars($order_data['id']); ?></h4>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="client_number" class="form-label">Client Number *</label>
                            <input type="number" class="form-control" id="client_number" name="client_number"
                                   value="<?php echo htmlspecialchars($order_data['client_number']); ?>"
                                   min="101200" required>
                            <small class="form-text text-muted">Must be 101200 or higher. Edit this to add old clients.</small>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Customer Name *</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?php echo htmlspecialchars($order_data['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Address *</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3" required><?php echo htmlspecialchars($order_data['adresse']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="order" class="form-label">Order Details *</label>
                            <small class="form-text text-muted mb-2 d-block">Select products and quantities from the available products</small>
                            <div id="product-selector" class="mb-3">
                                <!-- Product selection will be loaded here -->
                            </div>
                            <input type="hidden" id="order" name="order" value="<?php echo htmlspecialchars($order_data['total_products']); ?>" required>
                            <small class="form-text text-warning mt-1">⚠️ Changing quantities will automatically update inventory levels</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Additional Order Information</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($order_data['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="number" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="number" 
                                           value="<?php echo htmlspecialchars($order_data['number']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="method" class="form-label">Payment Method *</label>
                                    <select class="form-control" id="method" name="method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Oxxo Transfer" <?php echo ($order_data['method'] == 'Oxxo Transfer') ? 'selected' : ''; ?>>Oxxo Transfer</option>
                                        <option value="Bank Transfer" <?php echo ($order_data['method'] == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="Visa/MasterCard/American Express" <?php echo ($order_data['method'] == 'Visa/MasterCard/American Express') ? 'selected' : ''; ?>>Visa/MasterCard/American Express</option>
                                        <option value="Paypal" <?php echo ($order_data['method'] == 'Paypal') ? 'selected' : ''; ?>>Paypal</option>
                                        <option value="ApplePay/Google Pay" <?php echo ($order_data['method'] == 'ApplePay/Google Pay') ? 'selected' : ''; ?>>ApplePay/Google Pay</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="total_price" class="form-label">Total Price *</label>
                                    <input type="number" step="0.01" class="form-control" id="total_price" name="total_price"
                                           value="<?php echo htmlspecialchars($order_data['total_price']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="admin.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" name="done" class="btn btn-primary">Update Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Available products data
const productsData = <?php echo json_encode($products); ?>;

// Parse existing order to get selected products
function parseOrder(orderText) {
    const selected = {};
    if (!orderText) return selected;

    const items = orderText.split(',').map(item => item.trim());
    items.forEach(item => {
        const match = item.match(/^(.+?)\s*\((\d+)\)$/);
        if (match) {
            const productName = match[1].trim();
            const quantity = parseInt(match[2]);
            selected[productName] = quantity;
        }
    });
    return selected;
}

// Update order textarea
function updateOrderText() {
    const selectedProducts = [];
    document.querySelectorAll('.product-row').forEach(row => {
        const productName = row.querySelector('.product-select').value;
        const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;

        if (productName && quantity > 0) {
            selectedProducts.push(`${productName} (${quantity})`);
        }
    });

    document.getElementById('order').value = selectedProducts.join(', ');
    updateTotalPrice();
}

// Update total price
function updateTotalPrice() {
    let total = 0;
    document.querySelectorAll('.product-row').forEach(row => {
        const select = row.querySelector('.product-select');
        const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;

        if (select.value && quantity > 0) {
            const product = productsData.find(p => p.title === select.value);
            if (product) {
                total += parseFloat(product.price) * quantity;
            }
        }
    });

    document.getElementById('total_price').value = total.toFixed(2);
}

// Add product row
function addProductRow(selectedProduct = '', selectedQuantity = 1) {
    const container = document.getElementById('product-selector');
    const rowDiv = document.createElement('div');
    rowDiv.className = 'product-row row mb-2';

    let options = '<option value="">Select Product</option>';
    productsData.forEach(product => {
        const selected = product.title === selectedProduct ? 'selected' : '';
        options += `<option value="${product.title}" ${selected}>${product.title} - $${product.price}</option>`;
    });

    rowDiv.innerHTML = `
        <div class="col-md-7">
            <select class="form-control product-select" onchange="updateOrderText()">
                ${options}
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control product-quantity" value="${selectedQuantity}" min="1" onchange="updateOrderText()" placeholder="Qty">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)">Remove</button>
        </div>
    `;

    container.appendChild(rowDiv);
}

// Remove product row
function removeProductRow(button) {
    button.closest('.product-row').remove();
    updateOrderText();
}

// Initialize product selector with existing order
document.addEventListener('DOMContentLoaded', function() {
    const existingOrder = document.getElementById('order').value;
    const selectedProducts = parseOrder(existingOrder);

    // Add rows for existing products
    Object.entries(selectedProducts).forEach(([productName, quantity]) => {
        addProductRow(productName, quantity);
    });

    // Add one empty row if no products
    if (Object.keys(selectedProducts).length === 0) {
        addProductRow();
    }

    // Add "Add Product" button
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'btn btn-success btn-sm mb-2';
    addButton.textContent = '+ Add Product';
    addButton.onclick = () => addProductRow();
    document.getElementById('product-selector').appendChild(addButton);
});
</script>

</body>
</html>