<?php
require "../settings/db.php";
session_start();
$user_id = $_SESSION['user_id'];

if((!isset($user_id)) || $user_id < 0 ){
   header('location:../LogReg/login.php');
};
function GetUserename($con, $id)
   {
    $take_username_query = "SELECT * FROM user_info WHERE id = $id";
    $run_user_query = mysqli_query($con, $take_username_query);
    $username = mysqli_fetch_array($run_user_query);
    return $username;
  } 

if (isset($_GET['serch'])) {
    $keyword = $_GET['keywrd'];
    $qury_string = "SELECT * FROM movies WHERE title LIKE '%$keyword%'";
    
}
else{
    $qury_string = "SELECT * FROM movies";

}
$result = mysqli_query($con, $qury_string);

    
    
    
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>420 Vallarta Product Inventory</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Contact-Form-by-Moorcam.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search-search-table.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search.css">
    <style type="text/css">
<!--
.style1 {color: #FFFFFF}
.style3 {color: #000000}
-->
    </style>
</head>

<body style="margin: 10px;">
    <div class="col-md-12 search-table-col" style="margin-top: 0px;">
      <title></title>
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
<div align="center">
  <h2><span class="style1"><span class="style3">420 Vallarta Product Inventory</span></span></h2>
</div>
<br>
<label class="form-label" style="font-size: 25px;letter-spacing: 1px;margin-top: 10px;"><span class="col-md-12 search-table-col" style="margin-top: 0px;"><img src="/images/420 vallarta inventory icon off.png" alt="Product Inventory 420 Vallarta" width="84" height="84" border="0"> <a href="../admin.php"><img src="/images/420 Vallarta Orders Icon.png" alt="Client Orders 420 Vallarta" width="83" height="83" border="0"></a></span></label>
        <a href="http://420vallarta.com/movie.php" target="_blank"><img src="/images/cashier 420 vallarta.png" alt="Cashier 420 Vallarta" name="Cashier" width="85" height="85" border="0" id="Cashier"></a>
        <div class="form-group pull-right col-lg-4">
          <form action="" method="get">
            <div align="center">
              <input type="text" class="search form-control" name="keywrd" placeholder="Search by typing here.." style="border-width: 3px;border-color: rgb(42,86,31);">
              <button class="btn btn-primary" name="serch" type="submit" style="background: rgb(42,86,31);">Search</button>
            </div>
          </form>
        </div>
        <p>&nbsp;</p>
      <div class="table-responsive table table-hover table-bordered results">
            <table class="table table-hover table-bordered">
                <thead class="bill-header cs">
                    <tr style="background: #2a561f;">
                        <th id="trs-hd-1" class="col-lg-1">Product</th>
                        <th id="trs-hd-5" class="col-lg-1">price</th>
                        <th id="trs-hd-2" class="col-lg-2">Inventory add</th>
                        <th id="trs-hd-3" class="col-lg-3">Remaining in stock</th>
                        <th id="trs-hd-4" class="col-lg-2">Add Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(mysqli_num_rows($result) == 0)
                    {
                        ?>
                        <tr class="warning no-result">
                        <td colspan="12"><i class="fa fa-warning"></i>&nbsp; No Result !!!</td>
                    </tr>
                    <?php
                    }
                    else {
                        while ($product  = mysqli_fetch_assoc($result)) {
                            $id_prod = $product['movie_id'];
                            $qury_inv_string = "SELECT * FROM inventory WHERE product_id = $id_prod";
                        $result2 = mysqli_query($con, $qury_inv_string); 
                        
                           ?>
                                <tr></tr>
                    <tr style="color: rgb(0,0,0);">
                        <td><?php echo $product['title']; ?>&nbsp;</td>
                        <td style="color: var(--bs-table-color);"><?php echo $product['price'];?></td>
                        <td><div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr style="background: #2a561f;color: rgb(248,248,248);">
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Qnt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($inv  = mysqli_fetch_assoc($result2))
                                        {
                                            $user = GetUserename($con, $inv['user_id']);
                                            
                                                ?>
                                                    <tr>
                                            <td><?php echo $user['name'];?></td>
                                            <td><?php echo $inv['date'];?></td>
                                            <td><?php echo $inv['qnt_add'];?></td>
                                        </tr>
                                                <?php
                                            
                                        }
                                        
                                        ?>
                                    
                                        
                                    </tbody>
                                </table>
                            </div>
                        </td>
                        <td><?php echo $product['unit'];?></td>
                        <td><a href="add%20_new_qnt.php?id=<?php echo $product['movie_id'];?>">Add New Quantity&nbsp;</a></td>
                    </tr>
                           <?php
                        }
                      
                    }
                    ?>
                    
                    
                </tbody>
            </table>
      </div>
</div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/Table-With-Search.js"></script>
</body>

</html>