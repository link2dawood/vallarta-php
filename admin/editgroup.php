<?php
include "header.php";
global $con;

// Handle update request
if (isset($_POST['update_group_to_db'])) {
    $id = intval($_POST['edit_group_id']);
    $name = mysqli_real_escape_string($con, $_POST['up_group_name']);
   $parent = $_POST['edit_group_parents'];
if ($parent == 0) {
    $parent = "NULL";  // assign NULL instead of 0
} else {
    $parent = intval($parent);
}

    $qry = "UPDATE grp SET group_name = '$name', parentOf = $parent WHERE id = $id";
    if (mysqli_query($con, $qry)) {
      
	        echo "<div class='alert alert-success'>Group updated successfully!</div>";

    } else {
        echo "<div class='alert alert-danger'>Error updating group: " . mysqli_error($con) . "</div>";
    }
}

// Now load the form
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $qry = "SELECT * FROM grp WHERE id = $id";
    $rslt = mysqli_query($con, $qry);
    
    if (mysqli_num_rows($rslt) == 0) {
        echo "<div class='alert alert-danger'>Group not found!</div>";
        include "footer.php";
        exit;
    }
    
    $row2 = $rslt->fetch_assoc();
?>
<div class="container">
    <h4 class="text-center">Edit Group</h4>
    <form action="editgroup.php?id=<?php echo $id?>" method="post">
        <input type="hidden" id="edit_group_id" name="edit_group_id" value="<?php echo $id?>">
        
        <div class="form-group">
            <label for="up_group_name">Name</label>
            <input name="up_group_name" type="text" class="form-control" 
                   id="up_group_name" value="<?php echo htmlspecialchars($row2['group_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="group_parent">Parents</label>
            <select name="edit_group_parents" class="form-control" id="group_parent">
                <option value="0" <?php echo (is_null($row2['parentOf']) || $row2['parentOf'] == 0) ? 'selected' : ''; ?>>None</option>
                <?php
                $query = "SELECT * FROM grp WHERE id != $id";
                $result = mysqli_query($con, $query);
                while($row = $result->fetch_assoc()){
                    $selected = ($row2['parentOf'] == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['group_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <button class="btn btn-primary btn-block" name="update_group_to_db" type="submit">Update Group</button>
        <a href="addgroup.php" class="btn btn-secondary btn-block">Cancel</a>
    </form>
</div>
<?php
} else {
    echo "<div class='alert alert-danger'>No group ID provided!</div>";
}
include "footer.php";
?>
