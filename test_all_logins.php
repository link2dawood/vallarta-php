<?php
require_once('settings/db.php');
session_start();

echo "<h1>420 Vallarta - All Login Systems Test</h1>";

// Add some test users for different roles
echo "<h2>1. Create Test Users</h2>";

// Create test users with known passwords
$test_users = [
    ['name' => 'Staff User', 'email' => 'staff@test.com', 'password' => 'staff123', 'role' => 0],
    ['name' => 'Sales Team', 'email' => 'sales@test.com', 'password' => 'sales123', 'role' => 1],
    ['name' => 'Delivery Team', 'email' => 'delivery@test.com', 'password' => 'delivery123', 'role' => 2]
];

foreach ($test_users as $user) {
    $name = $user['name'];
    $email = $user['email'];
    $password_hash = md5($user['password']);
    $role = $user['role'];
    
    // Check if user exists
    $check = mysqli_query($con, "SELECT id FROM user_info WHERE email = '$email'");
    
    if (mysqli_num_rows($check) == 0) {
        // Insert new user
        $insert = mysqli_query($con, "INSERT INTO user_info (name, email, password, role) VALUES ('$name', '$email', '$password_hash', '$role')");
        
        if ($insert) {
            echo "✅ Created user: $name ($email) - Password: {$user['password']}<br>";
        } else {
            echo "❌ Failed to create user: $name<br>";
        }
    } else {
        // Update existing user
        $update = mysqli_query($con, "UPDATE user_info SET name='$name', password='$password_hash', role='$role' WHERE email='$email'");
        echo "🔄 Updated existing user: $name ($email) - Password: {$user['password']}<br>";
    }
}

echo "<br><h2>2. Current Users in Database</h2>";
$users_query = mysqli_query($con, "SELECT id, name, email, password, role FROM user_info ORDER BY id");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash</th><th>Role</th><th>Login Type</th></tr>";

while ($user = mysqli_fetch_assoc($users_query)) {
    $login_type = "";
    switch($user['role']) {
        case 0: $login_type = "Staff/Client"; break;
        case 1: $login_type = "Sales Team"; break;
        case 2: $login_type = "Delivery Team"; break;
        default: $login_type = "Unknown";
    }
    
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['name']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td style='font-size: 10px;'>{$user['password']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "<td>$login_type</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h2>3. Test Login Forms</h2>";
?>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">

<!-- Staff/Client Login -->
<div style="border: 1px solid #ccc; padding: 15px; width: 300px;">
    <h3 style="color: blue;">Staff/Client Login</h3>
    <p><strong>URL:</strong> LogReg/login.php</p>
    <form method="POST" action="LogReg/login.php" target="_blank">
        <label>Email:</label><br>
        <input type="email" name="email" value="staff@test.com" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" value="staff123" required><br><br>
        
        <input type="submit" name="submit" value="Staff Login" style="background: blue; color: white;">
    </form>
    <p><small>Redirects to: admin.php</small></p>
</div>

<!-- Sales Team Login -->
<div style="border: 1px solid #ccc; padding: 15px; width: 300px;">
    <h3 style="color: green;">Sales Team Login</h3>
    <p><strong>URL:</strong> LogReg/AdmLogin.php</p>
    <form method="POST" action="LogReg/AdmLogin.php" target="_blank">
        <label>Email:</label><br>
        <input type="email" name="email" value="sales@test.com" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" value="sales123" required><br><br>
        
        <input type="submit" name="submit" value="Sales Login" style="background: green; color: white;">
    </form>
    <p><small>Redirects to: admin.php</small></p>
</div>

<!-- Delivery Team Login -->
<div style="border: 1px solid #ccc; padding: 15px; width: 300px;">
    <h3 style="color: red;">Delivery Team Login</h3>
    <p><strong>URL:</strong> LogReg/AdmLogin2.php</p>
    <form method="POST" action="LogReg/AdmLogin2.php" target="_blank">
        <label>Email:</label><br>
        <input type="email" name="email" value="delivery@test.com" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" value="delivery123" required><br><br>
        
        <input type="submit" name="submit" value="Delivery Login" style="background: red; color: white;">
    </form>
    <p><small>Redirects to: order packaging details.php</small></p>
</div>

<!-- Admin Login -->
<div style="border: 1px solid #ccc; padding: 15px; width: 300px;">
    <h3 style="color: purple;">Admin Login</h3>
    <p><strong>URL:</strong> admin/login.php</p>
    <form method="POST" action="admin/action.php?act=login_check" target="_blank">
        <label>Username:</label><br>
        <input type="text" name="username" value="admin" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" value="admin" required><br><br>
        
        <input type="submit" value="Admin Login" style="background: purple; color: white;">
    </form>
    <p><small>Redirects to: movie_manager.php</small></p>
</div>

</div>

<h2>4. Reset Any User Password</h2>
<form method="POST" action="">
    <input type="hidden" name="reset_password" value="1">
    
    <label>Email to reset:</label><br>
    <select name="reset_email">
        <option value="staff@test.com">staff@test.com</option>
        <option value="sales@test.com">sales@test.com</option>
        <option value="delivery@test.com">delivery@test.com</option>
        <option value="staff@email.com">staff@email.com</option>
        <option value="sarah.j@email.com">sarah.j@email.com</option>
    </select><br><br>
    
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
        echo "<div style='color: green; margin-top: 20px; border: 2px solid green; padding: 10px;'>";
        echo "✅ Password reset successfully!<br>";
        echo "Email: $reset_email<br>";
        echo "New Password: $new_password<br>";
        echo "New Hash: $new_hash<br>";
        echo "</div>";
    } else {
        echo "<div style='color: red; margin-top: 20px; border: 2px solid red; padding: 10px;'>";
        echo "❌ Password reset failed: " . mysqli_error($con) . "<br>";
        echo "</div>";
    }
}
?>

<h2>5. Authentication Flow Summary</h2>
<table border="1" style="border-collapse: collapse; width: 100%;">
    <tr>
        <th>Login Type</th>
        <th>URL</th>
        <th>Credential Fields</th>
        <th>Database Table</th>
        <th>Role Check</th>
        <th>Redirect On Success</th>
    </tr>
    <tr>
        <td>Admin</td>
        <td>admin/login.php</td>
        <td>username + password</td>
        <td>users</td>
        <td>admin? = 'yes'</td>
        <td>movie_manager.php</td>
    </tr>
    <tr>
        <td>Staff/Client</td>
        <td>LogReg/login.php</td>
        <td>email + password</td>
        <td>user_info</td>
        <td>role = 0</td>
        <td>admin.php</td>
    </tr>
    <tr>
        <td>Sales Team</td>
        <td>LogReg/AdmLogin.php</td>
        <td>email + password</td>
        <td>user_info</td>
        <td>Any role (tagged as sales)</td>
        <td>admin.php</td>
    </tr>
    <tr>
        <td>Delivery Team</td>
        <td>LogReg/AdmLogin2.php</td>
        <td>email + password</td>
        <td>user_info</td>
        <td>Any role (tagged as delivery)</td>
        <td>order packaging details.php</td>
    </tr>
</table>

<p><strong>All passwords are now MD5 hashed correctly!</strong></p>