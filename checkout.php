<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php'; // This includes db.php and sets up $user_id
require_once('settings/inventory_functions.php');
require_once('settings/pdf_receipt_functions.php');

function getdata($movie_id, $conn){
   $c = mysqli_query($conn, "SELECT * FROM movies WHERE movie_id = '$movie_id'");
   return $c;
}

require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail2 = new PHPMailer;

$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'order@420vallarta.com';
$mail->Password = 'Darialheli12!';
$mail->SMTPSecure = 'TLS';
$mail->Port = 587;

$mail2->isSMTP();
$mail2->Host = 'smtp.hostinger.com';
$mail2->SMTPAuth = true;
$mail2->Username = 'order@420vallarta.com';
$mail2->Password = 'Darialheli12!';
$mail2->SMTPSecure = 'TLS';
$mail2->Port = 587;

$order_processed = false;
$success_message = "";

if(isset($_POST['order_btn'])){

   $name = mysqli_real_escape_string($con, $_POST['name']);
   $ttl = mysqli_real_escape_string($con, $_POST['ttl']);
   $number = mysqli_real_escape_string($con, $_POST['number']);
   $email = mysqli_real_escape_string($con, $_POST['email']);
   $method = mysqli_real_escape_string($con, $_POST['method']);
   $adresse = mysqli_real_escape_string($con, $_POST['adresse']);
   $pin_code = mysqli_real_escape_string($con, $_POST['pin_code']);
   $dat = date('Y-m-d H:i:s');

   $price_total = 0;
   $product_name = [];

   $cart_query = true;

   if ($user_id > 0) {
       // Logged in user - process database cart
       $cart_query = mysqli_query($con, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
       if(mysqli_num_rows($cart_query) > 0){
          while($product_item = mysqli_fetch_assoc($cart_query)){
             $product_name[] = $product_item['name'] .' ('. $product_item['quantity'] .') ';
             $product_price = intval($product_item['price']) * intval($product_item['quantity']);
             $price_total += $product_price;
             updateProductStock($product_item['movie_id'], -$product_item['quantity'], $user_id, "Order placed");
          }
       }
   } else {
       // Guest user - process session cart
       if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
           foreach ($_SESSION['guest_cart'] as $item) {
               $product_name[] = $item['name'] . ' (' . $item['quantity'] . ') ';
               $product_price = $item['price'] * $item['quantity'];
               $price_total += $product_price;
               updateProductStock($item['movie_id'], -$item['quantity'], $user_id, "Guest order placed");
           }
       }
   }
   $total_product = implode(', ',$product_name);

   // Get next available client number
   $client_num_query = mysqli_query($con, "SELECT MAX(client_number) as max_client FROM ordere");
   $client_num_result = mysqli_fetch_assoc($client_num_query);
   $next_client_number = max(101200, ($client_num_result['max_client'] ?? 101199) + 1);

   $detail_query = mysqli_query($con, "INSERT INTO `ordere` (name, number, email, method, adresse, pin_code, total_products, total_price, dat, valid, client_number) VALUES('$name','$number','$email','$method','$adresse','$pin_code','$total_product','$ttl','$dat',0,'$next_client_number')") or die('failed query');

   // Get the order ID for the newly created order
   $order_id = mysqli_insert_id($con);

   // Prepare order data for PDF receipt generation
   $order_data = array(
       'id' => $order_id,
       'name' => $name,
       'number' => $number,
       'email' => $email,
       'method' => $method,
       'adresse' => $adresse,
       'pin_code' => $pin_code,
       'total_products' => $total_product,
       'total_price' => $ttl,
       'dat' => $dat,
       'client_number' => $next_client_number
   );

   // Receipt will be sent when order is finalized, not at checkout
   $receipt_sent = false;

   // Send simple admin notification
   $mail2->setFrom('order@420vallarta.com', '420vallarta.com');
   $mail2->addAddress('420vallarta@gmail.com', 'New Order Notification');
   $mail2->Subject = 'New Order #' . $order_id . ' - ' . $name;
   $mail2->isHTML(true);
   $mail2->Body = "
   <h3>New Order Received</h3>
   <p><strong>Order ID:</strong> " . $order_id . "</p>
   <p><strong>Client Number:</strong> " . $next_client_number . "</p>
   <p><strong>Customer:</strong> " . $name . "</p>
   <p><strong>Phone:</strong> " . $number . "</p>
   <p><strong>Email:</strong> " . $email . "</p>
   <p><strong>Payment Method:</strong> " . $method . "</p>
   <p><strong>Total:</strong> $" . $ttl . "</p>
   <p><strong>Products:</strong> " . $total_product . "</p>
   <p><strong>Address:</strong> " . nl2br($adresse) . "</p>
   <p><em>Receipt will be sent after order finalization.</em></p>
   ";

   // Always try to send admin notification
   if (!$mail2->send()) {
       error_log('Admin notification email failed: ' . $mail2->ErrorInfo);
   }

   // Determine success based on order creation (receipt is bonus)
   $email_success = $detail_query; // Order creation is the primary success criteria

   if ($email_success) {
       // Clear cart after successful order
       if ($user_id > 0) {
           $deletquery = mysqli_query($con, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
       } else {
           $_SESSION['guest_cart'] = [];
           $deletquery = true;
       }

       // Mark order as processed successfully
       if ($cart_query && $detail_query) {
           $order_processed = true;

           $success_message = "
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
                   <p><strong>Order ID:</strong> CV-" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . "</p>
                   <p><strong>Client ID:</strong> CL-" . $next_client_number . "</p>
                   <p>We will confirm your order via WhatsApp at ".$number."</p>
                   <div class='receipt-status' style='background-color: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0;'>
                       📱 Your detailed e-receipt will be sent after we finalize your order details.
                   </div>
                </div>
                   <a href='contactus.php' class='btn'>Contact us</a>
                   <a href='index.php' class='btn'>Go Home</a>
                </div>
             </div>
             ";
         } else {
             $order_processed = true;
             $success_message = "
             <div class='order-message-container'>
             <div class='message-container'>
                <h3>Order Processing Error</h3>
                <p>There was an error processing your order. Please try again.</p>
                <a href='cart.php' class='btn'>Back to Cart</a>
                <a href='contactus.php' class='btn'>Contact Support</a>
             </div>
             </div>
             ";
         }
   } else {
       // Order creation failed
       $order_processed = false;
       echo "<script> alert('There was an error creating your order. Please try again.'); </script>";
   }
}

?>
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="cart/style2.css">

   <style>
   .order-message-container {
       display: flex;
       align-items: center;
       justify-content: center;
       min-height: 60vh;
       padding: 20px;
   }
   .message-container {
       background: #fff;
       border-radius: 10px;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
       padding: 30px;
       text-align: center;
       max-width: 600px;
   }
   .order-detail {
       background: #f8f9fa;
       padding: 20px;
       margin: 20px 0;
       border-radius: 5px;
   }
   .customer-details {
       margin: 20px 0;
   }
   .btn {
       display: inline-block;
       background: #28a745;
       color: white;
       padding: 10px 20px;
       text-decoration: none;
       border-radius: 5px;
       margin: 10px;
   }
   .btn:hover {
       background: #218838;
       color: white;
   }
   </style>

<!-- custom js file link  -->
<script src="script.js"></script>

<script>
function showPaymentInstructions() {
    const paymentMethod = document.getElementById('payment-method').value;
    const instructionsContainer = document.getElementById('payment-instructions');

    // Hide all payment instruction blocks
    document.querySelectorAll('.payment-instruction').forEach(function(element) {
        element.style.display = 'none';
    });

    // Show the container and appropriate instruction block
    if (paymentMethod) {
        instructionsContainer.style.display = 'block';

        // Map payment methods to instruction IDs
        const instructionMap = {
            'Oxxo Transfer': 'instructions-oxxo',
            'Bank Transfer': 'instructions-bank',
            'Visa/MasterCard/American Express': 'instructions-card',
            'Paypal': 'instructions-paypal',
            'ApplePay/Google Pay': 'instructions-applepay'
        };

        const instructionId = instructionMap[paymentMethod];
        if (instructionId) {
            document.getElementById(instructionId).style.display = 'block';
        }

        // Scroll to instructions
        instructionsContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    } else {
        instructionsContainer.style.display = 'none';
    }
}

// Update payment amounts when page loads or total changes
function updatePaymentAmounts() {
    const grandTotal = <?php echo $grand_total; ?>;
    const totalElements = document.querySelectorAll('.instruction-total');

    totalElements.forEach(function(element) {
        element.textContent = grandTotal.toFixed(2);
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentAmounts();
});
</script>

   <title>420 Vallarta Order Checkout</title>

<?php
// Display success or error message if order was processed
if($order_processed && !empty($success_message)) {
    echo $success_message;
} else {
?>

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
<a href="http://420vallarta.com/delivery.php" target="_blank">Learn About Delivery Fees</a> <br />
<br />
PAYMENT<br>
    </strong>Oxxo Cash Deposit, Visa/MasterCard via Stripe, Paypal, Bank Transfer, ApplePay, Mercado Pago<br />
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
                     $grand_total += $total_price;
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
                     $grand_total += $total_price;
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
            <select name="method" required id="payment-method" onchange="showPaymentInstructions()">
              <option value="">Select Payment Method</option>
              <option value="Oxxo Transfer">Oxxo Transfer</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="Visa/MasterCard/American Express">Visa/MasterCard/American Express</option>
              <option value="Paypal">Paypal</option>
              <option value="ApplePay/Google Pay">ApplePay/Google Pay</option>
            </select>
        </div>

        <!-- Dynamic Payment Instructions -->
        <div id="payment-instructions" style="display: none; margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">

            <!-- Oxxo Transfer Instructions -->
            <div id="instructions-oxxo" class="payment-instruction" style="display: none;">
                <h4 style="color: #007bff; margin-bottom: 15px;">💳 Oxxo Transfer Instructions</h4>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p><strong>Step 1:</strong> Visit any Oxxo store near you</p>
                    <p><strong>Step 2:</strong> Tell the cashier you want to make a "Depósito" (deposit)</p>
                    <p><strong>Step 3:</strong> Provide this account information:</p>
                    <ul>
                        <li><strong>Account Number:</strong> 4152 3142 6559 7115</li>
                        <li><strong>Bank:</strong> BBVA</li>
                        <li><strong>Reference:</strong> Your Client Number (will be provided after placing order)</li>
                    </ul>
                    <p><strong>Step 4:</strong> Send us a photo of the deposit receipt via WhatsApp</p>
                    <p><strong>💰 Amount:</strong> $<span class="instruction-total"><?php echo $grand_total; ?></span> MXN</p>
                </div>
                <div style="background: #d4edda; padding: 10px; border-radius: 5px;">
                    <small><strong>Note:</strong> Your order will be confirmed within 30 minutes of payment verification</small>
                </div>
            </div>

            <!-- Bank Transfer Instructions -->
            <div id="instructions-bank" class="payment-instruction" style="display: none;">
                <h4 style="color: #007bff; margin-bottom: 15px;">🏦 Bank Transfer Instructions</h4>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p><strong>Transfer to:</strong></p>
                    <ul>
                        <li><strong>Bank:</strong> BBVA</li>
                        <li><strong>Account Number:</strong> 4152 3142 6559 7115</li>
                        <li><strong>Reference:</strong> Your Client Number (will be provided after placing order)</li>
                    </ul>
                    <p><strong>💰 Amount:</strong> $<span class="instruction-total"><?php echo $grand_total; ?></span> MXN</p>
                </div>
                <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                    <small><strong>International Transfer:</strong> Contact us for SWIFT code and additional details</small>
                </div>
                <div style="background: #d4edda; padding: 10px; border-radius: 5px;">
                    <small><strong>Confirmation:</strong> Send transfer receipt to info@420vallarta.com or WhatsApp</small>
                </div>
            </div>

            <!-- Credit Card Instructions -->
            <div id="instructions-card" class="payment-instruction" style="display: none;">
                <h4 style="color: #007bff; margin-bottom: 15px;">💳 Credit/Debit Card Payment</h4>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p><strong>Secure Payment via Stripe:</strong></p>
                    <ul>
                        <li>✅ Visa, MasterCard, American Express accepted</li>
                        <li>🔒 SSL encrypted secure payment</li>
                        <li>🌍 International cards welcome</li>
                        <li>💱 Automatic currency conversion</li>
                    </ul>
                    <p><strong>💰 Amount:</strong> $<span class="instruction-total"><?php echo $grand_total; ?></span> USD</p>
                </div>
                <div style="background: #e7f3ff; padding: 15px; border-radius: 5px;">
                    <p><strong>Payment Process:</strong></p>
                    <p><strong>You will receive a secure email from Stripe with payment instructions.</strong></p>
                    <p>✅ No registration or login required</p>
                    <p>✅ Simply click the link in the email and complete payment</p>
                </div>
            </div>

            <!-- PayPal Instructions -->
            <div id="instructions-paypal" class="payment-instruction" style="display: none;">
                <h4 style="color: #007bff; margin-bottom: 15px;">💙 PayPal Payment</h4>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p><strong>Send Payment to:</strong></p>
                    <ul>
                        <li><strong>PayPal Email:</strong> pvblessings1@gmail.com</li>
                        <li><strong>Payment Type:</strong> Goods & Services</li>
                        <li><strong>Reference:</strong> Your Client Number (provided after placing order)</li>
                    </ul>
                    <p style="color: #dc3545; font-size: 14px;"><strong>⚠️ Important:</strong> Please do not mention 420 or anything cannabis related. Use your Client Number as reference only.</p>
                    <p><strong>💰 Amount:</strong> $<span class="instruction-total"><?php echo $grand_total; ?></span> USD</p>
                </div>
                <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                    <small><strong>Alternative:</strong> We can send you a PayPal invoice after order confirmation</small>
                </div>
                <div style="background: #d4edda; padding: 10px; border-radius: 5px;">
                    <small><strong>Note:</strong> PayPal payments are processed instantly upon receipt</small>
                </div>
            </div>

            <!-- Apple Pay / Google Pay Instructions -->
            <div id="instructions-applepay" class="payment-instruction" style="display: none;">
                <h4 style="color: #007bff; margin-bottom: 15px;">📱 Apple Pay / Google Pay</h4>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p><strong>Digital Wallet Payment:</strong></p>
                    <ul>
                        <li>🍎 Apple Pay (iPhone, iPad, Mac, Apple Watch)</li>
                        <li>🤖 Google Pay (Android devices)</li>
                        <li>⚡ One-touch payment</li>
                        <li>🔒 Biometric security</li>
                    </ul>
                    <p><strong>💰 Amount:</strong> $<span class="instruction-total"><?php echo $grand_total; ?></span> USD</p>
                </div>
                <div style="background: #e7f3ff; padding: 15px; border-radius: 5px;">
                    <p><strong>Payment Process:</strong></p>
                    <p><strong>You will receive an email from Stripe with payment instructions.</strong></p>
                    <p>✅ No registration or login required</p>
                    <p>✅ One-tap payment with biometric authentication</p>
                </div>
            </div>

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

<?php
} // End else block for order not processed
?>

</body>
</html>

<?php
include "footer.php";
?>