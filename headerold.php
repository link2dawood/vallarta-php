<?php require_once('settings/db.php');
include 'settings/function.php';
session_start();

if(empty($_SESSION['user_id'])){
    $guest_id = -(abs(crc32(uniqid())));
    $_SESSION['user_id'] = $guest_id;
    $user_id = $_SESSION['user_id'];
}else{
    $user_id = $_SESSION['user_id'];
}
if(isset($_GET['logout'])){
    unset($user_id);
    session_destroy();
    header('location:LogReg/Login.php');
 };
global $con;
?>
<!DOCTYPE html>
<html lang="en">

<head>

<!-- Google tag (gtag.js) --> <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11226186341"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'AW-11226186341'); </script>

    <title>420 Puerto Vallarta - Marijuana Cannabis Delivery. Order Online in Puerto Vallarta</title>
    <link rel="canonical" href="https://420vallarta.com/" />
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name=keywords content="puerto vallarta, Nuevo Vallarta, deliver, cannabis, marijuana, Dispensary, buy, online, 420 Puerto Vallarta "/>

    <meta name="description" content="420 Vallarta A Platform to Connect Marijuana Cannabis Connoisseurs. Order Marijuana Cannabis Online in Puerto Vallarta. ">

    <meta name="keywords" content="puerto vallarta, Nuevo Vallarta, deliver, cannabis, marijuana, dispensary, buy, online, 420 Vallarta, 420 Puerto Vallarta">

    <meta name="author" content="420 Puerto Vallarta">
    <meta name="application-name" content="420 cannabis marijuana Puerto Vallarta Nuevo Vallarta delivery"/>

    <?php
    if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $con->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->bind_param("i", $id);  // assuming movie_id is integer
    $stmt->execute();
    $result = $stmt->get_result();
    $m = $result->fetch_assoc();
}
    ?>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://420vallarta.com">
    <meta property="og:title" content="420 Puerto Vallarta - Marijuana Cannabis Connoisseurs Dispensary">
    <meta property="og:description" content="Order Marijuana Cannabis Online in Puerto Vallarta and Nuevo Vallarta">
    <meta property="og:image" content="http://420vallarta.com/uploads/PV emblem round.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="http://420vallarta.com">
    <meta property="twitter:title" content="420 Puerto Vallarta - Marijuana Cannabis Connoisseurs Dispensary">
    <meta property="twitter:description" content="Order Marijuana Cannabis Online in Puerto Vallarta and Nuevo Vallarta">
    <meta property="twitter:image" content="http://420vallarta.com/uploads/PV emblem round.png">


    <link rel="canonical" href="https://420vallarta.com/">

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
	
	
	

    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300i,400,700" rel="stylesheet">
    <link rel="stylesheet" href="assets/front/fonts/icomoon/style.css">

    <link rel="stylesheet" href="assets/front/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/front/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/front/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/front/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/front/css/owl.theme.default.min.css">

    <link rel="stylesheet" href="assets/front/css/lightgallery.min.css">

    <link rel="stylesheet" href="assets/front/css/bootstrap-datepicker.css">

    <link rel="stylesheet" href="assets/front/fonts/flaticon/font/flaticon.css">

    <link rel="stylesheet" href="assets/front/css/swiper.css">

    <link rel="stylesheet" href="assets/front/css/aos.css">

    <link rel="stylesheet" href="assets/front/css/style.css">

    <script src="https://kit.fontawesome.com/be09261c1a.js" crossorigin="anonymous"></script>

    <style>
        .pagination {
            display: inline-block;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .style1 {font-size: 24px}
    </style>

<!-- Meta Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '236425002586041');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=236425002586041&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

</head>

<body>

