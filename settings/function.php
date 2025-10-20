<?php
$errors   = array();
function display_error() {
    global $errors;

    if (count($errors) > 0){
        echo '<h5>';
        foreach ($errors as $error){
            echo $error .'<br>';
        }
        echo '</h5>';
    }
}
if(isset($_POST['add_cat'])){
    addcat();
}
function addcat() {
    global $con, $errors;
    
    $cat_name = $_POST['cat_name'];
    $parentOf = intval($_POST['parentsof']);
    
    session_start();
    $added_by = intval($_SESSION['user_id']);
    
    // Validate inputs
    if (empty($cat_name)) {
        array_push($errors, "<span style='color: red'>Category name is required</span>");
        return;
    }

    // Handle "None" (0) -> NULL
    if ($parentOf === 0) {
        $stmt = $con->prepare("INSERT INTO cat (cat_name, parentOf, added_by) VALUES (?, NULL, ?)");
        $stmt->bind_param("si", $cat_name, $added_by);
    } else {
        $stmt = $con->prepare("INSERT INTO cat (cat_name, parentOf, added_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $cat_name, $parentOf, $added_by);
    }

    if ($stmt->execute()) {
        array_push($errors, "<span style='color: green'>Category created successfully</span>");
    } else {
        array_push($errors, "<span style='color: red'>Error: " . $stmt->error . "</span>");
    }

    $stmt->close();
}

if(isset($_POST['delete_cat'])){
    delete_cat();
}
function delete_cat(){
    global $con, $errors;
    
    // Validate and sanitize input
    if (!isset($_POST['cat_id']) || empty($_POST['cat_id'])) {
        array_push($errors, "<span style='color: red'>Invalid category ID</span>");
        return;
    }
    
    $cat_id = intval($_POST['cat_id']); // Convert to integer for safety
    
    // Check if category exists before deleting
    $check_query = "SELECT id FROM cat WHERE id = $cat_id";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Category not found</span>");
        return;
    }
    
    // Check if this category is used as parent by other categories
    $parent_check = "SELECT COUNT(*) as count FROM cat WHERE parentOf = $cat_id";
    $parent_result = mysqli_query($con, $parent_check);
    $parent_row = mysqli_fetch_assoc($parent_result);
    
    if ($parent_row['count'] > 0) {
        array_push($errors, "<span style='color: red'>Cannot delete category: It has child categories</span>");
        return;
    }
    
    // Perform the delete
    $query = "DELETE FROM cat WHERE id = $cat_id";
    $result = mysqli_query($con, $query);
    
    if ($result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>Category deleted successfully</span>");
        } else {
            array_push($errors, "<span style='color: red'>No category was deleted</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error deleting category: " . mysqli_error($con) . "</span>");
    }
}

