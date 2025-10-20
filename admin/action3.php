<?php
// connections
    include '../settings/db.php';

    //api stuff
require 'api/apis.php';

//twitter
require 'twitter/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
$connection = new TwitterOAuth($twitter_api_key, $twitter_api_secret, $twitter_access_token, $twitter_access_token_secret);
$content = $connection->get("account/verify_credentials");



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
		$user_data=mysqli_fetch_array($data);
        $_SESSION['username'] = $username;
        $_SESSION['type'] = $user_data['admin?'];
        $_SESSION['user_id'] = $user_data['id'];
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

if ($act == 'editmovie') {

    $title          = $_POST['title'];
    $cat_id         = $_POST['cat_id'];
    $group_id       = $_POST['group_id'];
	$region_id       = $_POST['region_id'];
    $short_desc     = $_POST['short_desc'];
    $ad_link     = $_POST['ad_link'];
    $id    = $_POST['id'];

    $trailer    = $_POST['trailer'];
    $long_desc      = mysqli_real_escape_string($con,$_POST['long_desc']);
    $video_type     = $_POST['video_type'];
    //thumbnail upload
    $file_loc_t    = $_FILES['tupload']['tmp_name'];
    $price=$_POST['price'];
    if($file_loc_t != ""){
        $thumbnail      = rand(0,999).'_'.$_FILES['tupload']['name'];
        $folder_t = "../uploads/$thumbnail";
        move_uploaded_file($file_loc_t,"$folder_t");
    } else {
        $thumbnail = $_POST['tupload_old'];
    }

    //
    $ad_img_post    = $_FILES['ad_img']['tmp_name'];
    if($ad_img_post != ""){
        $ad_img      = rand(0,999).'_ad_'.$_FILES['ad_img']['name'].'jpg';
        $folder_ad = "../uploads/$ad_img";
        move_uploaded_file($ad_img_post,"$folder_ad");
    }
    else{
        $ad_img = $_POST['ad_img_old'];
    }

    if($video_type == 'embed'){
        $video_types = '1';
        $video       = $_POST['embed'];
    } else if($video_type =='upload'){
        $video_types = '2';
        //video upload
        $file_loc_v    = $_FILES['vupload']['tmp_name'];
        if($file_loc_v != ""){
            $video         = rand(0,999).'_'.$_FILES['vupload']['name'];
            $folder_v      = "../uploads/$video";
            move_uploaded_file($file_loc_v,"$folder_v");
        } else {
            $video         = $_POST['vupload_old'];
        }
    }

    $query = "UPDATE movies SET cat_id='$cat_id', region_id = '$region_id', group_id='$group_id',title='$title',short_desc='$short_desc',long_desc='$long_desc',thumbnail='$thumbnail',video_type = '$video_types',video = '$video', trailer='$trailer',ad_img='$ad_img', ad_link='$ad_link', price='$price' WHERE movie_id ='$id'";
    $result = mysqli_query($con,$query);
    if($result){
        header("location: movie_manager.php?page=data&DONE_EDIT");
    }
}

if ($act == 'add') {
    $title          = $_POST['title'];
    $cat_id         = $_POST['cat_id'];
    $group_id       = $_POST['group_id'];
	$region_id       = $_POST['region_id'];
    $short_desc     = $_POST['short_desc'];
    $trailer     = $_POST['trailer'];
    $ad_link     = $_POST['ad_link'];
	$added_by = $_SESSION['user_id'];
    $long_desc      = mysqli_real_escape_string($con,$_POST['long_desc']);
    $video_type     = $_POST['video_type'];
    $price=$_POST['price'];
    //thumbnail upload
    $file_loc_t    = $_FILES['tupload']['tmp_name'];
    $thumbnail      = rand(0,999).'_'.$_FILES['tupload']['name'];
    $folder_t = "../uploads/$thumbnail";
    move_uploaded_file($file_loc_t,"$folder_t");
    $ad_img_post    = $_FILES['ad_img']['tmp_name'];
        $ad_img      = rand(0,999).'_ad_'.$_FILES['ad_img']['name'];
        $folder_ad = "../uploads/$ad_img";
        move_uploaded_file($ad_img_post,"$folder_ad");

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
    
    $query = $con->query("INSERT INTO movies (title,cat_id,short_desc,long_desc,thumbnail,video_type,video,group_id, region_id, trailer,ad_img,ad_link,added_by) VALUES ('$title','$cat_id','$short_desc','$long_desc','$thumbnail','$video_types','$video','$group_id','$region_id','$trailer','$ad_img','$ad_link','$added_by', price='$price')");
    if($query){

        $q = "SELECT title,MAX(id) as id FROM movies";
        $r = mysqli_fetch_assoc(mysqli_query($con,$q));
        //echo $r['id'];
        header('Location: autopost/index.php');
    }
}

if ($act == 'del') {
    $id = $_GET['id'];
    $query = $con->query("DELETE FROM movies WHERE movie_id = '$id'");
    if($query){
        header("location:movie_manager.php?page=data");
    }
}

if ($act == 'pinoff') {
    $id = $_GET['id'];
    $query = $con->query("UPDATE movies SET featured = 0 WHERE movie_id = '$id'");
    if($query){
        header("location:movie_manager.php?page=data");
    }
}
if ($act == 'pinon') {
    $id = $_GET['id'];
    $date2 = date("Y-m-d h:i:s");
    $query = $con->query("UPDATE movies SET pin_unpin_time ='$date2',featured='1' WHERE movie_id = '$id'");
    if($query){
        header("location:movie_manager.php?page=data");
    }
}