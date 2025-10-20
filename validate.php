<?php
 require_once('settings/db.php');
 if(isset($_POST['update']))
 {
    $date = date("Y-m-d H:i:s");
    $id = $_GET['id'];
    $Status = $_POST['status'];
    switch ($Status) {
        case 'NEW!!! (Validate)':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', valide_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'Recieved/Processing':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', re_pro_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'Confirming':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', confirm_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;
        case 'Ready for Delivery':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', rd_f_delv_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'In Delivery':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', in_delv_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'Delivered':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', delivred_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'Cancled':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', canceled_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;

        case 'Delayed':
            mysqli_query($con, "UPDATE `ordere` SET valid = '$Status', delayed_date = '$date' WHERE id = '$id'") or die('query failed');
            header("location:admin.php"); 
        break;
        default:
            // code...
            break;
    }
    
 }
?>