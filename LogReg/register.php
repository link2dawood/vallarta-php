<?php

include '../settings/db.php';
require '../PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;


$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'access825592275.webspace-data.io';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'u1130193824';                 // SMTP username
$mail->Password = 'Fiverr12345!';                           // SMTP password
$mail->SMTPSecure = 'SFTP';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 22; 
if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($con, $_POST['name']);
   $email = mysqli_real_escape_string($con, $_POST['email']);
   $password = mysqli_real_escape_string($con, $_POST['password']);
   $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
   $pass = md5($password);
   $cpass = md5($cpassword);

   $select = mysqli_query($con, "SELECT * FROM `user_info` WHERE email = '$email' AND password = '$pass'") or die('query failed');

   if(mysqli_num_rows($select) > 0){
      $message[] = 'user already exist!';
   }else{
      mysqli_query($con, "INSERT INTO `user_info`(name, email, password, role) VALUES('$name', '$email', '$pass',0)") or die('query failed');
      
      
      $body = "thankyou";
      
      $mail->setFrom('420vallarta@gmail.com', 'Mailer');
         $mail->addAddress($email, 'thankyou');  
            // Add a recipient
         //$mail->addAddress('ellen@example.com');               // Name is optional
         //$mail->addReplyTo('info@example.com', 'Information');
         //$mail->addCC('cc@example.com');
         //$mail->addBCC('bcc@example.com');

         $mail->Subject = 'thanks for register';
         $mail->Body    = $body;
         $mail->AltBody = 'Thankyou';
         if(!$mail->send()) {
    echo "<script> alert('there is an error please trie again '); </script>";
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   $message[] = 'registered successfully!';
   header('location:login.php');
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
   <title>register</title>

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
      <h3>register now</h3>
      <input type="text" name="name" required placeholder="enter username" class="box">
      <input type="email" name="email" required placeholder="enter email" class="box">
      <input type="password" name="password" required placeholder="enter password" class="box">
      <input type="password" name="cpassword" required placeholder="confirm password" class="box">
      <input type="submit" name="submit" class="btn" value="register now">
      <p>already have an account? <a href="login.php">login now</a></p>
   </form>

</div>

</body>
</html>