<?php
include "header.php";
global $con;
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Region</h1>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <h4 class="text-center">Add New Region</h4>
            <form action="addregion.php" method="post">
                <?php
                echo display_error();
                ?>
                <div class="form-group">
                    <label class="my-1 mr-2" for="region_name">Name</label>
                    <input name="region_name" type="text" class="form-control form-control-user" id="region_name" placeholder="Region Name" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="dfee">Delivery Fee</label>
                    <input name="dfee" type="number" step="0.01" min="0" class="form-control form-control-user" id="dfee" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="region_parent">Parents</label>
                    <select name="parentsof" class="custom-select my-1 mr-sm-2" id="region_parent">
                        <option value="0" selected>None</option>
                        <?php
                        $query = "SELECT * FROM reg";
                        $result = mysqli_query($con, $query);
                        while($row = $result->fetch_assoc()){
                            ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['region_name']); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-primary btn-user btn-block" name="add_region" type="submit">
                    Add New Region
                </button>
            </form>
        </div>
        
        <div class="col-md-8 col-sm-12">
            <h4 class="text-center">Action</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Delivery Fee</th>
                        <th scope="col">Parents</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($_SESSION['type']=='yes'){
                        $querys = "SELECT * FROM reg";
                    }else{
                        $added_by=$_SESSION['user_id'];
                        $querys="SELECT * FROM reg WHERE added_by='$added_by'";
                    }   
                    $results = mysqli_query($con, $querys);
                    while($row2 = $results->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row2['id']; ?></td>
                            <td><?php echo htmlspecialchars($row2['region_name']); ?></td>
                            <td><?php echo number_format($row2['dfee'], 2); ?></td>
                            <td>
                            <?php 
                            if(is_null($row2['parentOf']) || $row2['parentOf'] == 0) {
                                echo "None";
                            } else {
                                $parent_query = "SELECT region_name FROM reg WHERE id = " . intval($row2['parentOf']);
                                $parent_result = mysqli_query($con, $parent_query);
                                if($parent_row = $parent_result->fetch_assoc()) {
                                    echo htmlspecialchars($parent_row['region_name']);
                                } else {
                                    echo "Unknown";
                                }
                            }
                            ?>
                            </td>
                            <td>
                                <div class="row">
                                <?php if($_SESSION['type']=='yes'){?>
                                    <form action="addregion.php" method="POST" class="col-4">
                                        <input name="region_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                        <button style="border: 0; background-color: #F8F9FC;" name="delete_region" type="submit" class="col-2">
                                            <i class="fas fa-trash-alt" style="color: red"></i>
                                        </button>
                                    </form>
                                <?php }?>
                                    <a href="editregion.php?id=<?php echo $row2['id']; ?>" class="update_region col-4">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
include "footer.php";
?>