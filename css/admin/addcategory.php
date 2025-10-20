<?php
include "header.php";
global $con;
?>


    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Category </h1>
        <div class="row">
            <div class="col-md-4 col-sm-12">
                <h4 class="text-center">Add New Category</h4>
                <form action="addcategory.php" method="post">
                    <?php
                    echo display_error();
                    ?>
                    <div class="form-group">
                        <label class="my-1 mr-2" for="cat_name">Name</label>
                        <input name="cat_name" type="TEXT" class="form-control form-control-user" id="cat_name" placeholder="Category Name">
                    </div>
                    <div class="form-group">
                        <label class="my-1 mr-2" for="cat_parent">Prents</label>
                        <select name="parentsof" class="custom-select my-1 mr-sm-2" id="cat_parent">
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

                    </div>
                    <button class="btn btn-primary btn-user btn-block" name="add_cat">
                        Add New Category
                    </button>
                </form>

            </div>
            <div class="col-md-8 col-sm-12">
                <h4 class="text-center">Action</h4>
                <br>
                    <table class="table">
                        <thead class="">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Parents</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $querys = "SELECT * FROM cat";
                        $results = mysqli_query($con, $querys);
                            while($row2 = $results->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row2['id']; ?></td>
                            <td><?php echo $row2['cat_name']; ?></td>
                            <td><?php echo $row2['parentOf']; ?></td>
                            <td>
                                <div class="row">
                                    <form action="addcategory.php" method="POST" class="col-4">
                                        <input name="cat_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                        <button style="border: 0; background-color: #F8F9FC;" name="delete_cat"  class="col-2"><i class="fas fa-trash-alt" style="color: red"></i></button>
                                    </form>
                                        <a href="editcat.php?id=<?php echo $row2['id']; ?>" id="" class="update_cat col-4"><i class="fas fa-edit"></i></a>
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