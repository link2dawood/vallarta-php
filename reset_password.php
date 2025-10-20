<?php
require_once('settings/db.php');

// Test password
$new_password = 'test123';
$new_password_hash = md5($new_password);

echo "Resetting password for staff@email.com\n";
echo "New password: $new_password\n";
echo "New hash: $new_password_hash\n\n";

// Update the password for the test user
$update_query = "UPDATE user_info SET password = '$new_password_hash' WHERE email = 'staff@email.com'";
$result = mysqli_query($con, $update_query);

if ($result) {
    echo "Password updated successfully!\n";
    
    // Verify the update
    $check_query = "SELECT id, name, email, password FROM user_info WHERE email = 'staff@email.com'";
    $check_result = mysqli_query($con, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $user = mysqli_fetch_assoc($check_result);
        echo "\nUpdated user record:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password Hash: " . $user['password'] . "\n";
        
        echo "\nYou can now login with:\n";
        echo "Email: staff@email.com\n";
        echo "Password: test123\n";
    }
} else {
    echo "Error updating password: " . mysqli_error($con) . "\n";
}
?>