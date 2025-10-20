<?php
require_once('settings/db.php');

echo "<h2>Login Debug Tool</h2>";

// Test the database connection
echo "<h3>1. Database Connection Test:</h3>";
if ($con) {
    echo "✅ Database connected successfully<br>";
    echo "Database: " . mysqli_get_server_info($con) . "<br><br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br><br>";
    exit;
}

// Check what users exist
echo "<h3>2. Users in user_info table:</h3>";
$query = "SELECT id, name, email, password, role FROM user_info ORDER BY id";
$result = mysqli_query($con, $query);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash</th><th>Role</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['password'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ Error querying users: " . mysqli_error($con) . "<br><br>";
}

// Test login simulation
echo "<h3>3. Login Simulation Test:</h3>";
$test_email = 'staff@email.com';
$test_passwords = ['test123', 'hello', 'password', '123456', 'admin'];

foreach ($test_passwords as $test_password) {
    echo "<strong>Testing: $test_email / $test_password</strong><br>";
    
    // Simulate the login process exactly as in login.php
    $email = mysqli_real_escape_string($con, $test_email);
    $password = mysqli_real_escape_string($con, $test_password);
    $pass_hash = md5($password);
    
    echo "- Escaped email: '$email'<br>";
    echo "- Escaped password: '$password'<br>";
    echo "- MD5 hash: '$pass_hash'<br>";
    
    $select = "SELECT * FROM `user_info` WHERE email='$email' AND password='$pass_hash' LIMIT 1";
    echo "- Query: $select<br>";
    
    $result = mysqli_query($con, $select);
    if ($result) {
        $count = mysqli_num_rows($result);
        echo "- Result: $count rows found<br>";
        if ($count > 0) {
            $user = mysqli_fetch_assoc($result);
            echo "- ✅ LOGIN SUCCESS for user: " . $user['name'] . "<br>";
        } else {
            echo "- ❌ LOGIN FAILED<br>";
        }
    } else {
        echo "- ❌ Query error: " . mysqli_error($con) . "<br>";
    }
    echo "<br>";
}

// Manual hash check
echo "<h3>4. Manual Hash Verification:</h3>";
$db_hash = '25d55ad283aa400af464c76d713c07ad'; // First user's hash
echo "Target hash from DB: $db_hash<br>";

$test_passwords_extended = [
    'test123', 'hello', 'password', '123456', 'admin', 'staff', 'user',
    'demo', 'login', 'welcome', 'qwerty', 'abc123', '000000',
    'password123', 'admin123', 'staff123', 'test123', 'hello123'
];

foreach ($test_passwords_extended as $pwd) {
    $hash = md5($pwd);
    if ($hash === $db_hash) {
        echo "🎉 <strong style='color: green;'>FOUND PASSWORD: '$pwd' creates hash '$hash'</strong><br>";
        break;
    }
}

// Test current login.php logic
echo "<h3>5. Current login.php Logic Test:</h3>";
if ($_POST) {
    echo "<strong>Form submitted!</strong><br>";
    echo "POST data received:<br>";
    print_r($_POST);
    
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $pass_hash = md5($password);

        echo "Email: '$email'<br>";
        echo "Password: '$password'<br>";
        echo "Hash: '$pass_hash'<br>";

        $select = mysqli_query($con, "SELECT * FROM `user_info` WHERE email='$email' AND password='$pass_hash' LIMIT 1");
        
        if (mysqli_num_rows($select) > 0) {
            $row = mysqli_fetch_assoc($select);
            echo "✅ LOGIN SUCCESS! User: " . $row['name'] . "<br>";
        } else {
            echo "❌ LOGIN FAILED - No matching user found<br>";
        }
    }
} else {
    echo "No form data submitted. Use the form below to test:<br><br>";
}
?>

<h3>6. Test Login Form:</h3>
<form method="POST" action="">
    <label>Email:</label><br>
    <input type="email" name="email" value="staff@email.com" required><br><br>
    
    <label>Password:</label><br>
    <input type="password" name="password" placeholder="Enter password" required><br><br>
    
    <input type="submit" value="Test Login">
</form>

<h3>7. Reset Password Tool:</h3>
<form method="POST" action="">
    <input type="hidden" name="reset_password" value="1">
    <label>Email to reset:</label><br>
    <input type="email" name="reset_email" value="staff@email.com" required><br><br>
    
    <label>New Password:</label><br>
    <input type="text" name="new_password" value="test123" required><br><br>
    
    <input type="submit" value="Reset Password">
</form>

<?php
// Handle password reset
if (isset($_POST['reset_password'])) {
    $reset_email = mysqli_real_escape_string($con, $_POST['reset_email']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $new_hash = md5($new_password);
    
    $update_query = "UPDATE user_info SET password = '$new_hash' WHERE email = '$reset_email'";
    $result = mysqli_query($con, $update_query);
    
    if ($result && mysqli_affected_rows($con) > 0) {
        echo "<div style='color: green; margin-top: 20px;'>";
        echo "✅ Password reset successfully!<br>";
        echo "Email: $reset_email<br>";
        echo "New Password: $new_password<br>";
        echo "New Hash: $new_hash<br>";
        echo "</div>";
    } else {
        echo "<div style='color: red; margin-top: 20px;'>";
        echo "❌ Password reset failed: " . mysqli_error($con) . "<br>";
        echo "</div>";
    }
}
?>