// Alternative: Using Prepared Statements (More Secure)
function delete_cat_prepared(){
    global $con, $errors;
    
    if (!isset($_POST['cat_id']) || empty($_POST['cat_id'])) {
        array_push($errors, "<span style='color: red'>Invalid category ID</span>");
        return;
    }
    
    $cat_id = intval($_POST['cat_id']);
    
    // Check if category exists
    $check_stmt = $con->prepare("SELECT id FROM cat WHERE id = ?");
    $check_stmt->bind_param("i", $cat_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        array_push($errors, "<span style='color: red'>Category not found</span>");
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    // Check for child categories
    $parent_stmt = $con->prepare("SELECT COUNT(*) as count FROM cat WHERE parentOf = ?");
    $parent_stmt->bind_param("i", $cat_id);
    $parent_stmt->execute();
    $parent_result = $parent_stmt->get_result();
    $parent_row = $parent_result->fetch_assoc();
    
    if ($parent_row['count'] > 0) {
        array_push($errors, "<span style='color: red'>Cannot delete category: It has child categories</span>");
        $parent_stmt->close();
        return;
    }
    $parent_stmt->close();
    
    // Delete the category
    $delete_stmt = $con->prepare("DELETE FROM cat WHERE id = ?");
    $delete_stmt->bind_param("i", $cat_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            array_push($errors, "<span style='color: green'>Category deleted successfully</span>");
        } else {
            array_push($errors, "<span style='color: red'>No category was deleted</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error deleting category: " . $delete_stmt->error . "</span>");
    }
    
    $delete_stmt->close();
}

if(isset($_POST['update_cat_to_db'])){
    update_cat();
}
function update_cat(){
    global $con, $errors;
    
    // Validate inputs
    if (!isset($_POST['edit_cat_id']) || !isset($_POST['up_cat_name']) || !isset($_POST['edit_cat_parents'])) {
        array_push($errors, "<span style='color: red'>Missing required fields</span>");
        return;
    }
    
    $cat_id = intval($_POST['edit_cat_id']);
    $cat_name = trim($_POST['up_cat_name']);
    $edit_cat_parents = $_POST['edit_cat_parents'];
    
    // Validate category name
    if (empty($cat_name)) {
        array_push($errors, "<span style='color: red'>Category name cannot be empty</span>");
        return;
    }
    
    // Check if category exists using prepared statement
    $check_stmt = mysqli_prepare($con, "SELECT id FROM cat WHERE id = ?");
    mysqli_stmt_bind_param($check_stmt, "i", $cat_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Category not found</span>");
        mysqli_stmt_close($check_stmt);
        return;
    }
    mysqli_stmt_close($check_stmt);
    
    // Handle parentOf validation - convert empty string or 0 to NULL for top-level categories
    $parent_id = null;
    if (!empty($edit_cat_parents) && $edit_cat_parents !== '0') {
        $parent_id = intval($edit_cat_parents);
        
        // Prevent setting category as its own parent
        if ($parent_id == $cat_id) {
            array_push($errors, "<span style='color: red'>Category cannot be its own parent</span>");
            return;
        }
        
        // Validate that parent category exists
        $parent_check_stmt = mysqli_prepare($con, "SELECT id FROM cat WHERE id = ?");
        mysqli_stmt_bind_param($parent_check_stmt, "i", $parent_id);
        mysqli_stmt_execute($parent_check_stmt);
        $parent_result = mysqli_stmt_get_result($parent_check_stmt);
        
        if (mysqli_num_rows($parent_result) == 0) {
            array_push($errors, "<span style='color: red'>Parent category does not exist</span>");
            mysqli_stmt_close($parent_check_stmt);
            return;
        }
        mysqli_stmt_close($parent_check_stmt);
        
        // Check for circular reference in hierarchy
        if (checkCircularReference($con, $cat_id, $parent_id)) {
            array_push($errors, "<span style='color: red'>Circular reference detected. This would create an infinite loop in the category hierarchy.</span>");
            return;
        }
    }
    
    // Check for duplicate name (excluding current category) using prepared statement
    $duplicate_stmt = mysqli_prepare($con, "SELECT id FROM cat WHERE cat_name = ? AND id != ?");
    mysqli_stmt_bind_param($duplicate_stmt, "si", $cat_name, $cat_id);
    mysqli_stmt_execute($duplicate_stmt);
    $duplicate_result = mysqli_stmt_get_result($duplicate_stmt);
    
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Category name already exists</span>");
        mysqli_stmt_close($duplicate_stmt);
        return;
    }
    mysqli_stmt_close($duplicate_stmt);
    
    // Perform the update using prepared statement
    $update_stmt = mysqli_prepare($con, "UPDATE cat SET cat_name = ?, parentOf = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "sii", $cat_name, $parent_id, $cat_id);
    $update_result = mysqli_stmt_execute($update_stmt);
    
    if ($update_result) {
        if (mysqli_stmt_affected_rows($update_stmt) > 0) {
            $parent_msg = ($parent_id === null) ? "as top-level category" : "with parent ID: $parent_id";
            array_push($errors, "<span style='color: green'>Successfully updated category '$cat_name' $parent_msg</span>");
        } else {
            array_push($errors, "<span style='color: orange'>No changes were made</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error updating category: " . mysqli_stmt_error($update_stmt) . "</span>");
    }
    
    mysqli_stmt_close($update_stmt);
}

// Helper function to check for circular references
function checkCircularReference($con, $cat_id, $parent_id) {
    if ($parent_id == 0 || $parent_id === null) return false; // No parent, no circular reference
    
    $current_parent = $parent_id;
    $visited = array();
    $max_depth = 100; // Prevent infinite loops in case of database inconsistency
    $depth = 0;
    
    while ($current_parent !== null && $current_parent != 0 && !in_array($current_parent, $visited) && $depth < $max_depth) {
        if ($current_parent == $cat_id) {
            return true; // Circular reference found
        }
        
        $visited[] = $current_parent;
        $depth++;
        
        // Get the parent of current parent using prepared statement
        $stmt = mysqli_prepare($con, "SELECT parentOf FROM cat WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $current_parent);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_parent = $row['parentOf']; // This can be NULL for top-level categories
        } else {
            $current_parent = null; // Category not found or no parent
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // If we hit max_depth, there might be a circular reference or very deep hierarchy
    if ($depth >= $max_depth) {
        return true; // Treat as circular reference to be safe
    }
    
    return false; // No circular reference
}

// Alternative: Using Prepared Statements (More Secure)
function update_cat_prepared(){
    global $con, $errors;
    
    if (!isset($_POST['edit_cat_id']) || !isset($_POST['up_cat_name']) || !isset($_POST['edit_cat_parents'])) {
        array_push($errors, "<span style='color: red'>Missing required fields</span>");
        return;
    }
    
    $cat_id = intval($_POST['edit_cat_id']);
    $cat_name = trim($_POST['up_cat_name']);
    $edit_cat_parents = intval($_POST['edit_cat_parents']);
    
    if (empty($cat_name)) {
        array_push($errors, "<span style='color: red'>Category name cannot be empty</span>");
        return;
    }
    
    // Check if category exists
    $check_stmt = $con->prepare("SELECT id FROM cat WHERE id = ?");
    $check_stmt->bind_param("i", $cat_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        array_push($errors, "<span style='color: red'>Category not found</span>");
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    // Prevent self-referencing
    if ($edit_cat_parents == $cat_id) {
        array_push($errors, "<span style='color: red'>Category cannot be its own parent</span>");
        return;
    }
    
    // Check for duplicate name
    $duplicate_stmt = $con->prepare("SELECT id FROM cat WHERE cat_name = ? AND id != ?");
    $duplicate_stmt->bind_param("si", $cat_name, $cat_id);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        array_push($errors, "<span style='color: red'>Category name already exists</span>");
        $duplicate_stmt->close();
        return;
    }
    $duplicate_stmt->close();
    
    // Update the category
    $update_stmt = $con->prepare("UPDATE cat SET cat_name = ?, parentOf = ? WHERE id = ?");
    $update_stmt->bind_param("sii", $cat_name, $edit_cat_parents, $cat_id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            array_push($errors, "<span style='color: green'>Successfully updated to '$cat_name'</span>");
        } else {
            array_push($errors, "<span style='color: orange'>No changes were made</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error updating category: " . $update_stmt->error . "</span>");
    }
    
    $update_stmt->close();
}
if(isset($_POST['add_group'])){
    addgroup();
}
function addgroup(){
    global $con, $errors;
    
    // Validate and sanitize input data
    if (!isset($_POST['group_name']) || empty(trim($_POST['group_name']))) {
        array_push($errors, "<span style='color: red'>Group name is required</span>");
        return;
    }
    
    $group_name = mysqli_real_escape_string($con, trim($_POST['group_name']));
    $parentOf_input = $_POST['parentsof'];
    
    session_start();
    $added_by = intval($_SESSION['user_id']);
    
    // Check for duplicate group name
    $duplicate_check = "SELECT id FROM grp WHERE group_name = '$group_name'";
    $duplicate_result = mysqli_query($con, $duplicate_check);
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Group name already exists</span>");
        return;
    }
    
    // Handle parentOf - use NULL instead of 0 for no parent
    if ($parentOf_input == "0" || empty($parentOf_input) || $parentOf_input == "None") {
        $query = "INSERT INTO grp (group_name, parentOf, added_by) 
                  VALUES('$group_name', NULL, $added_by)";
    } else {
        $parentOf = intval($parentOf_input);
        
        // Verify that the parent group exists
        $parent_check = "SELECT id FROM grp WHERE id = $parentOf";
        $parent_result = mysqli_query($con, $parent_check);
        if (mysqli_num_rows($parent_result) == 0) {
            array_push($errors, "<span style='color: red'>Selected parent group does not exist</span>");
            return;
        }
        
        $query = "INSERT INTO grp (group_name, parentOf, added_by) 
                  VALUES('$group_name', $parentOf, $added_by)";
    }
    
    $result = mysqli_query($con, $query);
    
    if ($result) {
        array_push($errors, "<span style='color: green'>Group created successfully</span>");
    } else {
        array_push($errors, "<span style='color: red'>Error creating group: " . mysqli_error($con) . "</span>");
    }
}

if(isset($_POST['add_user'])){
    adduser();
}
function adduser(){

    global $con,$errors;
    $name = $_POST['name'];
    $user_name = $_POST['user_name'];
    $password = md5($_POST['password']);
    $user_type = $_POST['user_type'];
	session_start();
	$added_by = $_SESSION['user_id'];
    $query = "INSERT INTO users (username, password,name,`admin?`,added_by) 
                  VALUES('$user_name', '$password','$name','$user_type','$added_by')";
    $result = mysqli_query($con, $query);
	if ($result){
        array_push($errors, "<span style='color: green'>User created successfully</span>");
    }

}

if(isset($_POST['delete_group'])){
    delete_group();
}
function delete_group(){
    global $con, $errors;
    
    // Validate input
    if (!isset($_POST['group_id']) || empty($_POST['group_id'])) {
        array_push($errors, "<span style='color: red'>Invalid group ID</span>");
        return;
    }
    
    $group_id = intval($_POST['group_id']);
    
    // Check if group exists
    $check_query = "SELECT id FROM grp WHERE id = ?";
    $check_stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $group_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Group not found</span>");
        mysqli_stmt_close($check_stmt);
        return;
    }
    mysqli_stmt_close($check_stmt);
    
    // Check if this group is used as parent by other groups
    $parent_check = "SELECT COUNT(*) as count FROM grp WHERE parentOf = ?";
    $parent_stmt = mysqli_prepare($con, $parent_check);
    mysqli_stmt_bind_param($parent_stmt, "i", $group_id);
    mysqli_stmt_execute($parent_stmt);
    $parent_result = mysqli_stmt_get_result($parent_stmt);
    $parent_row = mysqli_fetch_assoc($parent_result);
    
    if ($parent_row['count'] > 0) {
        array_push($errors, "<span style='color: red'>Cannot delete group: It has child groups</span>");
        mysqli_stmt_close($parent_stmt);
        return;
    }
    mysqli_stmt_close($parent_stmt);
    
    // Start transaction
    mysqli_autocommit($con, false);
    
    try {
        // First, delete all movies associated with this group
        $delete_movies_query = "DELETE FROM movies WHERE group_id = ?";
        $delete_movies_stmt = mysqli_prepare($con, $delete_movies_query);
        mysqli_stmt_bind_param($delete_movies_stmt, "i", $group_id);
        
        if (!mysqli_stmt_execute($delete_movies_stmt)) {
            throw new Exception("Error deleting movies: " . mysqli_stmt_error($delete_movies_stmt));
        }
        
        $deleted_movies_count = mysqli_stmt_affected_rows($delete_movies_stmt);
        mysqli_stmt_close($delete_movies_stmt);
        
        // Then delete the group
        $delete_group_query = "DELETE FROM grp WHERE id = ?";
        $delete_group_stmt = mysqli_prepare($con, $delete_group_query);
        mysqli_stmt_bind_param($delete_group_stmt, "i", $group_id);
        
        if (!mysqli_stmt_execute($delete_group_stmt)) {
            throw new Exception("Error deleting group: " . mysqli_stmt_error($delete_group_stmt));
        }
        
        $deleted_groups_count = mysqli_stmt_affected_rows($delete_group_stmt);
        mysqli_stmt_close($delete_group_stmt);
        
        // Commit transaction
        mysqli_commit($con);
        
        if ($deleted_groups_count > 0) {
            $message = "<span style='color: green'>Group deleted successfully";
            if ($deleted_movies_count > 0) {
                $message .= " (also deleted $deleted_movies_count associated movies)";
            }
            $message .= "</span>";
            array_push($errors, $message);
        } else {
            array_push($errors, "<span style='color: red'>No group was deleted</span>");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        array_push($errors, "<span style='color: red'>" . $e->getMessage() . "</span>");
    }
    
    // Restore autocommit
    mysqli_autocommit($con, true);
}

if(isset($_POST['delete_user'])){
    delete_user();
}

function delete_user(){
    global $con, $errors;
    
    // Validate input
    if (!isset($_POST['del_user_id']) || empty($_POST['del_user_id'])) {
        array_push($errors, "<span style='color: red'>Invalid user ID</span>");
        return;
    }
    
    $user_id = intval($_POST['del_user_id']);
    
    // Prevent deleting current user
    session_start();
    if ($user_id == $_SESSION['user_id']) {
        array_push($errors, "<span style='color: red'>Cannot delete your own account</span>");
        return;
    }
    
    // Check if user exists
    $check_query = "SELECT id FROM users WHERE id = $user_id";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>User not found</span>");
        return;
    }
    
    // Perform the delete
    $query = "DELETE FROM users WHERE id = $user_id";
    $result = mysqli_query($con, $query);
    
    if ($result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>User deleted successfully</span>");
        } else {
            array_push($errors, "<span style='color: red'>No user was deleted</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error deleting user: " . mysqli_error($con) . "</span>");
    }
}

if(isset($_POST['update_user'])){
    update_user();
}

function update_user(){
    global $con, $errors;
    
    // Validate inputs
    if (!isset($_POST['edit_user_id']) || !isset($_POST['user_name']) || !isset($_POST['name']) || !isset($_POST['user_type'])) {
        array_push($errors, "<span style='color: red'>Missing required fields</span>");
        return;
    }
    
    $user_id = intval($_POST['edit_user_id']);
    $username = mysqli_real_escape_string($con, trim($_POST['user_name']));
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $user_type = $_POST['user_type'];
    
    // Validate inputs
    if (empty($username)) {
        array_push($errors, "<span style='color: red'>Username cannot be empty</span>");
        return;
    }
    
    if (empty($name)) {
        array_push($errors, "<span style='color: red'>Name cannot be empty</span>");
        return;
    }
    
    if (!in_array($user_type, ['yes', 'no'])) {
        array_push($errors, "<span style='color: red'>Invalid user type</span>");
        return;
    }
    
    // Check if user exists
    $check_query = "SELECT id FROM users WHERE id = $user_id";
    $check_result = mysqli_query($con, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>User not found</span>");
        return;
    }
    
    // Check for duplicate username (excluding current user)
    $duplicate_check = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
    $duplicate_result = mysqli_query($con, $duplicate_check);
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Username already exists</span>");
        return;
    }
    
    // Handle password update
    if (!empty($_POST['password']) && trim($_POST['password']) != '') {
        $password = trim($_POST['password']);
        
        // Validate password strength
        if (strlen($password) < 6) {
            array_push($errors, "<span style='color: red'>Password must be at least 6 characters long</span>");
            return;
        }
        
        $hashed_password = md5($password);
        $user_update_query = "UPDATE users SET name='$name', username='$username', password='$hashed_password', `admin?`='$user_type' WHERE id=$user_id";
    } else {
        $user_update_query = "UPDATE users SET name='$name', username='$username', `admin?`='$user_type' WHERE id=$user_id";
    }
    
    $user_update_result = mysqli_query($con, $user_update_query);
    
    if ($user_update_result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>Successfully updated user '$username'</span>");
        } else {
            array_push($errors, "<span style='color: orange'>No changes were made</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error updating user: " . mysqli_error($con) . "</span>");
    }
}

function update_group_to_db(){
    global $con, $errors;
    
    // Validate inputs
    if (!isset($_POST['edit_group_id']) || !isset($_POST['up_group_name']) || !isset($_POST['edit_group_parents'])) {
        array_push($errors, "<span style='color: red'>Missing required fields</span>");
        return;
    }
    
    $group_id = intval($_POST['edit_group_id']);
    $group_name = mysqli_real_escape_string($con, trim($_POST['up_group_name']));
    $edit_group_parents_input = $_POST['edit_group_parents'];
    
    // Validate group name
    if (empty($group_name)) {
        array_push($errors, "<span style='color: red'>Group name cannot be empty</span>");
        return;
    }
    
    // Check if group exists
    $check_query = "SELECT id FROM grp WHERE id = $group_id";
    $check_result = mysqli_query($con, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Group not found</span>");
        return;
    }
    
    // Check for duplicate name (excluding current group)
    $duplicate_check = "SELECT id FROM grp WHERE group_name = '$group_name' AND id != $group_id";
    $duplicate_result = mysqli_query($con, $duplicate_check);
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Group name already exists</span>");
        return;
    }
    
    // Handle parentOf
    if ($edit_group_parents_input == "0" || empty($edit_group_parents_input) || $edit_group_parents_input == "None") {
        $group_update_query = "UPDATE grp SET group_name='$group_name', parentOf=NULL WHERE id=$group_id";
    } else {
        $edit_group_parents = intval($edit_group_parents_input);
        
        // Prevent self-referencing
        if ($edit_group_parents == $group_id) {
            array_push($errors, "<span style='color: red'>Group cannot be its own parent</span>");
            return;
        }
        
        // Verify parent exists
        $parent_check = "SELECT id FROM grp WHERE id = $edit_group_parents";
        $parent_result = mysqli_query($con, $parent_check);
        if (mysqli_num_rows($parent_result) == 0) {
            array_push($errors, "<span style='color: red'>Selected parent group does not exist</span>");
            return;
        }
        
        // Check for circular reference
        if (checkCircularReferenceGroup($con, $group_id, $edit_group_parents)) {
            array_push($errors, "<span style='color: red'>Circular reference detected. This would create an infinite loop.</span>");
            return;
        }
        
        $group_update_query = "UPDATE grp SET group_name='$group_name', parentOf=$edit_group_parents WHERE id=$group_id";
    }
    
    $group_update_result = mysqli_query($con, $group_update_query);
    
    if ($group_update_result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>Successfully updated to '$group_name'</span>");
        } else {
            array_push($errors, "<span style='color: orange'>No changes were made</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error updating group: " . mysqli_error($con) . "</span>");
    }
}

