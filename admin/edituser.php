<?php
include "header.php";
global $con;

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $qry = "SELECT * FROM users WHERE id = $id";
    $rslt = mysqli_query($con, $qry);
    
    if(mysqli_num_rows($rslt) == 0) {
        echo "<div class='alert alert-danger'>User not found!</div>";
        include "footer.php";
        exit;
    }
    
    $row2 = $rslt->fetch_assoc();
    
    // Prevent editing your own admin status
    $current_user_id = $_SESSION['user_id'];
    $is_editing_self = ($id == $current_user_id);
?>
<div class="container">
    <?php
    echo display_error();
    ?>
    <h4 class="text-center">Edit User</h4>
    <form action="edituser.php?id=<?php echo $id?>" method="post">
        <div class="form-group">
            <input type="hidden" id="edit_user_id" name="edit_user_id" value="<?php echo $id?>">
            <label class="my-1 mr-2" for="user_name">Username</label>
            <input name="user_name" type="text" class="form-control form-control-user" id="user_name" 
                   placeholder="User Name" value="<?php echo htmlspecialchars($row2['username']); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="my-1 mr-2" for="name">Name</label>
            <input name="name" type="text" class="form-control form-control-user" id="name" 
                   placeholder="Full Name" value="<?php echo htmlspecialchars($row2['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="my-1 mr-2" for="password">Password</label>
            <input name="password" type="password" class="form-control form-control-user" id="password" 
                   placeholder="Leave blank to keep current password">
            <small class="form-text text-muted">Leave blank to keep current password. Minimum 6 characters if changing.</small>
        </div>
        
        <div class="form-group">
            <label class="my-1 mr-2" for="user_type">User Type</label>
            <select name="user_type" class="custom-select my-1 mr-sm-2" id="user_type" 
                    <?php echo $is_editing_self ? 'disabled title="Cannot change your own user type"' : 'required'; ?>>
                <option value="">Select Type</option>
                <?php if($_SESSION['type']=='yes' && !$is_editing_self){ ?>
                    <option value="yes" <?php echo ($row2['admin?'] == 'yes') ? 'selected' : ''; ?>>Admin</option>
                <?php } ?>
                <option value="no" <?php echo ($row2['admin?'] == 'no') ? 'selected' : ''; ?>>User</option>
            </select>
            
            <?php if($is_editing_self){ ?>
                <input type="hidden" name="user_type" value="<?php echo $row2['admin?']; ?>">
                <small class="form-text text-muted text-warning">You cannot change your own user type.</small>
            <?php } ?>
        </div>
        
        <button class="btn btn-primary btn-user btn-block" name="update_user" type="submit">
            Update User
        </button>
        
        <a href="adduser.php" class="btn btn-secondary btn-user btn-block">
            Cancel
        </a>
    </form>
</div>
<?php
} else {
    echo "<div class='alert alert-danger'>No user ID provided!</div>";
}
include "footer.php";
?>