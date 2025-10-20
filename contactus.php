<?php
include 'header.php';


?>
<?php


	if(isset($_POST['contact_form'])){
		$fname = $_POST['fname'];
		$whatsapp = $_POST['whatsapp'];
		$email = $_POST['email'];
		$deliveryadr = $_POST['deliveryadr'];
		$code = $_POST['code'];
		$ordermessage = $_POST['message'];
		
		$to = '420vallarta@gmail.com';
		$from = 'orders@vallartavisitors.com';
		$subject = 'New order';
		
		$message = "s
			First Name: ".htmlspecialchars($fname)."\n
			WhatsApp: ".htmlspecialchars($whatsapp)."\n
			E-mail: ".htmlspecialchars($email)."\n
			Delivery Address: ".htmlspecialchars($deliveryadr)."\n
			Code: ".htmlspecialchars($code)."\n
			Message: ".htmlspecialchars($ordermessage)."\n
		";
		

		$insert_data=mysqli_query($con, "insert into contact_tb(fname, whatsapp, email, deliveryadr, code, message)values('$fname','$whatsapp','$email','$deliveryadr','$code','$ordermessage')");

		//$mail = $smtp->send($to,$subject,$message,$headers);

		/* if (PEAR::isError($mail)) {
			echo('<p>' . $mail->getMessage() . '</p>');
		} else {
			echo('<p>Message successfully sent!</p>');
		}
		
		exit; */
		
		header("location:contactus.php?success");
		echo "<script> window.location.href = 'contactus.php?success'; </script>";
		exit();
		
	}
?><title>420 Vallarta - Contact Us</title>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="row mb-5">
            <div class="col-12 ">
                <h2 class="site-section-heading text-center">Contact Us </h2>
				<?php
					if (isset($_GET['success']))
					{
				?>
						<div class="alert alert-success" role="alert">We have received your message. 
						</div>
				        <?php
					}
				?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-5">
                <form  method="POST">
                    <div class="row form-group">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-black" for="fname">Username</label>
                            <input type="text" id="fname" name="fname" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="text-black" for="lname">WhatsApp</label>
                            <input type="text" id="lname" name="whatsapp" class="form-control">
                        </div>
                    </div>

                    <div class="row form-group">

                        <div class="col-md-12">
                            <label class="text-black" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="row form-group">

                        <div class="col-md-12">
                            <label class="text-black" for="subject">Delivery Address </label>
                            <input type="text" name="deliveryadr" id="subject" class="form-control">
                        </div>
                    </div>
 <div class="row form-group">

                        <div class="col-md-12">
                            <label class="text-black" for="subject">Referral Code or Client ID </label>
                            <input type="text" name="code" id="subject" class="form-control">
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-12">
                            <label class="text-black" for="message">Tell Us </label>
                            <textarea name="message" id="message" name="message" cols="30" rows="7" class="form-control" placeholder="Feel Free to Ask Us the Questions You Need"></textarea>
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-md-12">
                            <input type="submit" value="Send Message" name="contact_form" class="btn btn-primary py-2 px-4 text-white">
                        </div>
                    </div>


                </form>
            </div>
            <div class="col-lg-3 ml-auto">
              <div class="mb-3 bg-white">
                  <span class="mb-0"><strong>THIS IS HOW IT WORKS</strong><br />
                  <br />
1. Place Your Order<br />
2. We Will Confrm the Order with a WhatsApp Message <br />
3. Recieve  the Uber Delivery and Enjoy</span> <br />
<br />
<br />
<strong>PAYMENT</strong><a href="/420%20Vallarta%20Payment%20Option.php"><br />
          </a>PayPal, 
              Visa/MasterCard via Stripe, CashApp, Skrill<a href="/420%20Vallarta%20Payment%20Option.php"> <br />
              </a><strong><a href="/420%20Vallarta%20Payment%20Option.php">Learn More</a> <br />
                    <br />
                  REACH US </strong><br />
                    Via Online Chat<br /> 
                  <br />
                  Email; <a href="#">info@420vallarta.com</a><br />
                WhatsApp;  (52)  322 271 7643<br />
                <a href="faq.php"><br />
                Frequently Asked Questions</a><br />
                <a href="delivery.php">Delivery Details</a><br />
              <a href="WhatsApp%20420%20Delivery%20Confirmation.php">Delivery Confirmation              </a></div>

            </div>
        </div>
    </div>

</div>
<?php
include 'footer.php';
?>
