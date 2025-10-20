<?php
include 'header.php';

// No login required - guests can use cart

function getdata($movie_id, $conn){
    $c = mysqli_query($conn, "SELECT * FROM movies WHERE movie_id = '$movie_id'");
    return $c;
}

// Handle cart operations for both logged in users and guests
if(isset($_POST['update_cart'])){
	$update_quantity = $_POST['cart_quantity'];
	$update_id = $_POST['cart_id'];
	mysqli_query($con, "UPDATE `cart` SET quantity = '$update_quantity' WHERE id = '$update_id'") or die('query failed');
	$message[] = 'cart quantity updated successfully!';
}

// Handle guest cart update
if(isset($_POST['update_guest_cart'])){
	$index = $_POST['guest_cart_index'];
	$update_quantity = $_POST['cart_quantity'];
	if (isset($_SESSION['guest_cart'][$index])) {
		$_SESSION['guest_cart'][$index]['quantity'] = $update_quantity;
		$message[] = 'cart quantity updated successfully!';
	}
}
 
if(isset($_GET['remove'])){
	$remove_id = $_GET['remove'];
	mysqli_query($con, "DELETE FROM `cart` WHERE id = '$remove_id'") or die('query failed');
}

// Handle guest cart remove
if(isset($_GET['remove_guest'])){
	$index = $_GET['remove_guest'];
	if (isset($_SESSION['guest_cart'][$index])) {
		unset($_SESSION['guest_cart'][$index]);
		$_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']); // Reindex array
	}
}
   
