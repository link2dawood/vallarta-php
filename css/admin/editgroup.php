<?php
include "header.php";
global $con;
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $qry =  "SELECT * FROM grp where id='$id'";
    $rslt = mysqli_query($con, $qry);
    $row2 = $rslt->fetch_assoc();

?>
<div class="container">
    <?php
    echo display_error();
    ?>
    <form action="editgroup.php?id=<?php echo $id?>" method="post">
        <div class="form-group">
            <input type="hidden" id="edit_grp_id" name="edit_grp_id" value="<?php echo $id?>">
            <label class="my-1 mr-2" for="up_grp_name">Name</label>
            <input name="up_grp_name" type="TEXT" class="form-control form-control-user" id="up_grp_name" placeholder="Group Name" VALUE="<?PHP echo $row2['group_name'];  ?>">
        </div>
        <div class="form-group">
            <label class="my-1 mr-2" for="grp_parent">Prents</label>
            <select name="edit_grp_parents" class="custom-select my-1 mr-sm-2" id="grp_parent">
                <option selected>None</option>
                <?php
                $query = "SELECT * FROM grp";
                $result = mysqli_query($con, $query);
                while($row = $result->fetch_assoc()){
                    ?>
                    <option><?php echo $row['group_name']; ?></option>
                    <?php
                }
                ?>
            </select>
            <button class="btn btn-primary btn-user btn-block" name="update_grp_to_db">
                Edit Group
            </button>

        </div>
    </form>
</div>

<?php
}
include "footer.php";
?>