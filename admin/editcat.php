<?php
include "header.php";
global $con;

// Handle update request
if (isset($_POST['update_cat_to_db'])) {
    $id = intval($_POST['edit_cat_id']);
    $name = mysqli_real_escape_string($con, $_POST['up_cat_name']);
    $parent = $_POST['edit_cat_parents']; // keep raw

    // If user chose "None", set parent = NULL
    if ($parent == 0) {
        $qry = "UPDATE cat SET cat_name = '$name', parentOf = NULL WHERE id = $id";
    } else {
        $parent = intval($parent);
        $qry = "UPDATE cat SET cat_name = '$name', parentOf = $parent WHERE id = $id";
    }

    if (mysqli_query($con, $qry)) {
        echo "<div class='alert alert-success'>Category updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating category: " . mysqli_error($con) . "</div>";
    }
}


// Show form if id exists
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $qry = "SELECT * FROM cat WHERE id = $id";
    $rslt = mysqli_query($con, $qry);

    if (mysqli_num_rows($rslt) == 0) {
        echo "<div class='alert alert-danger'>Category not found!</div>";
        include "footer.php";
        exit;
    }

    $row2 = $rslt->fetch_assoc();
?>
<div class="container">
 
    <h4 class="text-center">Edit Category</h4>
    <form action="editcat.php?id=<?php echo $id?>" method="post">
        <div class="form-group">
            <input type="hidden" id="edit_cat_id" name="edit_cat_id" value="<?php echo $id?>">
            <label class="my-1 mr-2" for="up_cat_name">Name</label>
            <input name="up_cat_name" type="text" class="form-control form-control-user"
                   id="up_cat_name" placeholder="Category Name"
                   value="<?php echo htmlspecialchars($row2['cat_name']); ?>" required>
        </div>

        <div class="form-group">
            <label class="my-1 mr-2" for="cat_parent">Parents</label>
            <select name="edit_cat_parents" class="custom-select my-1 mr-sm-2" id="cat_parent">
                <option value="0" <?php echo (is_null($row2['parentOf']) || $row2['parentOf'] == 0) ? 'selected' : ''; ?>>None</option>
                <?php
                // Prevent circular reference (exclude current category)
                $query = "SELECT * FROM cat WHERE id != $id";
                $result = mysqli_query($con, $query);
                while($row = $result->fetch_assoc()){
                    $selected = ($row2['parentOf'] == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['cat_name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <button class="btn btn-primary btn-user btn-block" name="update_cat_to_db" type="submit">
            Update Category
        </button>
        <a href="addcategory.php" class="btn btn-secondary btn-user btn-block">Cancel</a>
    </form>
</div>
<?php
} else {
    echo "<div class='alert alert-danger'>No category ID provided!</div>";
}
include "footer.php";
?>
