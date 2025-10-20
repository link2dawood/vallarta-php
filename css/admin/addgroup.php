<?php
include "header.php";
global $con;
?>


    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Group </h1>
        <div class="row">
            <div class="col-md-4 col-sm-12">
                <h4 class="text-center">Add New Group</h4>
                <form action="addgroup.php" method="post">
                    <?php
                    echo display_error();
                    ?>
                    <div class="form-group">
                        <label class="my-1 mr-2" for="cat_name">Name</label>
                        <input name="group_name" type="TEXT" class="form-control form-control-user" id="group_name" placeholder="Group Name">
                    </div>
                    <div class="form-group">
                        <label class="my-1 mr-2" for="cat_parent">Prents</label>
                        <select name="parentsof" class="custom-select my-1 mr-sm-2" id="group_parent">
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

                    </div>
                    <button class="btn btn-primary btn-user btn-block" name="add_group">
                        Add New Group
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
                        $querys = "SELECT * FROM grp";
                        $results = mysqli_query($con, $querys);
                            while($row2 = $results->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row2['id']; ?></td>
                            <td><?php echo $row2['group_name']; ?></td>
                            <td><?php echo $row2['parentOf']; ?></td>
                            <td>
                                <div class="row">
                                    <form action="addgroup.php" method="POST" class="col-4">
                                        <input name="group_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                        <button style="border: 0; background-color: #F8F9FC;" name="delete_group"  class="col-2"><i class="fas fa-trash-alt" style="color: red"></i></button>
                                    </form>
                                        <a href="editgroup.php?id=<?php echo $row2['id']; ?>" id="" class="update_cat col-4"><i class="fas fa-edit"></i></a>
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