<div class="site-wrap">

    <div class="site-mobile-menu">
        <div class="site-mobile-menu-header">
            <div class="site-mobile-menu-close mt-3">
                <span class="icon-close2 js-menu-toggle"></span>
            </div>
        </div>
        <div class="site-mobile-menu-body"></div>
    </div>




    <header class="site-navbar py-3 border-bottom" role="banner">

        <div class="container-fluid">
            <div class="row align-items-center">

                <div class="col-6 col-xl-2" data-aos="fade-down">
                    <h1 align="center" class="mb-0"><a href="index.php" class="text-black h2 mb-0"><img src="images/420 medal.png" alt="420 Puerto Vallarta" width="50" height="50" longdesc="http://420vallarta.com"></a><span class="style1"> </span><a href="index.php" class="text-black h2 mb-0"><span class="style1"><br>
                    420 Vallarta </span></a></h1>
                </div>
                <div class="col-10 col-md-8 d-none d-xl-block" data-aos="fade-down">
                    <nav class="site-navigation position-relative text-right text-lg-center" role="navigation">

                        <ul class="site-menu js-clone-nav mx-auto d-none d-lg-block">
                         
                          <?php
                          if(($user_id < 0) ){
                            ?>
                          <?php
                          }else{
                            ?>
                            <li>
                              <?php
                            
                          }
                          ?>
                            <li class=""><a href="menu.php">Menu</a><br>
                          </li>
                          <li class=""><a href="cart.php">Cart</a></li>
                            <li class=""><a href="movie.php">Products   </a></li>
                            <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Brand                                </a>
                              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                <?php
                                    $sql_group = "Select * from grp";
                                    $result_group = mysqli_query($con,$sql_group);
                                    while ($row = mysqli_fetch_assoc($result_group)){
                                    ?>
                                <a class="dropdown-item" href="movie.php?grp=<?php echo $row['id']?>"><?php echo $row['group_name']?></a>
                                <?php
                                    }
                                    ?>
                              </div>
                            </li>
                            <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Category                                </a>
                              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                <?php
                                    $sql_cat = "Select * from cat";
                                    $result_cat = mysqli_query($con,$sql_cat);
                                    while ($row = mysqli_fetch_assoc($result_cat)){
                                        ?>
                                <a class="dropdown-item" href="movie.php?cat=<?php echo $row['id']?>"><?php echo $row['cat_name']?></a>
                                <?php
                                    }
                                    ?>
                              </div>
                          </li>
                            
                            <li class=""><a href="contactus.php">Contact Us</a> </li>
                        </ul>
                    </nav>
                </div>

                <div class="col-6 col-xl-2 text-right" data-aos="fade-down">
                    <div class="d-none d-xl-inline-block">
                        <ul class="site-menu js-clone-nav ml-auto list-unstyled d-flex text-right mb-0" data-class="social">
                            <li>
                                <a href="https://www.facebook.com/profile.php?id=61553636732357" target="_blank" class="pl-0 pr-3"><span class="icon-facebook"></span></a>                            </li>
								<li>
                                <a href="https://www.youtube.com/@420vallarta" target="_blank" class="pl-3 pr-3"><span class="icon-youtube-play"></span></a>                            </li>
                            <li>
                                <a href="https://twitter.com/420Vallarta/" target="_blank" class="pl-3 pr-3"><span class="icon-twitter"></span></a>                            </li>
                            <li>
                                <a href="https://www.instagram.com/420.puertovallarta/" target="_blank" class="pl-3 pr-3"><span class="icon-instagram"></span></a>                            </li>
                            <li>
                                <a href="https://www.tumblr.com/blog/view/420puertovallarta/" target="_blank" class="pl-3 pr-3"><span class="icon-tumblr"></span></a>                            </li>
								
							<li>
								<a href="https://www.pinterest.com.mx/420puertovallarta/" target="_blank" class="pl-3 pr-3"><span class="icon-pinterest"></span></a>                            </li>
						
                        </ul>
                    </div>


                    <div class="d-inline-block d-xl-none ml-md-0 mr-auto py-3" style="position: relative; top: 3px;"><a href="#" class="site-menu-toggle js-menu-toggle text-black"><span class="icon-menu h3"></span></a></div>

                </div>

            </div>
        </div>

    </header>


