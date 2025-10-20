<?php

include '../settings/db.php';
session_start();

if(isset($_POST['submit'])){

   $email = mysqli_real_escape_string($con, $_POST['email']);
   $password = mysqli_real_escape_string($con, $_POST['password']);
   $pass_hash = md5($password);

   // Sales Team login (same as staff) - all go to admin.php
   $select = mysqli_query($con, "SELECT * FROM `user_info` WHERE email = '$email' AND password = '$pass_hash'") or die('query failed');

   if(mysqli_num_rows($select) > 0){
      $row = mysqli_fetch_assoc($select);
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['user_role'] = 'sales';  // Mark as sales team (for identification)
      header('location:../admin.php');
      exit;
   }else{
      $message[] = 'incorrect password or email!';
   }

}

?>

 <!--Favicon-->
    <link rel="shortcut icon" href="/Favi/favicon.ico" >
    <link href="Favi/apple-touch-icon.png" rel="apple-touch-icon" />
    <link rel="apple-touch-icon" sizes="180x180" href="/Favi/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="48x48" href="/Favi/favicon-48x48.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="apple-mobile-web-app-title" content="420 Vallarta">
	<meta name="application-name" content="420 Vallarta">
    <meta name="msapplication-TileColor" content="#2b5797">
	<meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sales Team Login 420 Vallarta</title>

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
?>
   
<div class="form-container">

   <form action="" method="post">
      <h3><img src="/images/PV emblem round.png" alt="420 Vallarta" width="100" height="100"><br>
      Sales Team Login</h3>
      <input type="email" name="email" required placeholder="enter email" class="box">
      <input type="password" name="password" required placeholder="enter password" class="box">
      <input type="submit" name="submit" class="btn" value="login now">
      <p>Login as an <a href="login.php">Client<br>
      </a>Login as an <a href="AdmLogin2.php">Delivery Team </a></p>
      <br>
   </form>

</div>

</body>
</html>