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
<title>420 Vallarta Order Table</title></head>
<body>

<h1>420 Vallarta Inventory Table</h1>

<table id="customers">
  <tr>
    <th>Product</th>
    <th>Price</th>
    <th>Inventory Added </th>
    <th>Remaining In Stock</th>
  </tr>
  <?php
	  $order_query = mysqli_query($con, "SELECT * FROM `movies` ORDER BY date_created") or die('query failed');
	 
	  if(mysqli_num_rows($order_query) > 0){
		 while($fetch_order = mysqli_fetch_assoc($order_query)){
   ?>
  <tr>
    <td><?php echo $fetch_order['title'];?></td>
    <td><?php echo $fetch_order['price'];?></td>
    <td><?php echo $fetch_order['unit'];?></td>
    <td>&nbsp;</td>
  </tr>
  <?php
         }
        }
  ?>
  
  
</table>

</body>
</html>


