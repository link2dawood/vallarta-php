<?php
	$users_id = $_SESSION['user_id'];
?>

<form name="form1" enctype="multipart/form-data" method="POST" action="action.php?act=add">
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" class="form-control" required>
        <input type="hidden" name="user_id" value="<?php echo $users_id?>" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Category</label>
        <select class="form-control" name="cat_id" required>
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
        <select class="form-control" name="group_id" required>
            <?php
            $cate = $con->query("SELECT * FROM grp");
            while ($c = mysqli_fetch_array($cate)){
                ?>
                <option value="<?= $c['id'];?>"><?= $c['group_name'];?></option>
            <?php  }?>
        </select>
    </div>
    <div class="form-group">
      <label>Region</label>
      <select class="form-control" name="region_id" required="required">
        <?php
            $cate = $con->query("SELECT * FROM reg");
            while ($c = mysqli_fetch_array($cate)){
                ?>
        <option value="<?= $c['id'];?>">
          <?= $c['region_name'];?>
        </option>
        <?php  }?>
      </select>
    </div>
    <div class="form-group">
        <label>Short Description</label>
        <textarea class="form-control" name="short_desc"></textarea>
    </div>
    <div class="form-group">
        <label>Long Description</label>
        <textarea name="long_desc" id="default"></textarea>
    </div>
    <div class="form-group">
        <label>Thumbnail</label>
        <input name="tupload" type="file" class="form-control">
    </div>
    <div class="form-group">
        <label>Video Type</label>
        <input type="radio" name="video_type" value="embed" checked="checked" />&nbsp; Embed
        <input type="radio" name="video_type" value="upload" />&nbsp; Uploads
    </div>
    <div class="form-group">
        <label>Embed Video</label>
        <input name="embed" class="form-control" required=>
    </div>
    <div class="form-group">
        <label>Embed Video link as Trailer </label>
        <input name="trailer" class="form-control" required=>
    </div>
    <div class="form-group">
        <label>AD Img</label>
        <input name="ad_img" type="file" class="form-control" >
    </div>
    <div class="form-group">
        <label>AD Link</label>
        <input name="ad_link" type="text" class="form-control">
    </div>
    <div class="form-group">
        <label>Uploads</label>
        <input name="vupload" type="file" class="form-control" disabled>
    </div>
    <div class="form-group">
        <label>Price</label>
        <input name="price" type="text" class="form-control" disabled>
    </div>
    <button type="submit" class="btn btn-primary float-right">Add WritersBlock</button>
</form>