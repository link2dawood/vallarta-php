<?php
session_start();
require_once('../settings/db.php');
include '../settings/function.php';

if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['status'] != "login") {
    header("location:login.php?pesan=no_session");
    exit;
}

$users_id = $_SESSION['user_id'];

$user_res = mysqli_query($con, "SELECT * FROM users WHERE id=$users_id");
if (!$user_res || mysqli_num_rows($user_res) == 0) {
    session_destroy();
    header("location:login.php?pesan=invalid_user");
    exit;
}

$user_row = mysqli_fetch_array($user_res);
$action = $user_row['admin?'];

if (!$action) {
    session_destroy();
    header("location:login.php?pesan=access_denied");
    exit;
}

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>420 Cancun Admin</title>

    <!-- Custom fonts for this template -->
    <link href="../assets/back/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../assets/back/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="../assets/back/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

<!-- Page Wrapper --><div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="movie_manager.php?page=data">
            <div class="sidebar-brand-text mx-3"><img src="/images/PV emblem round.png" alt="420 Vallarta" width="55" height="55"><br>
              Admin<br>
            </div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
		<?php if($action=='yes') {?>
		
        <li class="nav-item">
            <a class="nav-link" href="movie_manager.php?page=data">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>420 Cancun  Manager</span></a>        </li>
        <li class="nav-item">
            <a class="nav-link" href="addcategory.php">
                <i class="fas fa-plus"></i>
                <span>Add Category</span></a>        </li>
        <li class="nav-item">
            <a class="nav-link" href="addgroup.php">
                <i class="fas fa-plus"></i>
                <span>Add Group</span></a>        </li>
        <li class="nav-item">
            <a class="nav-link" href="addregion.php">
                <i class="fas fa-plus"></i>
                <span>Add Region</span></a>        </li>
        <li class="nav-item">
            <a class="nav-link" href="addbrand.php">
                <i class="fas fa-tag"></i>
                <span>Add Brand</span></a>        </li>
		<li class="nav-item">
            <a class="nav-link" href="adduser.php">
                <i class="fas fa-plus"></i>
                <span>Add User</span></a>        </li>
		<li class="nav-item">
            <a class="nav-link" href="../receipt_settings.php">
                <i class="fas fa-cog"></i>
                <span>E-Receipt Settings</span></a>        </li>

		<?php } else { ?>

		<li class="nav-item">
            <a class="nav-link" href="movie_manager.php?page=data">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>420 Cancun  Manager</span></a>        </li>
		<?php } ?>
        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">

                    <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                    <li class="nav-item dropdown no-arrow d-sm-none">
                        <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-search fa-fw"></i>
                        </a>
                        <!-- Dropdown - Messages -->
                        <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                            <form class="form-inline mr-auto w-100 navbar-search">
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>

                    <!-- Nav Item - Alerts -->
                    <li class="nav-item dropdown no-arrow mx-1">

                        <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username']; ?></span>
                            <img class="img-profile rounded-circle" src="https://source.unsplash.com/QAB-WJcbgJk/60x60">
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a href="action.php?act=logout"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>

                </ul>

            </nav>
            <!-- End of Topbar -->