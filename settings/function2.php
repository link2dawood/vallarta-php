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
    $query = "INSERT INTO cat (cat_name, parentOf) 
                  VALUES('$cat_name', '$parentOf')";
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
    $query = "INSERT INTO grp (group_name, parentOf) 
                  VALUES('$group_name', '$parentOf')";
    $result = mysqli_query($con, $query);
    if ($result){
        array_push($errors, "<span style='color: green'>Group created successfully</span>");
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
if(isset($_POST['search_movie'])){
    search_movie();
}

function search_movie(){

    global $con,$errors;
    $key = $_POST['keyword'];

    header("location: movie.php?keyword=$key");
}



