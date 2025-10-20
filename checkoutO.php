<?php
@include '../settings/db.php';
function getdata($movie_id, $conn){
  
   $c = mysqli_query($conn, "SELECT * FROM movies WHERE movie_id = '$movie_id'");
   
   return $c;
}
include 'header.php'; 
require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail2 = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.hostinger.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'order@420vallarta.com';                 // SMTP username
$mail->Password = 'Darialheli12!';                           // SMTP password
$mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587; 

$mail2->isSMTP();                                      // Set mailer to use SMTP
$mail2->Host = 'smtp.hostinger.com';  // Specify main and backup SMTP servers
$mail2->SMTPAuth = true;                               // Enable SMTP authentication
$mail2->Username = 'order@420vallarta.com';                 // SMTP username
$mail2->Password = 'Darialheli12!';                           // SMTP password
$mail2->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
$mail2->Port = 587; 
if(isset($_POST['order_btn'])){

   @$name = $_POST['name'];
   @$ttl = $_POST['ttl'];
   @$number = $_POST['number'];
   @$email = $_POST['email'];
   @$method = $_POST['method'];
   @$adresse = $_POST['adresse'];
   @$pin_code = $_POST['pin_code'];
   @$dat = date('Y-m-d H:i:s');
   
   

   $price_total = 0;
   $product_name = [];
   
   $cart_query = true; // Default to true for success condition
   
   if ($user_id > 0) {
       // Logged in user - process database cart
       $cart_query = mysqli_query($con, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
       if(mysqli_num_rows($cart_query) > 0){
          while($product_item = mysqli_fetch_assoc($cart_query)){
             $product_name[] = $product_item['name'] .' ('. $product_item['quantity'] .') ';
             $product_price = intval($product_item['price']) * intval($product_item['quantity']);
             $price_total += $product_price;
             $qnt_query = getdata($product_item['movie_id'], $con);
             $qnt = mysqli_fetch_array($qnt_query);
             $qnt_new = $qnt['unit'] - $product_item['quantity'];
             $id_prod = $product_item['movie_id'];
             $qnt_update_query = mysqli_query($con, "UPDATE `movies` SET unit = '$qnt_new' WHERE movie_id = '$id_prod'");
          }
       }
   } else {
       // Guest user - process session cart  
       if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
           foreach ($_SESSION['guest_cart'] as $item) {
               $product_name[] = $item['name'] . ' (' . $item['quantity'] . ') ';
               $product_price = $item['price'] * $item['quantity'];
               $price_total += $product_price;
               $qnt_query = getdata($item['movie_id'], $con);
               $qnt = mysqli_fetch_array($qnt_query);
               $qnt_new = $qnt['unit'] - $item['quantity'];
               $id_prod = $item['movie_id'];
               $qnt_update_query = mysqli_query($con, "UPDATE `movies` SET unit = '$qnt_new' WHERE movie_id = '$id_prod'");
           }
       }
   }
 $total_product = implode(', ',$product_name);

   $detail_query = mysqli_query($con, "INSERT INTO `ordere` (name, number, email, method, adresse, pin_code, total_products, total_price, dat, valid) VALUES('$name','$number','$email','$method','$adresse','$pin_code','$total_product','$ttl','$dat',0)") or die('failed query');
   $to = "420vallarta@gmail.com";
   $subj = "420 Vallarta Order";
   $body = "
         Thank you for your confidence in 420 Vallarta!<br>
        <br />We have received your order and will contact you soon via WhatsApp to confirm your order and make delivery arrangements. <br />
The Items ordered are listed below.<br />
<br />
 
            ".$total_product."
             total : $".$ttl."/-  <br>
         
            your name : ".$name."<br>
            your number : ".$number."<br><br />

           420 Vallarta<br />
wwww.420vallarta.com<br />
WhatsApp (52) 322 271 7643";
            $body2 = "
        This is for admin<br>
         
            ".$total_product."<br>
             total : $".$ttl."/-  <br>
         
            name : ".$name."<br>
            number : ".$number."<br>
            email : ".$email."<br>
            
             payment mode : >".$method."
          ";


    
         $mail->setFrom('order@420vallarta.com', '420vallarta.com');
         $mail->addAddress($email, 'thankyou');  
            // Add a recipient
         $mail->addAddress('420vallarta@gmail.com');               // Name is optional
         $mail->addReplyTo('420vallarta@gmail.com', 'New order');
         $mail->addCC('420vallarta@gmail.com');
         $mail->addBCC('420vallarta@gmail.com');

         $mail->Subject = '"420 Vallarta Order';
         $mail->Body    = $body;
         $mail->AltBody = 'Thankyou';


         
         $mail2->setFrom('order@420vallarta.com', '420vallarta.com');
         $mail2->addAddress('420vallarta@gmail.com', 'You have new order');  
            // Add a recipient
         //$mail->addAddress('ellen@example.com');               // Name is optional
         //$mail->addReplyTo('info@exampl48e.com', 'Information');
         //$mail->addCC('cc@example.com');
         //$mail->addBCC('bcc@example.com');

         $mail2->Subject = 'You have new order';
         $mail2->Body    = $body2;
         $mail2->AltBody = 'Thankyou';
         if(!$mail->send()) {
    echo "<script> alert('there is an error please trie again '); </script>";
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   if(!$mail2->send()) {
    echo "<script> alert('there is an error please trie again for second one '); </script>";
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}else{
  


         
   // Clear cart after successful order
   if ($user_id > 0) {
       $deletquery=mysqli_query($con, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   } else {
       $_SESSION['guest_cart'] = []; // Clear guest cart
       $deletquery = true; // Set to true so the success condition works
   }
   if($cart_query && $detail_query){
      if($user_id < 0){
      echo "
      <div class='order-message-container'>
      <div class='message-container'>
         <h3>Thank you for your confidence in 420 Vallarta!</h3>
         <div class='order-detail'>
            <span>".$total_product."</span>
            <span class='total'> total : $".$ttl."/-  </span>
         </div>
         <div class='customer-details'>
            <p> your name : <span>".$name."</span> </p>
            <p> your number : <span>".$number."</span> </p>
            <p> your email : <span>".$email."</span> </p>
            
            <p> your payment mode : <span>".$method."</span> </p>
            <p>We will confirm your order via WhatsApp</p>
         </div>
            <a href='contactus.php' class='btn'>Contact us</a>
            <a href='index.php' class='btn'>Go Home</a>
         </div>
      </div>
      ";
      }else{
         echo "<div class='order-message-container'>
      <div class='message-container'>
         <h3>Thank you for your confidence in 420 Vallarta!</h3>
         <div class='order-detail'>
            <span>".$total_product."</span>
            <span class='total'> total : $".$ttl."/-  </span>
         </div>
         <div class='customer-details'>
            <p> your name : <span>".$name."</span> </p>
            <p> your number : <span>".$number."</span> </p>
            <p> your email : <span>".$email."</span> </p>
            
            <p> your payment mode : <span>".$method."</span> </p>
            <p>We will confirm your order via WhatsApp</p>
         </div>
            <a href='index.php' class='btn'>Go Home</a>
         </div>
      </div>
      ";
      }
   }
   }
}
  

}

?>
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="cart/style2.css">





<!-- custom js file link  -->
<script src="script.js"></script>
   <title>420 Vallarta Order Checkout</title>
   <h2 align="center"><span class="heading">Complete Your Order</span><br />
  <br>
</h2>
<div class="d-flex justify-content-center" style="font-size:15px;">

<div class="mb-3 bg-white" bis_skin_checked="1">
    <span class="mb-0"><strong>THIS IS HOW IT WORKS</strong><br>
                  <br>
1. Place Your Order<br>
2. We Will Confrm the Order Details including Payment Options with a WhatsApp Message <br>
3. Recieve  the Uber Delivery and Enjoy</span> <br />
<br />
<br />
<strong><strong>REFERENCE CODE  </strong></strong><br />
<span class="inputBox">Returning Clients Use your Client ID, New Clients-Place New or Reference Code</span> Provided <br>
<a href="WhatsApp 420 Delivery Confirmation.php" target="_blank">Learn More about Order Confirmation</a> <br />
<br>
<strong><strong>DELIVERY FEE <br />
</strong></strong>Delivery in most cases is free but is not included in the total.<br />
<span class="checkout-form">We will provide you an e-receipt in Mx Pesos and USD.</span><strong><br />
<a href="http://test.420vallarta.com/delivery.php" target="_blank">Learn About Delivery Fees</a> <br />
<br />
PAYMENT<br>
    </strong>Cash, Visa/MasterCard via Stripe, CashApp <br />
    <a href="420%20Vallarta%20Payment%20Option.php" target="_blank">Learn More about Payment Options </a><strong><br />
    <br>
                    <br>
    REACH US </strong><br>
                    Via Online Chat<br>
                  Email; <a href="#">info@420vallarta.com</a><br>
                WhatsApp; (52)  322 271 7643 </div>
</div>
      
<div class="container">
  <section class="checkout-form">
    <style>
      .checkout-form form{
   padding:2rem;
   border-radius: .5rem;
   background-color: #fff;
}
   </style>
    <br />
    <form action="" method="post">
      <div class="display-order">
        <?php
         $total = 0;
         $grand_total = 0;
         $has_items = false;
         
         if ($user_id > 0) {
             // Logged in user - check database cart
             $select_cart = mysqli_query($con, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
             if(mysqli_num_rows($select_cart) > 0){
                 $has_items = true;
                 while($fetch_cart = mysqli_fetch_assoc($select_cart)){
                     $total_price =(intval($fetch_cart['price']) * intval($fetch_cart['quantity']));
                     $grand_total = $total += $total_price;
               ?>
                 <span>
                   <img src="uploads/<?php echo $fetch_cart['image']; ?>" height="100" alt=""><br />
                   <?= $fetch_cart['name']; ?><br />
                   Quantity: <?= $fetch_cart['quantity']; ?>
                 </span>
               <?php
                 }
             }
         } else {
             // Guest user - check session cart
             if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                 $has_items = true;
                 foreach ($_SESSION['guest_cart'] as $item) {
                     $total_price = $item['price'] * $item['quantity'];
                     $grand_total = $total += $total_price;
               ?>
                 <span>
                   <img src="uploads/<?php echo $item['image']; ?>" height="100" alt=""><br />
                   <?= $item['name']; ?><br />
                   Quantity: <?= $item['quantity']; ?>
                 </span>
               <?php
                 }
             }
         }
         
         if (!$has_items) {
             echo "<div class='display-order'><span>your cart is empty!</span></div>";
         }
      ?>
        <span class="grand-total"> grand total : $
          <?= $grand_total; ?>
          /- </span> </div>
      <?php
   if($has_items){
   ?>
      <div class="flex">
        <div class="inputBox"> <span>Username</span>
            <input type="text" placeholder="Your rap/rockstar name, pornstar name, aka" name="name" required="required" />
        </div>
        <div class="inputBox"> <span>WhatsApp Number</span>
            <input type="number" placeholder="We confirm all orders via WhatsApp" name="number" required="required" />
        </div>
        <div class="inputBox"> <span>Your Email</span>
            <input type="email" placeholder="enter your email" name="email" />
        </div>
        <div class="inputBox"> <span>Payment method</span>
            <select name="method">
              
			  <option value="Cash">Cash</option><br />
 <option value="Transfer Deposit">Transfer Deposit</option>
			  <option value="Visa MasterCard Via Stripe">Visa MasterCard Via Stripe</option>
            </select>
        </div>
        <div class="inputBox"> <span>Delivery Location/Address/Hotel/Resort </span>
            <input type="text" placeholder="ex-Francia 100, or Hotel Ziva" name="adresse" required="required" />
        </div>
        <div class="inputBox"> <span>Reference Code</span>
            <input type="text" placeholder="Use your Client ID, New Clients-Place New/Reference Code" name="pin_code" required="required" />
        </div>
      </div>
      <input type="hidden" value="<?php echo $grand_total ?>" name="ttl" />
      <input type="submit" value="order now" name="order_btn" class="btn" />
    </form>
    <?php
   }else{
    ?>
    <?php
   }
?>
  </section>
</div>
</body>
</html>
<?php
include "footer.php";
?>