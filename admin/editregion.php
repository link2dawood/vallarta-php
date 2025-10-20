<?php
include "header.php";
global $con;

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $qry = "SELECT * FROM reg WHERE id = $id";
    $rslt = mysqli_query($con, $qry);
    
    if(mysqli_num_rows($rslt) == 0) {
        echo "<div class='alert alert-danger'>Region not found!</div>";
        include "footer.php";
        exit;
    }
    
    $row2 = $rslt->fetch_assoc();
?>
<div class="container">
    <?php
    echo display_error();
    ?>
    <h4 class="text-center">Edit Region</h4>
    <form action="editregion.php?id=<?php echo $id?>" method="post">
        <div class="form-group">
            <input type="hidden" id="edit_reg_id" name="edit_reg_id" value="<?php echo $id?>">
            <label class="my-1 mr-2" for="up_reg_name">Name</label>
            <input name="up_reg_name" type="text" class="form-control form-control-user" id="up_reg_name" 
                   placeholder="Region Name" value="<?php echo htmlspecialchars($row2['region_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="my-1 mr-2" for="up_reg_dfee">Delivery Fee</label>
            <input name="up_reg_dfee" type="number" step="0.01" min="0" class="form-control form-control-user" id="up_reg_dfee" 
                   placeholder="0.00" value="<?php echo $row2['dfee']; ?>" required>
        </div>
        
        <div class="form-group">
            <label class="my-1 mr-2" for="region_parent">Parents</label>
            <select name="edit_reg_parents" class="custom-select my-1 mr-sm-2" id="region_parent">
                <option value="0" <?php echo (is_null($row2['parentOf']) || $row2['parentOf'] == 0) ? 'selected' : ''; ?>>None</option>
                <?php
                // Get all regions except the current one
                $query = "SELECT * FROM reg WHERE id != $id";
                $result = mysqli_query($con, $query);
                while($row = $result->fetch_assoc()){
                    $selected = ($row2['parentOf'] == $row['id']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($row['region_name']); ?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </div>
        
        <button class="btn btn-primary btn-user btn-block" name="update_reg_to_db" type="submit">
            Update Region
        </button>
        
        <a href="addregion.php" class="btn btn-secondary btn-user btn-block">
            Cancel
        </a>
    </form>
</div>
<?php
} else {
    echo "<div class='alert alert-danger'>No region ID provided!</div>";
}
include "footer.php";
?>