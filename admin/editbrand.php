<?php
include "header.php";
global $con;

$brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($brand_id == 0) {
    header('location:addbrand.php');
    exit;
}

// Get brand data
$brand_query = mysqli_query($con, "SELECT * FROM brand WHERE id = $brand_id");
if (!$brand_query || mysqli_num_rows($brand_query) == 0) {
    header('location:addbrand.php');
    exit;
}
$brand_data = mysqli_fetch_array($brand_query);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Edit Brand</h1>
    
    <?php display_error(); ?>
    
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Brand Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="editbrand.php?id=<?php echo $brand_id; ?>">
                        <input type="hidden" name="brand_id" value="<?php echo $brand_id; ?>">
                        
                        <div class="form-group">
                            <label for="brand_name">Brand Name</label>
                            <input type="text" name="brand_name" id="brand_name" class="form-control form-control-user" 
                                   value="<?php echo htmlspecialchars($brand_data['brand_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="parentsof">Parent Brand (Optional)</label>
                            <select name="parentsof" id="parentsof" class="custom-select">
                                <option value="0" <?php echo ($brand_data['parentOf'] == NULL) ? 'selected' : ''; ?>>None (Top Level)</option>
                                <?php
                                $brands = mysqli_query($con, "SELECT * FROM brand WHERE id != $brand_id ORDER BY brand_name");
                                while ($brand = mysqli_fetch_array($brands)) {
                                    $selected = ($brand['id'] == $brand_data['parentOf']) ? 'selected' : '';
                                    echo "<option value='{$brand['id']}' $selected>{$brand['brand_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_brand" class="btn btn-primary btn-user btn-block">
                            Update Brand
                        </button>
                        
                        <a href="addbrand.php" class="btn btn-secondary btn-user btn-block">
                            Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php
include "footer.php";
?>
