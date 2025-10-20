<?php
require "../settings/db.php";
session_start();
$user_id = $_SESSION['user_id'];

if((!isset($user_id)) || $user_id < 0 ){
    header('location:../LogReg/login.php');
 };

$movie_id = $_GET['id'];
$product_name_query = "SELECT * FROM movies WHERE movie_id = $movie_id";
$run_prod_query = mysqli_query($con, $product_name_query);
$product_name = mysqli_fetch_array($run_prod_query);
if(isset($_POST['Add']))
{
    $qnt = $_POST['qnt'];
    $new_qnt = ($product_name['unit'] + $qnt);
    $date = date('Y-m-d H:i:s');
    $upadate_query = "UPDATE movies SET unit = $new_qnt WHERE movie_id = $movie_id";
    $run_upd_query = mysqli_query($con, $upadate_query);
    $insert_new_qnt = "INSERT INTO inventory (user_id, product_id, qnt_add, date) VALUES ('$user_id', '$movie_id', '$qnt', '$date')";
    $run_inst_query = mysqli_query($con, $insert_new_qnt);
    echo '<script>alert("Quantity Added")</script>';
    
}elseif (isset($_POST['return'])) {
    header('location:index.php');
}
?><br>
<br>
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
    <title>inventory</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Contact-Form-by-Moorcam.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search-search-table.css">
    <link rel="stylesheet" href="assets/css/Table-With-Search.css">
</head>

<body style="background: rgb(244,244,244);filter: contrast(104%) saturate(95%);backdrop-filter: blur(13px);">
    <section class="shadow contact-clean" style="background: #d4f1cd;backdrop-filter: opacity(0.79) brightness(34%);border-width: 25px;">
        <form class="bg-light border rounded border-secondary shadow-lg" method="post" style="background: rgb(248,248,249);" action="">
            <h2 class="text-center">Add Quantity</h2>
            <div class="form-group mb-3"><input class="form-control" type="text" value="<?php echo $product_name['title']; ?>" name="name" placeholder="Product Name"></div>
            <div class="form-group mb-3"><input class="form-control" type="number" name="qnt" placeholder="Quantity" inputmode=""></div>
            <div class="form-group mb-3"><button class="btn btn-primary" name="Add" type="submit">Add</button></div>
            <div class="form-group mb-3"><button class="btn btn-primary" name="return"  type="submit">PRODUCT LIST</button></div>
        </form>
    </section>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/Table-With-Search.js"></script>
</body>

</html>