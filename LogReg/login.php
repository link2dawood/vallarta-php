<?php
session_start();
require_once __DIR__ . '/../settings/db.php';

if (isset($_POST['submit'])) {
   $email = mysqli_real_escape_string($con, $_POST['email']);
   $password = mysqli_real_escape_string($con, $_POST['password']);
   $pass_hash = md5($password);

   // DEBUG: Log the login attempt
   error_log("Login attempt - Email: $email, Hash: $pass_hash");
   
   $select = mysqli_query($con, "SELECT * FROM `user_info` WHERE email='$email' AND password='$pass_hash' LIMIT 1")
             or die('query failed');

   $row_count = mysqli_num_rows($select);
   error_log("Query result: $row_count rows found");
   
   if ($row_count > 0) {
      $row = mysqli_fetch_assoc($select);
      $_SESSION['user_id'] = (int)$row['id'];
      $_SESSION['user_role'] = 'staff';  // Mark as staff (same as sales)
      session_regenerate_id(true);       // prevent fixation
      error_log("Login successful for user ID: " . $row['id']);
      header('Location: ../admin.php');    // absolute path
      exit;
   } else {
      $message[] = 'incorrect password or email!';
      error_log("Login failed for email: $email");
   }
}

if (isset($_POST['skipp'])) {
   $_SESSION['user_id'] = -random_int(1, 100000);
   session_regenerate_id(true);
   header('Location: ../admin.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '<div class="message" onclick="this.remove();">'.$message.'</div>';
   }
}

if (isset($_GET['pesan'])) {
   if ($_GET['pesan'] == "invalid_staff") {
      echo '<div class="message" onclick="this.remove();">Invalid staff account. Please contact administrator.</div>';
   } else if ($_GET['pesan'] == "staff_only") {
      echo '<div class="message" onclick="this.remove();">This area is for staff only. <a href="../admin/login.php">Click here for admin login</a></div>';
   } else if ($_GET['pesan'] == "session_expired") {
      echo '<div class="message" onclick="this.remove();">Session expired. Please log in again.</div>';
   } else if ($_GET['pesan'] == "login_required") {
      echo '<div class="message" onclick="this.remove();">Please log in to access your cart.</div>';
   } else if ($_GET['pesan'] == "invalid_session") {
      echo '<div class="message" onclick="this.remove();">Invalid session. Please log in again.</div>';
   }
}
?>
   
<div class="form-container">

   <form action="" method="post">
      <h3>login now</h3>
      <input type="email" name="email"  placeholder="enter email" class="box">
      <input type="password" name="password"  placeholder="enter password" class="box">
      <input type="submit" name="submit" class="btn" value="login now">
      <input type="submit" name="skipp" class="btn" value="skip">
      <p>Login as an <a href="AdmLogin.php">Sales Team<br>
      </a>Login as an <a href="AdmLogin2.php">Delivery Team <br>
      </a></p>
  </form>

</div>

</body>
</html>