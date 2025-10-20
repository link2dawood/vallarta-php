<?php
include '../settings/db.php';
if (isset($_GET['id'])){
    $id = $_GET['id'];

}
$query = $con->query("SELECT * FROM `movies` JOIN grp ON movies.group_id = grp.id JOIN cat on movies.cat_id =cat.id WHERE movies.id = '$id'");
$m = mysqli_fetch_array($query);

if (isset($_POST['submit2'])){


header("location: movie_manager.php?page=data&id=$id&DONE...");
     echo $id             = $_POST['id'];
   /* $title          = $_POST['title'];
    $cat_id         = $_POST['cat_id'];
    $group_id       = $_POST['group_id'];
    $short_desc     = $_POST['short_desc'];
    $ad_link     = $_POST['ad_link'];

    $trailer    = $_POST['trailer'];
    $long_desc      = mysqli_real_escape_string($con,$_POST['long_desc']);
    $video_type     = $_POST['video_type'];
    //thumbnail upload
    $file_loc_t    = $_FILES['tupload']['tmp_name'];
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
        $ad_img      = rand(0,999).'_ad_'.$_FILES['ad_img']['name'];
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

    $query = "UPDATE movies SET cat_id='$cat_id', group_id = '$group_id',title='$title',short_desc='$short_desc',long_desc='$long_desc',thumbnail='$thumbnail',video_type = '$video_types',video = '$video', trailer='$trailer',ad_img='$ad_img', ad_link='$ad_link' WHERE id ='$id'";
    $result = mysqli_query($con,$query);
    if($result){
        header("location: movie_manager.php?page=data&id=$id&DONE");
    }*/

}
?>

<?php
include 'header.php';
?>
<form name="form1" enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="submit" name="submit2" class="btn btn-primary float-right" value="Edit movie">
<input name="id" value="<?= $id;?>" hidden>
    <div class="form-group">
        <label >Title</label>
        <input type="text" name="title" value="<?= $m['title'];?>" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Category</label>
        <select class="form-control" name="cat_id">
            <option value="<?= $m['cat_id'];?>" selected><?= $m['cat_name'];?></option>
            <?php
            $cate = $con->query("SELECT * FROM cat");
            while ($c = mysqli_fetch_array($cate)){
            ?>
            <option value="<?= $c['id'];?>"><?= $c['cat_name'];?></option>
            <?php  }?>
        </select>
    </div>
    <div class="form-group">
        <label>Group</label>
        <select class="form-control" name="group_id">
            <option value="<?= $m['group_id'];?>" selected><?= $m['group_name'];?></option>
            <?php
            $grp = $con->query("SELECT * FROM grp");
            while ($c = mysqli_fetch_array($grp)){
                ?>
                <option value="<?= $c['id'];?>"><?= $c['group_name'];?></option>
            <?php  }?>
        </select>
    </div>
    <div class="form-group">
        <label>Short Description</label>
        <textarea class="form-control" name="short_desc"><?= $m['short_desc'];?></textarea>
    </div>
    <div class="form-group">
        <label>Long Description</label>
        <textarea name="long_desc" id="default"><?= $m['long_desc'];?></textarea>
    </div>
    <div class="form-group">
        <label >Thumbnail</label>
        <input name="tupload" type="file" class="form-control">
        <input name="tupload_old" value="<?= $m['thumbnail'];?>" type="text" class="form-control" hidden>
    </div>
    <div class="form-group">
        <label>Video Type</label>
        <input type="radio" name="video_type" value="embed" <?php if($m['video_type']=='1'){echo 'checked';}?> />&nbsp; Embed
        <input type="radio" name="video_type" value="upload" <?php if($m['video_type']=='2'){echo 'checked';}?>/>&nbsp; Uploads
    </div>
    <div class="form-group">
        <label >Embed Video</label>
        <input name="embed" value="<?= $m['video'];?>" class="form-control" required <?php if($m['video_type']=='2'){echo 'disabled';}?>>
    </div>
    <div class="form-group">
        <label>Uploads</label>
        <input name="vupload" type="file" class="form-control" <?php if($m['video_type']=='1'){echo 'disabled';}?>>
        <input name="vupload_old" value="<?= $m['video'];?>" type="text" class="form-control" hidden>
    </div>
    <div class="form-group">
        <label>Embed Video link as Trailer </label>
        <input name="trailer" type="text" class="form-control" value="<?= $m['trailer'];?>">
    </div>
    <div class="form-group">
        <label>AD Img</label>
        <input name="ad_img" type="file" class="form-control" value="<?= $m['ad_img'];?>">
        <input name="ad_img_old" value="<?= $m['ad_img'];?>" type="text" class="form-control" hidden>
    </div>
    <div class="form-group">
        <label>AD Link</label>
        <input name="ad_link" type="text" class="form-control" value="<?= $m['ad_link'];?>">
    </div>

    <input type="submit" name="submit_edit_movie" class="btn btn-primary float-right" value="Edit movie">
</form>