if(isset($_GET['delete_all'])){
	if ($user_id > 0) {
		mysqli_query($con, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
	} else {
		$_SESSION['guest_cart'] = []; // Clear guest cart
	}
}
 
 ?>



	<style>
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

:root{
   --blue:#3498db;
   --red:#e74c3c;
   --orange:#f39c12;
   --black:#333;
   --white:#fff;
   --light-bg:#eee;
   --box-shadow:0 5px 10px rgba(0,0,0,.1);
   --border:2px solid var(--black);
}

*{
   font-family: 'Poppins', sans-serif;
   margin:0; padding:0;
   box-sizing: border-box;
   outline: none; border: none;
   text-decoration: none;
}

*::-webkit-scrollbar{
   width: 10px;
   height: 5px;
}

*::-webkit-scrollbar-track{
   background-color: transparent;
}

*::-webkit-scrollbar-thumb{
   background-color: var(--blue);
}

body{
   background-color: #fff;
}

.message{
   position: sticky;
   top:0; left:0; right:0;
   padding:15px 10px;
   background-color: var(--white);
   text-align: center;
   z-index: 1000;
   box-shadow: var(--box-shadow);
   color:var(--black);
   font-size: 20px;
   text-transform: capitalize;
   cursor: pointer;
}

.btn,
.delete-btn,
.option-btn{
   display: inline-block;
   
   cursor: pointer;
   font-size: 18px;
   color:var(--white);
   border-radius: 5px;
   text-transform: capitalize;
}

.btn:hover,
.delete-btn:hover,
.option-btn:hover{
   background-color: var(--black);
}

.btn{
   background-color: var(--blue);
   margin-top: 10px;
}

.delete-btn{
   background-color: var(--red);
}

.option-btn{
   background-color: var(--orange);
}


.container .heading{
   text-align: center;
   margin-bottom: 20px;
   font-size: 40px;
   text-transform: uppercase;
   color:var(--black);
}

.container .user-profile{
   padding:20px;
   text-align: center;
   border:var(--border);
   background-color: var(--white);
   box-shadow: var(--box-shadow);
   border-radius: 5px;
   margin:20px auto;
   max-width: 500px;
}

.container .user-profile p{
   margin-bottom: 10px;
   font-size: 25px;
   color:var(--black);
}

.container .user-profile p span{
   color:var(--red);
}

.container .user-profile .flex{
   display: flex;
   justify-content: center;
   flex-wrap: wrap;
   gap:10px;
   align-items: flex-end;
}

.container .products .box-container{
   display: flex;
   flex-wrap: wrap;
   gap:15px;
   justify-content: center;
}

.container .products .box-container .box{
   text-align: center;
   border-radius: 5px;
   box-shadow: var(--box-shadow);
   border:var(--border);
   position: relative;
   padding:20px;
   background-color: var(--white);
   width: 350px;
}

.container .products .box-container .box img{
   height: 250px;
}

.container .products .box-container .box .name{
   font-size: 20px;
   color:var(--black);
   padding:5px 0;
}

.container .products .box-container .box .price{
   position: absolute;
   top:10px; left:10px;
   padding:5px 10px;
   border-radius: 5px;
   background-color: var(--orange);
   color:var(--white);
   font-size: 25px;
}

.container .products .box-container .box input[type="number"]{
   margin:10px 0;
   width: 100%;
   border:var(--border);
   border-radius: 5px;
   font-size: 20px;
   color:var(--black);
   padding:12px 14px
}

.container .shopping-cart{
   padding:20px 0;
}

.container .shopping-cart table{
   width: 100%;
   text-align: center;
   border:var(--border);
   border-radius: 5px;
   box-shadow: var(--box-shadow);
   background-color: var(--white);
}

.container .shopping-cart table thead{
   background-color: var(--black);
}

.container .shopping-cart table thead th{
   
   color:var(--white);
   text-transform: capitalize;
   
}

.container .shopping-cart table .table-bottom{
   background-color: var(--light-bg);
}

.container .shopping-cart table tr td{
   
   font-size: 20px;
   color:var(--black);
}


.container .shopping-cart table tr td:nth-child(1){
   padding:0;
}

.container .shopping-cart table tr td input[type="number"]{
   width: 50px;
   border:var(--border);
   font-size: 10px;
   color:var(--black);
}

.container .shopping-cart .cart-btn{
   margin-top: 10px;
   text-align: center;
}

.container .shopping-cart .disabled{
   pointer-events: none;
   background-color: var(--red);
   opacity: .5;
   user-select: none;
}
.container .shopping-cart .table .nim{
   display:none;
}
@media(max-width: 550px ){
   .table thead{
    display: none;
}
.container .shopping-cart .table, .table tbody, .table tr, .table td{
   display: block;
   width: 100%;
}
.container .shopping-cart .table tr{
   margin-bottom: right;
   margin-bottom:20px;
}
.container .shopping-cart .table td{
   text-align: right;
   padding-left: 50%;
   text-align:right;
   position: relative;
}
.container .shopping-cart .table td::before{
   content: attr(data-label);
   position: absolute;
   left: 0;
   width: 50%;
   padding-left:15px;
   font-size:15px;
   font-weight:bold;
   text-align:left;
}
.container .shopping-cart .table .imagpro{
   display:none;
}
.container .shopping-cart .table .nim{
   display: block;
   margin-left:-150px
   
}
.container .shopping-cart .table .imname{
   display: flex;
   justify-content: space-between;
   
}


}


	</style>
	<div class="container">
	<div class="shopping-cart">

<h1 class="heading">shopping cart</h1>

<div style="text-align: center; margin-bottom: 20px;">
    <a href="product_selector.php" class="btn" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px;">
        🛒 Smart Product Selector (No Typing Required!)
    </a>
</div>

<table class="table">
   <thead>
	  <th>image</th>
	  <th>name</th>
	  <th>price</th>
	  <th>quantity</th>
	  <th>total price</th>
	  <th>action</th>
   </thead>
   <tbody>
   <?php
	  $grand_total = 0;
	  $has_items = false;

	  if ($user_id > 0) {
		  // Logged in user - show database cart
		  $cart_query = mysqli_query($con, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
		  
		  if(mysqli_num_rows($cart_query) > 0){
			  $has_items = true;
			 while($fetch_cart = mysqli_fetch_assoc($cart_query)){
				 $qnt_query = getdata($fetch_cart['movie_id'], $con);
				 $qnt = mysqli_fetch_array($qnt_query);
		?>
			  <tr>
				 <td data-label="image" class="imagpro"><img src="images/<?php echo $fetch_cart['image']; ?>" height="100" alt=""></td>
			   
				 <td class="imname"><img class="nim" src="images/<?php echo $fetch_cart['image']; ?>" height="70" alt=""><?php echo $fetch_cart['name']; ?></td>
				 <td data-label="price">$<?php echo $fetch_cart['price']; ?>/-</td>
				 <td data-label="quantity">
					<form action="" method="post">
					   <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
						<input type="number" min="1" max="<?php echo $qnt['unit']; ?>" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
					   <input type="submit" name="update_cart" value="update" class="option-btn">
					</form>
				 </td>
				 <td data-label="total price">$<?php echo $sub_total = (intval($fetch_cart['price']) * intval($fetch_cart['quantity'])); ?>/-</td>
				 <td><a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" class="delete-btn" onclick="return confirm('remove item from cart?');">remove</a></td>
			  </tr>
		   <?php
			  $grand_total += $sub_total;
				 }
		  }
	  } else {
		  // Guest user - show session cart
		  if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
			  $has_items = true;
			  foreach ($_SESSION['guest_cart'] as $index => $item) {
				  $qnt_query = getdata($item['movie_id'], $con);
				  $qnt = mysqli_fetch_array($qnt_query);
				  $sub_total = $item['price'] * $item['quantity'];
		?>
			  <tr>
				 <td data-label="image" class="imagpro"><img src="images/<?php echo $item['image']; ?>" height="100" alt=""></td>
			   
				 <td class="imname"><img class="nim" src="images/<?php echo $item['image']; ?>" height="70" alt=""><?php echo $item['name']; ?></td>
				 <td data-label="price">$<?php echo $item['price']; ?>/-</td>
				 <td data-label="quantity">
					<form action="" method="post">
					   <input type="hidden" name="guest_cart_index" value="<?php echo $index; ?>">
						<input type="number" min="1" max="<?php echo $qnt['unit']; ?>" name="cart_quantity" value="<?php echo $item['quantity']; ?>">
					   <input type="submit" name="update_guest_cart" value="update" class="option-btn">
					</form>
				 </td>
				 <td data-label="total price">$<?php echo $sub_total; ?>/-</td>
				 <td><a href="cart.php?remove_guest=<?php echo $index; ?>" class="delete-btn" onclick="return confirm('remove item from cart?');">remove</a></td>
			  </tr>
		<?php
			  $grand_total += $sub_total;
			  }
		  }
	  }

	  if (!$has_items) {
		 echo '<tr><td style="padding:20px; text-transform:capitalize;" colspan="6">no item added</td></tr>';
	  }
   ?>
   <tr class="table-bottom">
	  <td data-label="Grand total" colspan="4">grand total :</td>
	  <td>$<?php echo $grand_total; ?>/-</td>
	  <td><a href="cart.php?delete_all" onclick="return confirm('delete all from cart?');" class="delete-btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">delete all</a></td>
   </tr>
</tbody>
</table>

<div class="cart-btn">  
   <a href="checkout.php" class="btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">proceed to checkout</a><br>
   <a href="index.php" class="btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">Keep shopping</a>
</div>

</div>

</div>
</body>
</html>
<?PHP
include 'footer.php';
?>

