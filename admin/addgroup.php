<?php
include "header.php";
global $con;
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Group</h1>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <h4 class="text-center">Add New Group</h4>
            <form action="addgroup.php" method="post">

                <div class="form-group">
                    <label class="my-1 mr-2" for="group_name">Name</label>
                    <input name="group_name" type="text" class="form-control form-control-user" id="group_name" placeholder="Group Name" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="group_parent">Parents</label>
                    <select name="parentsof" class="custom-select my-1 mr-sm-2" id="group_parent">
                        <option value="0" selected>None</option>
                        <?php
                        $query = "SELECT * FROM grp";
                        $result = mysqli_query($con, $query);
                        while($row = $result->fetch_assoc()){
                        ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['group_name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-primary btn-user btn-block" name="add_group" type="submit">
                    Add New Group
                </button>
            </form>
        </div>
        
        <div class="col-md-8 col-sm-12">
            <h4 class="text-center">Action</h4>
            <br>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Parents</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($_SESSION['type']=='yes'){
                    $querys = "SELECT * FROM grp";
                }else{
                    $added_by=$_SESSION['user_id'];
                    $querys="SELECT * FROM grp WHERE added_by='$added_by'";
                }
                $results = mysqli_query($con, $querys);
                while($row2 = $results->fetch_assoc()){
                ?>
                <tr>
                    <td><?php echo $row2['id']; ?></td>
                    <td><?php echo $row2['group_name']; ?></td>
                    <td>
                    <?php 
                    if($row2['parentOf'] == 0) {
                        echo "None";
                    } else {
                        $parent_query = "SELECT group_name FROM grp WHERE id = " . intval($row2['parentOf']);
                        $parent_result = mysqli_query($con, $parent_query);
                        if($parent_row = $parent_result->fetch_assoc()) {
                            echo $parent_row['group_name'];
                        } else {
                            echo "Unknown";
                        }
                    }
                    ?>
                    </td>
                    <td>
                        <div class="row">
                        <?php if($_SESSION['type']=='yes'){?>
                            <form action="addgroup.php" method="POST" class="col-4">
                                <input name="group_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                <button style="border: 0; background-color: #F8F9FC;" name="delete_group" type="submit" class="col-2">
                                    <i class="fas fa-trash-alt" style="color: red"></i>
                                </button>
                            </form>
                        <?php }?>   
                            <a href="editgroup.php?id=<?php echo $row2['id']; ?>" class="update_group col-4">
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
<!-- /.container-fluid -->

<?php
include "footer.php";
?>