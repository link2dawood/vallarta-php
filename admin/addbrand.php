<?php
include "header.php";
global $con;
?>
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Brand Management</h1>
        <div class="row">
            <div class="col-md-4 col-sm-12">
                <h4 class="text-center">Add New Brand</h4>
                <form action="addbrand.php" method="post">
 
                    <div class="form-group">
                        <label class="my-1 mr-2" for="brand_name">Brand Name</label>
                        <input name="brand_name" type="TEXT" class="form-control form-control-user" id="brand_name" placeholder="Brand Name" required>
                    </div>
                    <div class="form-group">
                        <label class="my-1 mr-2" for="brand_parent">Parent Brand</label>
                        <select name="parentsof" class="custom-select my-1 mr-sm-2" id="brand_parent">
                        <option value="0" selected>None</option>
                        <?php
                        $query = "SELECT * FROM brand ORDER BY brand_name";
                        $result = mysqli_query($con, $query);
                        while($row = $result->fetch_assoc()){
                        ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['brand_name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>

                    </div>
                    <button class="btn btn-primary btn-user btn-block" name="add_brand">
                        Add New Brand
                    </button>
                </form>

            </div>
            <div class="col-md-8 col-sm-12">
                <h4 class="text-center">Existing Brands</h4>
                <br>
                <?php display_error(); ?>
                <br>
                    <table class="table">
                        <thead class="">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Brand Name</th>
                            <th scope="col">Parent Brand</th>
                            <th scope="col">Date Created</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $querys = "SELECT b.*, p.brand_name as parent_name 
                                   FROM brand b 
                                   LEFT JOIN brand p ON b.parentOf = p.id 
                                   ORDER BY b.brand_name";
                        $results = mysqli_query($con, $querys);
                        while($row2 = $results->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row2['id']; ?></td>
                            <td><?php echo $row2['brand_name']; ?></td>
                            <td><?php echo $row2['parent_name'] ?? 'None'; ?></td>
                            <td><?php echo date('M j, Y', strtotime($row2['date_created'])); ?></td>
                            <td>
                                <div class="row">
                                    <form action="addbrand.php" method="POST" class="col-4">
                                        <input name="brand_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                        <button style="border: 0; background-color: #F8F9FC;" name="delete_brand" class="col-2" onclick="return confirm('Are you sure you want to delete this brand?')"><i class="fas fa-trash-alt" style="color: red"></i></button>
                                    </form>
                                    <a href="editbrand.php?id=<?php echo $row2['id']; ?>" id="" class="update_brand col-4"><i class="fas fa-edit"></i></a>
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
    <!-- /.container-fluid -->

<?php
include "footer.php";
?>
