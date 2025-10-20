<?php
require_once('settings/db.php');
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:LogReg/login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

if ($user_id > 0) {
    $user_res = mysqli_query($con, "SELECT * FROM user_info WHERE id=$user_id LIMIT 1");
    if (!$user_res || mysqli_num_rows($user_res) == 0) {
        session_destroy();
        header('location:LogReg/login.php?pesan=invalid_staff');
        exit;
    }
    $user_data = mysqli_fetch_array($user_res);
} else {
    header('location:LogReg/login.php?pesan=staff_only');
    exit;
}

?>

 <!--Favicon-->
    <link rel="shortcut icon" href="Favi/favicon.ico" >
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
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
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
.style1 {color: #FFFFFF}
.style2 {color: #000000}
</style>
<title>Client Orders 420 Vallarta</title></head>
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
<?php
// Display success and error messages
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<strong>Success!</strong> ' . htmlspecialchars(urldecode($_GET['success']));
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<strong>Error!</strong> ' . htmlspecialchars(urldecode($_GET['error']));
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}
?>

<h2 align="center" class="style2">420 Vallarta Orders</h2>
<br>
<br>
<a href="inventory/index.php">
  <img src="images/420 vallarta inventory icon.png"
       alt="Product Inventory 420 Vallarta" width="84" height="84" border="0">
</a>
 <img src="images/420 Vallarta Orders off.png" alt="Client Orders 420 Vallarta" width="83" height="83"> <a href="http://420vallarta.com/movie.php" target="_blank"><img src="/images/cashier 420 vallarta.png" alt="Cashier 420 Vallarta" name="Cashier" width="85" height="85" border="0" id="Cashier"></a>
<br>
<br>
<table width="97%" border="0" align="right">
  <tr>
    <td width="60%"><form action="" method="GET">
      <div class="row">
        <div class="col-md-4">
          <label for="first_date">
          <div align="center">From Date</div>
          </label>
          <div align="center">
            <input type="date" name="first_date" class="form-control">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <label for="second_date">
          <div align="center">To Date</div>
          </label>
          <div align="center">
            <input type="date" name="second_date" class="form-control">
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <div align="center">
            <button type="submit" class="btn btn-primary">Filter Search</button>
          </div>
        </div>
      </div>
    </form></td>
    <td width="40%"><form action="" method="GET">
      <div class="mb-1">
        <label for="exampleInputEmail1" class="form-label">Search</label>
        <input type="text" class="form-control" value="<?php if(isset($_GET['Search'])){
      echo $_GET['Search'];
    } ?>" id="Search" name="Search">
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form></td>
  </tr>
</table>
<br>
<br>
<table border="0" id="customers">
  <tr>
    <th>Username</th>
    <th>WhatsApp number</th>
    <th>Email</th>
    <th>Reference Code</th>
    <th>Adress</th>
    <th>Order</th>
    <th>Payment Method </th>
    <th>Total</th>
    <th>Pending</th>
   
    <th>Status</th>
     <th>More information</th>
  </tr>

