<?php
require_once('settings/receipt_functions.php');

echo "<h1>420 Vallarta Payment Methods Test</h1>";

$payment_methods = [
    'Oxxo Transfer',
    'Bank Transfer', 
    'Visa/MasterCard/American Express',
    'Paypal',
    'ApplePay/Google Pay'
];

$test_client_id = 'CL-0001';

foreach ($payment_methods as $method) {
    echo "<div style='border: 1px solid #ccc; margin: 20px; padding: 15px; border-radius: 5px;'>";
    echo "<h2>" . htmlspecialchars($method) . "</h2>";
    
    $payment_info = getPaymentInformation($method, $test_client_id);
    
    echo "<h3>" . htmlspecialchars($payment_info['title']) . "</h3>";
    echo "<p>" . htmlspecialchars($payment_info['instructions']) . "</p>";
    
    if (!empty($payment_info['details'])) {
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        foreach ($payment_info['details'] as $key => $value) {
            echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "<br>";
        }
        echo "</div>";
    }
    
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px; color: #856404;'>";
    echo "<strong>⚠️ " . htmlspecialchars($payment_info['reference_note']) . "</strong>";
    echo "</div>";
    
    echo "</div>";
}

echo "<h2>Checkout Form Payment Dropdown</h2>";
echo "<form>";
echo "<label>Payment Method:</label><br>";
echo "<select name='method' required style='padding: 8px; width: 300px; margin: 10px 0;'>";
echo "<option value=''>Select Payment Method</option>";
foreach ($payment_methods as $method) {
    echo "<option value='" . htmlspecialchars($method) . "'>" . htmlspecialchars($method) . "</option>";
}
echo "</select>";
echo "</form>";

echo "<p><em>Test completed successfully! ✅</em></p>";
?>