// Helper function for circular reference checking (if not already defined)
function checkCircularReferenceGroup($con, $group_id, $parent_id) {
    if ($parent_id == 0 || is_null($parent_id)) return false;
    
    $current_parent = $parent_id;
    $visited = array();
    
    while (!is_null($current_parent) && $current_parent != 0 && !in_array($current_parent, $visited)) {
        if ($current_parent == $group_id) {
            return true; // Circular reference found
        }
        
        $visited[] = $current_parent;
        
        // Get the parent of current parent
        $query = "SELECT parentOf FROM grp WHERE id = $current_parent";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_parent = $row['parentOf'];
        } else {
            break;
        }
    }
    
    return false;
}

if(isset($_POST['add_region'])){
    addregion();
}

function addregion(){
    global $con, $errors;
    
    // Validate and sanitize input data
    if (!isset($_POST['region_name']) || empty(trim($_POST['region_name']))) {
        array_push($errors, "<span style='color: red'>Region name is required</span>");
        return;
    }
    
    if (!isset($_POST['dfee']) || !is_numeric($_POST['dfee'])) {
        array_push($errors, "<span style='color: red'>Valid delivery fee is required</span>");
        return;
    }
    
    $region_name = mysqli_real_escape_string($con, trim($_POST['region_name']));
    $dfee = floatval($_POST['dfee']);
    $parentOf_input = $_POST['parentsof'];
    
    session_start();
    $added_by = intval($_SESSION['user_id']);
    
    // Check for duplicate region name
    $duplicate_check = "SELECT id FROM reg WHERE region_name = '$region_name'";
    $duplicate_result = mysqli_query($con, $duplicate_check);
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Region name already exists</span>");
        return;
    }
    
    // Handle parentOf - use NULL instead of 0 for no parent
    if ($parentOf_input == "0" || empty($parentOf_input) || $parentOf_input == "None") {
        $query = "INSERT INTO reg (region_name, dfee, parentOf, added_by) 
                  VALUES('$region_name', $dfee, NULL, $added_by)";
    } else {
        $parentOf = intval($parentOf_input);
        
        // Verify that the parent region exists
        $parent_check = "SELECT id FROM reg WHERE id = $parentOf";
        $parent_result = mysqli_query($con, $parent_check);
        if (mysqli_num_rows($parent_result) == 0) {
            array_push($errors, "<span style='color: red'>Selected parent region does not exist</span>");
            return;
        }
        
        $query = "INSERT INTO reg (region_name, dfee, parentOf, added_by) 
                  VALUES('$region_name', $dfee, $parentOf, $added_by)";
    }
    
    $result = mysqli_query($con, $query);
    
    if ($result) {
        array_push($errors, "<span style='color: green'>Region created successfully</span>");
    } else {
        array_push($errors, "<span style='color: red'>Error creating region: " . mysqli_error($con) . "</span>");
    }
}

