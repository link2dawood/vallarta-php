<?php
require_once('settings/db.php');

// Test data
$test_email = 'staff@email.com';
$test_password = 'hello'; // Try different passwords

echo "Testing login with:\n";
echo "Email: $test_email\n";
echo "Password: $test_password\n";
echo "MD5 Hash: " . md5($test_password) . "\n\n";

// Check what's in database
$query = "SELECT * FROM user_info WHERE email = '$test_email'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo "Database record found:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Password Hash: " . $user['password'] . "\n";
    echo "Role: " . $user['role'] . "\n\n";
    
    // Test hash comparison
    $input_hash = md5($test_password);
    echo "Input hash: $input_hash\n";
    echo "DB hash:    " . $user['password'] . "\n";
    echo "Match? " . ($input_hash === $user['password'] ? 'YES' : 'NO') . "\n";
} else {
    echo "No user found with email: $test_email\n";
}

// Try to find what password creates the hash
$target_hash = '25d55ad283aa400af464c76d713c07ad';
$common_passwords = [
    'hello', 'password', '123456', 'admin', 'test', 'staff', 'user',
    'demo', 'login', 'welcome', 'qwerty', 'abc123', '000000',
    'password123', 'admin123', 'staff123', 'test123'
];

echo "\nTrying to find password for hash: $target_hash\n";
foreach ($common_passwords as $pwd) {
    $hash = md5($pwd);
    if ($hash === $target_hash) {
        echo "FOUND PASSWORD: '$pwd' -> $hash\n";
        break;
    }
}
?>