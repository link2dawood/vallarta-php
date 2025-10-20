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
function addcat(){

    global $con,$errors;
    $cat_name = $_POST['cat_name'];
    $parentOf = $_POST['parentsof'];
	session_start();
	$added_by = $_SESSION['user_id'];
    $query = "INSERT INTO cat (cat_name, parentOf,added_by) 
                  VALUES('$cat_name', '$parentOf','$added_by')";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Category created successfully</span>");
    }

}
if(isset($_POST['delete_cat'])){
    delete_cat();
}
function delete_cat(){

    global $con,$errors;
    $cat_id = $_POST['cat_id'];
    $query = "DELETE FROM cat WHERE  id='$cat_id'";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Deleted successfully</span>");

    }
}

if(isset($_POST['update_cat_to_db'])){
    update_cat();
}
function update_cat(){

    global $con,$errors;
    $cat_id = $_POST['edit_cat_id'];
    $cat_name = $_POST['up_cat_name'];
    $edit_cat_parents = $_POST['edit_cat_parents'];
    //header('location: ?id='.$cat_id.'&name='.$cat_name.'&pa='.$edit_cat_parents);
    $cat_update_query = "UPDATE cat SET cat_name='$cat_name',parentOf='$edit_cat_parents' WHERE id='$cat_id'";
    $cat_update_result = mysqli_query($con, $cat_update_query);
    if ($cat_update_result){

        array_push($errors, "<span style='color: green'>Successfully Update to $cat_name</span>");
    }
}
if(isset($_POST['add_group'])){
    addgroup();
}
function addgroup(){

    global $con,$errors;
    $group_name = $_POST['group_name'];
    $parentOf = $_POST['parentsof'];
	session_start();
	$added_by = $_SESSION['user_id'];
    $query = "INSERT INTO grp (group_name, parentOf,added_by) 
                  VALUES('$group_name', '$parentOf','$added_by')";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Group created successfully</span>");
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

    global $con,$errors;
    $cat_id = $_POST['group_id'];
    $query = "DELETE FROM grp WHERE  id='$cat_id'";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Deleted successfully</span>");

    }
}
if(isset($_POST['delete_user'])){
    delete_user();
}
function delete_user(){

    global $con,$errors;
    $user_id = $_POST['del_user_id'];
    $query = "DELETE FROM users WHERE  id='$user_id'";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Deleted successfully</span>");

    }
}

if(isset($_POST['update_user'])){
    update_user();
}

function update_user(){

    global $con,$errors;
    $user_id = $_POST['edit_user_id'];
    $username = $_POST['user_name'];
    $name = $_POST['name'];
    $user_type = $_POST['user_type'];
	if($_POST['password']!=''){
		$password = md5($_POST['password']);
		$cat_update_query = "UPDATE users SET name='$name',username='$username',password='$password',`admin?`='$user_type' WHERE id='$user_id'";	
	}else{
		$cat_update_query = "UPDATE users SET name='$name',username='$username',`admin?`='$user_type' WHERE id='$user_id'";	
	}
	$cat_update_result = mysqli_query($con, $cat_update_query);
    if ($cat_update_result){

        array_push($errors, "<span style='color: green'>Successfully Update to $username</span>");
    }
}
if(isset($_POST['update_grp_to_db'])){
    update_grp_to_db();
}

function update_grp_to_db(){

    global $con,$errors;
    $cat_id = $_POST['edit_grp_id'];
    $cat_name = $_POST['up_grp_name'];
    $edit_cat_parents = $_POST['edit_grp_parents'];
    //header('location: ?id='.$cat_id.'&name='.$cat_name.'&pa='.$edit_cat_parents);
    $cat_update_query = "UPDATE grp SET group_name='$cat_name',parentOf='$edit_cat_parents' WHERE id='$cat_id'";
    $cat_update_result = mysqli_query($con, $cat_update_query);
    if ($cat_update_result){

        array_push($errors, "<span style='color: green'>Successfully Update to $cat_name</span>");
    }
}
if(isset($_POST['add_region'])){
    addregion();
}
function addregion(){

    global $con,$errors;
    $region_name = $_POST['region_name'];
    $parentOf = $_POST['parentsof'];
	session_start();
	$added_by = $_SESSION['user_id'];
    $query = "INSERT INTO reg (region_name, parentOf,added_by) 
                  VALUES('$region_name', '$parentOf','$added_by')";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Region created successfully</span>");
    }

}

if(isset($_POST['delete_region'])){
    delete_region();
}
function delete_region(){

    global $con,$errors;
    $cat_id = $_POST['region_id'];
    $query = "DELETE FROM reg WHERE  id='$cat_id'";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Deleted successfully</span>");

    }
}

if(isset($_POST['update_reg_to_db'])){
    update_reg_to_db();
}

function update_reg_to_db(){

    global $con,$errors;
    $reg_id = $_POST['edit_reg_id'];
    $reg_name = $_POST['up_reg_name'];
    $edit_reg_parents = $_POST['edit_reg_parents'];
    //header('location: ?id='.$cat_id.'&name='.$cat_name.'&pa='.$edit_cat_parents);
    $cat_update_query = "UPDATE reg SET region_name='$reg_name',parentOf='$edit_reg_parents' WHERE id='$reg_id'";
    $cat_update_result = mysqli_query($con, $cat_update_query);
    if ($cat_update_result){

        array_push($errors, "<span style='color: green'>Successfully Update to $reg_name</span>");
    }
}
if(isset($_POST['search_movie'])){
    search_movie();
}

function search_movie(){

    global $con,$errors;
    $key = $_POST['keyword'];

    header("location: movie.php?keyword=$key");
}