if(isset($_POST['delete_region'])){
    delete_region();
}

function delete_region(){
    global $con, $errors;
    
    // Validate input
    if (!isset($_POST['region_id']) || empty($_POST['region_id'])) {
        array_push($errors, "<span style='color: red'>Invalid region ID</span>");
        return;
    }
    
    $region_id = intval($_POST['region_id']);
    
    // Check if region exists
    $check_query = "SELECT id FROM reg WHERE id = $region_id";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Region not found</span>");
        return;
    }
    
    // Check if this region is used as parent by other regions
    $parent_check = "SELECT COUNT(*) as count FROM reg WHERE parentOf = $region_id";
    $parent_result = mysqli_query($con, $parent_check);
    $parent_row = mysqli_fetch_assoc($parent_result);
    
    if ($parent_row['count'] > 0) {
        array_push($errors, "<span style='color: red'>Cannot delete region: It has child regions</span>");
        return;
    }
    
    // Perform the delete
    $query = "DELETE FROM reg WHERE id = $region_id";
    $result = mysqli_query($con, $query);
    
    if ($result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>Region deleted successfully</span>");
        } else {
            array_push($errors, "<span style='color: red'>No region was deleted</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error deleting region: " . mysqli_error($con) . "</span>");
    }
}

if(isset($_POST['update_reg_to_db'])){
    update_reg_to_db();
}