<?php
  @$src = $_GET['Search'];
 if(isset($src))
 {
    $query = "SELECT * FROM ordere WHERE CONCAT(name,email, pin_code,valid) LIKE '%$src%' ";
    $query_run = mysqli_query($con, $query);
    if(mysqli_num_rows($query_run) > 0)
    {  
      foreach ($query_run as $items)
      {
        ?>
              <tr>
          <td><?php echo $items['name'];?></td>
          <td><?php echo $items['number'];?></td>
          <td><?php echo $items['email'];?></td>
          <td><?php echo $items['pin_code'];?></td>
          <td><?php echo $items['adresse'];?></td>
          <td><?php echo $items['total_products'];?></td>
          <td><?php echo $items['method'];?></td>
          <td><?php echo $items['total_price'];?></td>
         <td> 
                <form action="validate.php?id=<?php echo $items['id'];?>" method="Post">
                  <select name="status">
                    <option></option>
                    <option value="NEW!!! (Validate)">NEW!!! (Validate)</option>
                    <option value="Recieved/Processing">Recieved/Processing</option>
                    <option value="Confirming">Confirming</option>
                    <option value="Ready for Delivery">Ready for Delivery</option>
                    <option value="In Delivery">In Delivery</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancled">Cancled</option>
                    <option value="Delayed">Delayed</option>
                  </select>
                    <input type="submit" name="update">
              </form>          </td>

         <td><?php echo $items['valid'] == 0 ? '' : $items['valid'];?></td>
          <td><a href="oreder_info.php?id=<?php echo $items['id'];?>">Order Details </a>/<a href="Edit_order.php?id=<?php echo $items['id'];?>">Edit</a></td>
        </tr>
        <?php
      }
    }
    else
    {

    }
 }elseif(isset($_GET['first_date']) && isset($_GET['second_date']))
 {
    $fr_date = $_GET['first_date'];
    $to_date = $_GET['second_date'];

    $query_date = "SELECT * FROM ordere WHERE dat BETWEEN '$fr_date' AND '$to_date' ";
    $query_date_run = mysqli_query($con, $query_date);

    if(mysqli_num_rows($query_date_run) > 0)
    {
      foreach($query_date_run as $itm)
      {
        ?>
            <tr>
              <td><?php echo $itm['name'];?></td>
              <td><?php echo $itm['number'];?></td>
              <td><?php echo $itm['email'];?></td>
              <td><?php echo $itm['pin_code'];?></td>
              <td><?php echo $itm['adresse'];?></td>
              <td><?php echo $itm['total_products'];?></td>
              <td><?php echo $itm['method'];?></td>
              <td><?php echo $itm['total_price'];?></td>
            <td> 
                <form action="validate.php?id=<?php echo $itm['id'];?>" method="Post">
                  <select name="status">
                    <option></option>
                    <option value="NEW!!! (Validate)">NEW!!! (Validate)</option>
                    <option value="Recieved/Processing">Recieved/Processing</option>
                    <option value="Confirming">Confirming</option>
                    <option value="Ready for Delivery">Ready for Delivery</option>
                    <option value="In Delivery">In Delivery</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancled">Cancled</option>
                    <option value="Delayed">Delayed</option>
                  </select>
                 <input type="submit" name="update">
              </form>          </td>

              <td><?php echo $itm['valid'] == 0 ? '' : $itm['valid'];?></td>
               <td><a href="oreder_info.php?id=<?php echo $itm['id'];?>">Order Details </a>/<a href="Edit_order.php?id=<?php echo $itm['id'];?>">Edit</a></td>
            </tr>
        <?php
      }
    }
    else
    {

    }
 }
 else
 {
  ?>
  
  <?php
    $order_query = mysqli_query($con, "SELECT * FROM `ordere` ORDER BY dat DESC") or die('query failed');
   
    if(mysqli_num_rows($order_query) > 0){
     while($fetch_order = mysqli_fetch_assoc($order_query)){
   ?>
  <tr>
    <td><?php echo $fetch_order['name'];?></td>
    <td><?php echo $fetch_order['number'];?></td>
    <td><?php echo $fetch_order['email'];?></td>
    <td><?php echo $fetch_order['pin_code'];?></td>
    <td><?php echo $fetch_order['adresse'];?></td>
    <td><?php echo $fetch_order['total_products'];?></td>
    <td><?php echo $fetch_order['method'];?></td>
    <td><?php echo $fetch_order['total_price'];?></td>
  <td> 
                <form action="validate.php?id=<?php echo $fetch_order['id'];?>" method="Post">
                  <select name="status">
                    <option></option>
                    <option value="NEW!!! (Validate)">NEW!!! (Validate)</option>
                    <option value="Recieved/Processing">Recieved/Processing</option>
                    <option value="Confirming">Confirming</option>
                    <option value="Ready for Delivery">Ready for Delivery</option>
                    <option value="In Delivery">In Delivery</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancled">Cancled</option>
                    <option value="Delayed">Delayed</option>
                  </select>
                  <input type="submit" name="update">
              </form>    </td>

   <td><?php echo $fetch_order['valid'] == 0 ? '' : $fetch_order['valid'];?></td>
    <td>
        <a href="oreder_info.php?id=<?php echo $fetch_order['id'];?>">Order Details</a> |
        <a href="Edit_order.php?id=<?php echo $fetch_order['id'];?>">Edit</a><br>
        <a href="finalize_order.php?order_id=<?php echo $fetch_order['id'];?>" class="btn btn-success btn-sm mt-1">📧 Finalize & Send Receipt</a>
    </td>
  </tr>
  <?php
         }
        }
  ?>
</table>
  <?php
 }
 
?>




</body>
</html>
<?php

?>

<!-- Bootstrap JS for alert dismissal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>