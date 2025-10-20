<?php
// ------------------------------
// Bootstrapping & no-cache
// ------------------------------
session_start(); // ensure session is available for all actions

// Force no-cache for this controller itself
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Helper for cache-busted redirects (hard refresh)
function hard_redirect(string $url): void {
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    header('Location: ' . $url . $sep . '_ts=' . time(), true, 303); // 303 See Other
    exit;
}

// ------------------------------
// Connections & deps
// ------------------------------
include '../settings/db.php';

// api stuff
require 'api/apis.php';

// twitter
require 'twitter/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
$connection = new TwitterOAuth($twitter_api_key, $twitter_api_secret, $twitter_access_token, $twitter_access_token_secret);
$content = $connection->get("account/verify_credentials");

// ------------------------------
// Router
// ------------------------------
$act = isset($_GET['act']) ? $_GET['act'] : '';

// ------------------------------
// Auth: login
// ------------------------------
if ($act === 'login_check') {
    $username = $_POST['username'] ?? '';
    $password = md5($_POST['password'] ?? ''); // NOTE: consider password_hash/verify later

    $data = mysqli_query($con, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $cek  = mysqli_num_rows($data);

    if ($cek > 0) {
        $user_data = mysqli_fetch_array($data);
        
        if ($user_data['admin?'] !== 'yes') {
            hard_redirect('login.php?pesan=access_denied');
        }
        
        $_SESSION['username'] = $username;
        $_SESSION['type']     = $user_data['admin?'];
        $_SESSION['user_id']  = $user_data['id'];
        $_SESSION['status']   = 'login';
        hard_redirect('movie_manager.php?page=data');
    } else {
        hard_redirect('login.php?pesan=error');
    }
}

// ------------------------------
// Auth: logout
// ------------------------------
if ($act === 'logout') {
    session_destroy();
    hard_redirect('login.php?pesan=logout');
}

// ------------------------------
// Edit movie
// ------------------------------
if ($act === 'editmovie') {
    $title      = $_POST['title'] ?? '';
    $cat_id     = $_POST['cat_id'] ?? '';
    $group_id   = $_POST['group_id'] ?? '';
    $region_id  = $_POST['region_id'] ?? '';
    $short_desc = $_POST['short_desc'] ?? '';
    $ad_link    = $_POST['ad_link'] ?? '';
    $id         = $_POST['id'] ?? '';
    $trailer    = $_POST['trailer'] ?? '';
    $long_desc  = mysqli_real_escape_string($con, $_POST['long_desc'] ?? '');
    $video_type = $_POST['video_type'] ?? '';
    $price      = $_POST['price'] ?? '';
    $unit       = $_POST['unit'] ?? '';

    // Thumbnail upload
    $file_loc_t = $_FILES['tupload']['tmp_name'] ?? '';
    if ($file_loc_t) {
        $thumbnail = rand(0, 999) . '_' . $_FILES['tupload']['name'];
        $folder_t  = "../uploads/$thumbnail";
        move_uploaded_file($file_loc_t, $folder_t);
    } else {
        $thumbnail = $_POST['tupload_old'] ?? '';
    }

    // Ad image upload
    $ad_img_post = $_FILES['ad_img']['tmp_name'] ?? '';
    if ($ad_img_post) {
        $ad_img   = rand(0, 999) . '_ad_' . $_FILES['ad_img']['name'] . 'jpg'; // (kept as in original)
        $folder_ad = "../uploads/$ad_img";
        move_uploaded_file($ad_img_post, $folder_ad);
    } else {
        $ad_img = $_POST['ad_img_old'] ?? '';
    }

    // Video type
    if ($video_type === 'embed') {
        $video_types = '1';
        $video       = $_POST['embed'] ?? '';
    } elseif ($video_type === 'upload') {
        $video_types = '2';
        $file_loc_v  = $_FILES['vupload']['tmp_name'] ?? '';
        if ($file_loc_v) {
            $video    = rand(0, 999) . '_' . $_FILES['vupload']['name'];
            $folder_v = "../uploads/$video";
            move_uploaded_file($file_loc_v, $folder_v);
        } else {
            $video = $_POST['vupload_old'] ?? '';
        }
    } else {
        $video_types = '0';
        $video       = '';
    }

    $query = "UPDATE movies 
              SET cat_id='$cat_id',
                  region_id='$region_id',
                  group_id='$group_id',
                  title='$title',
                  short_desc='$short_desc',
                  long_desc='$long_desc',
                  thumbnail='$thumbnail',
                  video_type='$video_types',
                  video='$video',
                  trailer='$trailer',
                  ad_img='$ad_img',
                  ad_link='$ad_link',
                  price='$price',
                  unit='$unit'
              WHERE movie_id='$id'";

    $result = mysqli_query($con, $query);
    if ($result) {
        hard_redirect('movie_manager.php?page=data&DONE_EDIT=1');
    }
}

// ------------------------------
// Add movie
// ------------------------------
if ($act === 'add') {
    $title      = $_POST['title'] ?? '';
    $cat_id     = $_POST['cat_id'] ?? '';
    $group_id   = $_POST['group_id'] ?? '';
    $region_id  = $_POST['region_id'] ?? '';
    $short_desc = $_POST['short_desc'] ?? '';
    $trailer    = $_POST['trailer'] ?? '';
    $ad_link    = $_POST['ad_link'] ?? '';
    $added_by   = $_SESSION['user_id'] ?? null; // session is active from the top
    $long_desc  = mysqli_real_escape_string($con, $_POST['long_desc'] ?? '');
    $video_type = $_POST['video_type'] ?? '';
    $price      = $_POST['price'] ?? '';
    $unit       = $_POST['unit'] ?? '';

    // Thumbnail upload (required in original)
    $file_loc_t = $_FILES['tupload']['tmp_name'] ?? '';
    $thumbnail  = rand(0, 999) . '_' . ($_FILES['tupload']['name'] ?? ('thumb_' . time()));
    $folder_t   = "../uploads/$thumbnail";
    if ($file_loc_t) {
        move_uploaded_file($file_loc_t, $folder_t);
    }

    // Ad image upload
    $ad_img_post = $_FILES['ad_img']['tmp_name'] ?? '';
    $ad_img      = rand(0, 999) . '_ad_' . ($_FILES['ad_img']['name'] ?? ('ad_' . time()));
    $folder_ad   = "../uploads/$ad_img";
    if ($ad_img_post) {
        move_uploaded_file($ad_img_post, $folder_ad);
    }

    // Video type
    if ($video_type === 'embed') {
        $video_types = '1';
        $video       = $_POST['embed'] ?? '';
    } elseif ($video_type === 'upload') {
        $video_types = '2';
        $file_loc_v  = $_FILES['vupload']['tmp_name'] ?? '';
        $video       = rand(0, 999) . '_' . ($_FILES['vupload']['name'] ?? ('video_' . time()));
        $folder_v    = "../uploads/$video";
        if ($file_loc_v) {
            move_uploaded_file($file_loc_v, $folder_v);
        }
    } else {
        $video_types = '0';
        $video       = '';
    }

    $query = $con->query("INSERT INTO movies 
        (title, cat_id, short_desc, long_desc, thumbnail, video_type, video, group_id, region_id, trailer, ad_img, ad_link, added_by, price, unit)
        VALUES
        ('$title', '$cat_id', '$short_desc', '$long_desc', '$thumbnail', '$video_types', '$video', '$group_id', '$region_id', '$trailer', '$ad_img', '$ad_link', '$added_by', '$price', '$unit')");

    if ($query) {
        // (Optional) If you MUST run autopost first, keep this and make autopost redirect back using ?return_to=
        // hard_redirect('autopost/index.php?return_to=' . urlencode('movie_manager.php?page=data&DONE_ADD=1'));

        // If autopost is not required, or autopost itself will be triggered elsewhere, go straight back:
        hard_redirect('movie_manager.php?page=data&DONE_ADD=1');
    }
}

// ------------------------------
// Delete movie
// ------------------------------
if ($act === 'del') {
    $id = intval($_GET['id'] ?? 0);

    if ($id > 0) {
        if ($con->query("DELETE FROM movies WHERE movie_id = $id") === TRUE) {
            // Success
            hard_redirect('movie_manager.php?page=data&DONE_DELETE=1');
        } else {
            // Log actual DB error for debugging
            error_log("Movie delete failed: " . $con->error);
            hard_redirect('movie_manager.php?page=data&ERROR=DB_ERROR');
        }
    } else {
        hard_redirect('movie_manager.php?page=data&ERROR=INVALID_ID');
    }
}


// ------------------------------
// Pin off
// ------------------------------
if ($act === 'pinoff') {
    $id = $_GET['id'] ?? '';
    $query = $con->query("UPDATE movies SET featured=0 WHERE movie_id='$id'");
    if ($query) {
        hard_redirect('movie_manager.php?page=data&PIN_OFF=1');
    }
}

// ------------------------------
// Pin on
// ------------------------------
if ($act === 'pinon') {
    $id    = $_GET['id'] ?? '';
    $date2 = date("Y-m-d h:i:s");
    $query = $con->query("UPDATE movies SET pin_unpin_time='$date2', featured='1' WHERE movie_id='$id'");
    if ($query) {
        hard_redirect('movie_manager.php?page=data&PIN_ON=1');
    }
}

// If nothing matched, you may want to redirect somewhere safe:
if (!headers_sent()) {
    hard_redirect('movie_manager.php?page=data');
}
?>