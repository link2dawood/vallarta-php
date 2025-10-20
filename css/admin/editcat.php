<?php
include "header.php";
global $con;
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $qry =  "SELECT * FROM cat where id='$id'";
    $rslt = mysqli_query($con, $qry);
    $row2 = $rslt->fetch_assoc();

?>
<div class="container">
    <?php
    echo display_error();
    ?>
    <form action="editcat.php?id=<?php echo $id?>" method="post">
        <div class="form-group">
            <input type="hidden" id="edit_cat_id" name="edit_cat_id" value="<?php echo $id?>">
            <label class="my-1 mr-2" for="up_cat_name">Name</label>
            <input name="up_cat_name" type="TEXT" class="form-control form-control-user" id="up_cat_name" placeholder="Category Name" VALUE="<?PHP echo $row2['cat_name'];  ?>">
        </div>
        <div class="form-group">
            <label class="my-1 mr-2" for="cat_parent">Prents</label>
            <select name="edit_cat_parents" class="custom-select my-1 mr-sm-2" id="cat_parent">
                <option selected>None</option>
                <?php
                $query = "SELECT * FROM cat";
                $result = mysqli_query($con, $query);
                while($row = $result->fetch_assoc()){
                    ?>
                    <option><?php echo $row['cat_name']; ?></option>
                    <?php
                }
                ?>
            </select>
            <button class="btn btn-primary btn-user btn-block" name="update_cat_to_db">
                Add New Category
            </button>

        </div>
    </form>
</div>

<?php
}
include "footer.php";
?>