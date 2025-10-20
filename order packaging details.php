<?php
require_once('settings/db.php');
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:LogReg/login.php');
};


?>
<!DOCTYPE html>
<html>
<head>
<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>
<title>Packing and Delivery Details - 420 Vallarta</title></head>
<body>

<h1>420 Vallarta Packing Details </h1>

<a href="Inventory Report.php">INVENTORY</a><br>
<br>
<table id="customers">
  <tr>
    <th width="10%">Username</th>
    <th width="15%">Order</th>
    <th width="7%">Total</th>
    <th width="7%">Delivery Fee </th>
    <th width="6%">Location</th>
    <th width="4%">valid</th>
    <th width="9%">validation</th>
    <th width="16%">Date/Time Recieved </th>
  </tr>
  <?php
	  $order_query = mysqli_query($con, "SELECT * FROM `ordere` ORDER BY dat DESC") or die('query failed');
	 
	  if(mysqli_num_rows($order_query) > 0){
		 while($fetch_order = mysqli_fetch_assoc($order_query)){
   ?>
  <tr>
    <td><?php echo $fetch_order['name'];?></td>
    <td><?php echo $fetch_order['total_products'];?></td>
    <td><?php echo $fetch_order['total_price'];?></td>
    <td>&nbsp;</td>
    <td><?php echo $fetch_order['adresse'];?></td>
    <td><?php if($fetch_order['valid']==1){echo "done";}else{echo "not yet";}?></td>
    <td><?php if($fetch_order['valid']==0){?><a href="validate.php?id=<?php echo $fetch_order['id'] ?>">Validate</a><?php }else{ echo "already validated";}?></td>
    <td><?php echo $fetch_order['dat'];?></td>
  </tr>
  <?php
         }
        }
  ?>
</table>

</body>
</html>


