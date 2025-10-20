<?php
include '../settings/db.php';
include 'header.php';

$users_id = $_SESSION['user_id'];


if (isset($_GET['id'])){
    $id = $_GET['id'];

}
$query = $con->query("SELECT * FROM `movies` JOIN grp ON movies.group_id = grp.id JOIN cat on movies.cat_id =cat.id WHERE movies.movie_id = '$id'");
$m = mysqli_fetch_array($query);


?>
<div class="container-fluid">
<form name="form1" enctype="multipart/form-data" method="POST" action="action.php?act=editmovie">

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
      <label>Region</label>
      <select class="form-control" name="region_id">
        <?php
            $reg = $con->query("SELECT * FROM reg");
            while ($c = mysqli_fetch_array($reg)){
                ?>
				<option value="<?php echo $c['id']; ?>"<?php if($m['region_id']==$c['id']) echo 'selected="selected"'; ?>><?php echo $c['region_name']; ?></option>
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

    <input type="submit" name="submit_edit_movie" class="btn btn-primary float-right" value="Edit WritersBlock">
</form>
</div>

<!-- /.container-fluid -->
<script>
    tinymce.init({
        selector: '#default',
    });
</script>

<!--- custom -->
<script language="javascript">
    var form = document.forms['form1'];
    form.video_type[0].onfocus = function() {
        form.embed.disabled = false;
        form.vupload.disabled = true;
    }
    form.video_type[1].onfocus = function() {
        form.embed.disabled = true;
        form.vupload.disabled = false;
    }
</script>
</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Your Website 2019</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->
<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Bootstrap core JavaScript-->
<script src="../assets/back/vendor/jquery/jquery.min.js"></script>
<script src="../assets/back/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="../assets/back/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../assets/back/js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="../assets/back/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/back/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="../assets/back/js/demo/datatables-demo.js"></script>

<!-- Tiny -->
<script src="../assets/back/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#default',
    });
</script>

<!--- custom -->
<script language="javascript">
    var form = document.forms['form1'];
    form.video_type[0].onfocus = function() {
        form.embed.disabled = false;
        form.vupload.disabled = true;
    }
    form.video_type[1].onfocus = function() {
        form.embed.disabled = true;
        form.vupload.disabled = false;
    }
</script>

</body>

</html>