function update_reg_to_db(){
    global $con, $errors;
    
    // Validate inputs
    if (!isset($_POST['edit_reg_id']) || !isset($_POST['up_reg_name']) || !isset($_POST['edit_reg_parents']) || !isset($_POST['up_reg_dfee'])) {
        array_push($errors, "<span style='color: red'>Missing required fields</span>");
        return;
    }
    
    $reg_id = intval($_POST['edit_reg_id']);
    $reg_name = mysqli_real_escape_string($con, trim($_POST['up_reg_name']));
    $reg_dfee = floatval($_POST['up_reg_dfee']);
    $edit_reg_parents_input = $_POST['edit_reg_parents'];
    
    // Validate inputs
    if (empty($reg_name)) {
        array_push($errors, "<span style='color: red'>Region name cannot be empty</span>");
        return;
    }
    
    if (!is_numeric($_POST['up_reg_dfee'])) {
        array_push($errors, "<span style='color: red'>Valid delivery fee is required</span>");
        return;
    }
    
    // Check if region exists
    $check_query = "SELECT id FROM reg WHERE id = $reg_id";
    $check_result = mysqli_query($con, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        array_push($errors, "<span style='color: red'>Region not found</span>");
        return;
    }
    
    // Check for duplicate name (excluding current region)
    $duplicate_check = "SELECT id FROM reg WHERE region_name = '$reg_name' AND id != $reg_id";
    $duplicate_result = mysqli_query($con, $duplicate_check);
    if (mysqli_num_rows($duplicate_result) > 0) {
        array_push($errors, "<span style='color: red'>Region name already exists</span>");
        return;
    }
    
    // Handle parentOf
    if ($edit_reg_parents_input == "0" || empty($edit_reg_parents_input) || $edit_reg_parents_input == "None") {
        $reg_update_query = "UPDATE reg SET region_name='$reg_name', dfee=$reg_dfee, parentOf=NULL WHERE id=$reg_id";
    } else {
        $edit_reg_parents = intval($edit_reg_parents_input);
        
        // Prevent self-referencing
        if ($edit_reg_parents == $reg_id) {
            array_push($errors, "<span style='color: red'>Region cannot be its own parent</span>");
            return;
        }
        
        // Verify parent exists
        $parent_check = "SELECT id FROM reg WHERE id = $edit_reg_parents";
        $parent_result = mysqli_query($con, $parent_check);
        if (mysqli_num_rows($parent_result) == 0) {
            array_push($errors, "<span style='color: red'>Selected parent region does not exist</span>");
            return;
        }
        
        // Check for circular reference
        if (checkCircularReferenceRegion($con, $reg_id, $edit_reg_parents)) {
            array_push($errors, "<span style='color: red'>Circular reference detected. This would create an infinite loop.</span>");
            return;
        }
        
        $reg_update_query = "UPDATE reg SET region_name='$reg_name', dfee=$reg_dfee, parentOf=$edit_reg_parents WHERE id=$reg_id";
    }
    
    $reg_update_result = mysqli_query($con, $reg_update_query);
    
    if ($reg_update_result) {
        if (mysqli_affected_rows($con) > 0) {
            array_push($errors, "<span style='color: green'>Successfully updated to '$reg_name'</span>");
        } else {
            array_push($errors, "<span style='color: orange'>No changes were made</span>");
        }
    } else {
        array_push($errors, "<span style='color: red'>Error updating region: " . mysqli_error($con) . "</span>");
    }
}

// Helper function to check for circular references in regions
function checkCircularReferenceRegion($con, $reg_id, $parent_id) {
    if ($parent_id == 0 || is_null($parent_id)) return false;
    
    $current_parent = $parent_id;
    $visited = array();
    
    while (!is_null($current_parent) && $current_parent != 0 && !in_array($current_parent, $visited)) {
        if ($current_parent == $reg_id) {
            return true; // Circular reference found
        }
        
        $visited[] = $current_parent;
        
        // Get the parent of current parent
        $query = "SELECT parentOf FROM reg WHERE id = $current_parent";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_parent = $row['parentOf'];
        } else {
            break;
        }
    }
    
    return false;
}
if(isset($_POST['search_WritersBlock'])){
    search_movie();
}

function search_movie(){

    global $con,$errors;
    $key = $_POST['keyword'];

    header("location:movie.php?keyword=$key");
}



