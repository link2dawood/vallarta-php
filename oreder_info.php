<?php
require_once('settings/db.php');
$id = $_GET['id'];
  ?>
<!DOCTYPE html>
<html>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
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
  background-color: #187bcd;
  color: white;
}
</style>
</head>
<body>


<table width="100%" border="1" bgcolor="#000000">
  <tr>
    <td height="226"><h2 align="center"><img src="/images/lotus flower.png" alt="420 Lotus" width="55" height="55" align="right"><br>
            <br>
    </h2>
        <h2 align="center" class="style1"><img src="/images/PV emblem round.png" alt="420 Vallarta" width="160" height="160" align="middle"><br>
            <br>
      </h2></td>
  </tr>
</table>
<h2 align="center" class="style2">420 Vallarta Orders Details </h2>
<br>
<br>
<a href="inventory/index.php"><img src="images/420 vallarta inventory icon.png" alt="Product Inventory 420 Vallarta" width="84" height="84" border="0"></a> <a href="admin.php"><img src="images/420 Vallarta Orders Icon.png" alt="Client Orders 420 Vallarta" width="83" height="83" border="0"></a> <a href="http://420vallarta.com/movie.php" target="_blank"><img src="/images/cashier 420 vallarta.png" alt="Cashier 420 Vallarta" name="Cashier" width="85" height="85" border="0" id="Cashier"></a>
<h1>&nbsp;</h1>

<table id="customers">
  <tr>
    
    <th>WhatsApp number</th>
    <th>Status</th>
    <th>Date of order</th>
    <th>Validation</th>
    <th>Recieved/Processing</th>
    <th>Confirming</th>
    <th>Ready for Delivery</th>
     <th>In Delivery</th>
   
    <th>Delivered</th>
     <th>Cancled</th>
     <th>Delayed</th>
     <th>Finalized</th>

</tr>
  <?php
    $order_query = mysqli_query($con, "SELECT * FROM `ordere` WHERE id = '$id'") or die('query failed');
   
    if(mysqli_num_rows($order_query) > 0){
     while($fetch_order = mysqli_fetch_assoc($order_query)){
   ?>
  <tr>
    <td><?php echo $fetch_order['number'];?></td>
    <td><?php echo $fetch_order['valid'];?></td>

    <td><?php echo !empty($fetch_order['dat']) ? date('M j, Y g:i A', strtotime($fetch_order['dat'])) : '';?></td>
    <td><?php echo !empty($fetch_order['valide_date']) ? date('M j, Y g:i A', strtotime($fetch_order['valide_date'])) : '';?></td>
    <td><?php echo !empty($fetch_order['re_pro_date']) ? date('M j, Y g:i A', strtotime($fetch_order['re_pro_date'])) : '';?></td>
    <td><?php echo !empty($fetch_order['confirm_date']) ? date('M j, Y g:i A', strtotime($fetch_order['confirm_date'])) : '';?></td>
    <td><?php echo !empty($fetch_order['rd_f_delv_date']) ? date('M j, Y g:i A', strtotime($fetch_order['rd_f_delv_date'])) : '';?></td>
    <td><?php echo !empty($fetch_order['in_delv_date']) ? date('M j, Y g:i A', strtotime($fetch_order['in_delv_date'])) : '';?></td>
      <td><?php echo !empty($fetch_order['delivred_date']) ? date('M j, Y g:i A', strtotime($fetch_order['delivred_date'])) : '';?></td>
        <td><?php echo !empty($fetch_order['canceled_date']) ? date('M j, Y g:i A', strtotime($fetch_order['canceled_date'])) : '';?></td>
          <td><?php echo !empty($fetch_order['delayed_date']) ? date('M j, Y g:i A', strtotime($fetch_order['delayed_date'])) : '';?></td>
          <td><?php echo !empty($fetch_order['finalized_date']) ? date('M j, Y g:i A', strtotime($fetch_order['finalized_date'])) : '';?></td>
  <td> 
  	<?php
  }
}
  	?>

