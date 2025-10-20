<?php
include "header.php";
global $con;
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">User</h1>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <h4 class="text-center">Add New User</h4>
            <form action="adduser.php" method="post">
                <?php
                echo display_error();
                ?>
                <div class="form-group">
                    <label class="my-1 mr-2" for="user_name">Username</label>
                    <input name="user_name" type="text" class="form-control form-control-user" id="user_name" placeholder="User Name" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="name">Name</label>
                    <input name="name" type="text" class="form-control form-control-user" id="name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="password">Password</label>
                    <input name="password" type="password" class="form-control form-control-user" id="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="user_type">Select User Type</label>
                    <select name="user_type" class="custom-select my-1 mr-sm-2" id="user_type" required>
                        <option value="">Select Type</option>
                        <?php if($_SESSION['type']=='yes'){ ?>  
                            <option value="yes">Admin</option>
                        <?php } ?>  
                        <option value="no">User</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-user btn-block" name="add_user" type="submit">
                    Add New User
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
                    <th scope="col">Username</th>
                    <th scope="col">Name</th>
                    <th scope="col">Type</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($_SESSION['type']=='yes'){
                    $querys = "SELECT * FROM users";
                }else{
                    $added_by=$_SESSION['user_id'];
                    $querys="SELECT * FROM users WHERE added_by='$added_by'";
                }   
                $results = mysqli_query($con, $querys);
                while($row2 = $results->fetch_assoc()){
                ?>
                <tr>
                    <td><?php echo $row2['id']; ?></td>
                    <td><?php echo htmlspecialchars($row2['username']); ?></td>
                    <td><?php echo htmlspecialchars($row2['name']); ?></td>
                    <td>
                        <?php 
                        echo ($row2['admin?'] == 'yes') ? 
                            '<span class="badge badge-success">Admin</span>' : 
                            '<span class="badge badge-info">User</span>'; 
                        ?>
                    </td>
                    <td>
                        <div class="row">
                            <?php if($_SESSION['type']=='yes'){ ?>
                                <form action="adduser.php" method="POST" class="col-4">
                                    <input name="del_user_id" type="hidden" value="<?php echo $row2['id']; ?>">
                                    <button style="border: 0; background-color: #F8F9FC;" name="delete_user" type="submit" class="col-2">
                                        <i class="fas fa-trash-alt" style="color: red"></i>
                                    </button>
                                </form>
                            <?php } ?>
                            <a href="edituser.php?id=<?php echo $row2['id']; ?>" class="update_user col-4">
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