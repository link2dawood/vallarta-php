<?php
// 420 Vallarta E-Receipt System Configuration

// Exchange Rate Configuration
$GLOBALS['receipt_config'] = array(
    // Currency Settings
    'primary_currency' => 'MXN',
    'secondary_currency' => 'USD',
    'exchange_rate' => 18.50, // MXN to USD (1 USD = 18.50 MXN)
    'update_rate_url' => 'https://api.exchangerate-api.com/v4/latest/USD', // Optional: API for live rates

    // Company Information
    'company_name' => '420 Vallarta',
    'company_address' => 'Puerto Vallarta, Jalisco, Mexico',
    'company_phone' => '+52 322 271 7643',
    'company_email' => 'info@420vallarta.com',
    'company_website' => 'www.420vallarta.com',
    'whatsapp_number' => '+52 322 271 7643',

    // Social Media
    'social_handles' => array(
        'instagram' => '@420.puertovallarta',
        'facebook' => '@420vallarta',
        'twitter' => '@420vallarta',
        'youtube' => '@420vallarta',
        'pinterest' => '@420puertovallarta'
    ),

    // Default Values
    'default_delivery_fee' => 0.00, // MXN
    'default_discount' => 0.00,
    'default_refund' => 0.00,
    'default_eta' => '60-90 minutes',

    // Complimentary Items (editable per order)
    'default_complimentary_items' => array(
        'Rolling Papers' => 'Free',
        'Lighter' => 'Free',
        'Mints' => 'Free'
    ),

    // Payment Options
    'payment_options' => array(
        'Cash' => 'Cash on Delivery',
        'Visa MasterCard Via Stripe' => 'Credit/Debit Card via Stripe',
        'PayPal' => 'PayPal',
        'Bank Transfer' => 'Bank Transfer',
        'Crypto' => 'Cryptocurrency'
    ),

    // Email Settings
    'receipt_subject' => '420 Vallarta - Order Receipt #{order_id}',
    'receipt_from_name' => '420 Vallarta',
    'receipt_from_email' => 'order@420vallarta.com',

    // Receipt Template Settings
    'logo_url' => 'https://420vallarta.com/images/PV emblem round.png',
    'receipt_footer_text' => 'Thank you for choosing 420 Vallarta! Follow us on social media for updates and special offers.',
    'terms_url' => 'https://420vallarta.com/terms.php',
    'privacy_url' => 'https://420vallarta.com/privacy.php'
);

// Function to get current exchange rate (can be enhanced with live API)
function getCurrentExchangeRate() {
    global $receipt_config;

    // For now, return the configured rate
    // In future, this can be enhanced to fetch live rates
    return $receipt_config['exchange_rate'];
}

// Function to convert MXN to USD
function convertMXNtoUSD($mxn_amount) {
    $rate = getCurrentExchangeRate();
    return round($mxn_amount / $rate, 2);
}

// Function to convert USD to MXN
function convertUSDtoMXN($usd_amount) {
    $rate = getCurrentExchangeRate();
    return round($usd_amount * $rate, 2);
}

// Function to format currency
function formatCurrency($amount, $currency = 'MXN') {
    if ($currency == 'MXN') {
        return '$' . number_format($amount, 2) . ' MXN';
    } else {
        return '$' . number_format($amount, 2) . ' USD';
    }
}

// Function to get receipt configuration
function getReceiptConfig() {
    global $receipt_config;
    return $receipt_config;
}

// Function to update exchange rate (admin only)
function updateExchangeRate($new_rate) {
    global $con;

    if ($new_rate > 0) {
        // Store in database for persistence
        $query = "UPDATE receipt_settings SET exchange_rate = '$new_rate' WHERE id = 1";
        mysqli_query($con, $query);

        // Update global config
        $GLOBALS['receipt_config']['exchange_rate'] = $new_rate;
        return true;
    }
    return false;
}

// Initialize receipt settings table if it doesn't exist
function initializeReceiptSettings() {
    global $con;

    $create_table = "CREATE TABLE IF NOT EXISTS receipt_settings (
        id INT PRIMARY KEY DEFAULT 1,
        exchange_rate DECIMAL(10,4) DEFAULT 18.5000,
        default_delivery_fee DECIMAL(10,2) DEFAULT 100.00,
        default_eta VARCHAR(100) DEFAULT '60-90 minutes',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    mysqli_query($con, $create_table);

    // Insert default values if empty
    $check_settings = mysqli_query($con, "SELECT * FROM receipt_settings WHERE id = 1");
    if (mysqli_num_rows($check_settings) == 0) {
        $insert_defaults = "INSERT INTO receipt_settings (id, exchange_rate, default_delivery_fee, default_eta)
                           VALUES (1, 18.5000, 100.00, '60-90 minutes')";
        mysqli_query($con, $insert_defaults);
    }
}

// Load settings from database
function loadReceiptSettings() {
    global $con, $receipt_config;

    $settings_query = mysqli_query($con, "SELECT * FROM receipt_settings WHERE id = 1");
    if ($settings_query && mysqli_num_rows($settings_query) > 0) {
        $settings = mysqli_fetch_array($settings_query);
        $receipt_config['exchange_rate'] = $settings['exchange_rate'];
        $receipt_config['default_delivery_fee'] = $settings['default_delivery_fee'];
        $receipt_config['default_eta'] = $settings['default_eta'];
    }
}

// Generate unique receipt ID
function generateReceiptID($order_id) {
    return '420VTA-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
}
?>