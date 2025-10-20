<?php
// connections
    include '../settings/db.php';
// GET action URL

$act = $_GET['act'];

if ($act == 'login_check') {
    // activate session
    session_start();

    // get data from Login Form
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    // Check data from table users
    $data = mysqli_query($con, "select * from users where username='$username' and password='$password'");

    // check data if exists
    $cek = mysqli_num_rows($data);

    if ($cek > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['status'] = "login";
        header("location:movie_manager.php?page=data");
    } else {
        header("location:login.php?pesan=error");
    }
}

if ($act == 'logout') {
    // activate session
    session_start();

    // destroy session
    session_destroy();

    // back to login form
    header("location:login.php?pesan=logout");
}

if ($act == 'add') {
    $title          = $_POST['title'];
    $cat_id         = $_POST['cat_id'];
    $group_id       = $_POST['group_id'];
    $short_desc     = $_POST['short_desc'];
    $long_desc      = mysqli_real_escape_string($con,$_POST['long_desc']);
    $video_type     = $_POST['video_type'];
    //thumbnail upload
    $file_loc_t    = $_FILES['tupload']['tmp_name'];
    $thumbnail      = rand(0,999).'_'.$_FILES['tupload']['name'];
    $folder_t = "../uploads/$thumbnail";
    move_uploaded_file($file_loc_t,"$folder_t");

    if($video_type == 'embed'){       
        $video_types = '1';
        $video       = $_POST['embed'];
    } else if($video_type =='upload'){
        $video_types = '2';
        //video upload
        $file_loc_v    = $_FILES['vupload']['tmp_name'];
        $video         = rand(0,999).'_'.$_FILES['vupload']['name'];
        $folder_v      = "../uploads/$video";
        move_uploaded_file($file_loc_v,"$folder_v");
    }
    
    $query = $con->query("INSERT INTO movies (title,cat_id,short_desc,long_desc,thumbnail,video_type,video,group_id) VALUES ('$title','$cat_id','$short_desc','$long_desc','$thumbnail','$video_types','$video','$group_id')");
    if($query){
        header("location:movie_manager.php?page=data");
    }
}

if ($act == 'del') {
    $id = $_GET['id'];
    $query = $con->query("DELETE FROM movies WHERE id = '$id'");
    if($query){
        header("location:movie_manager.php?page=data");
    }
}