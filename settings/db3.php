<?php
$con = new mysqli("db5001581894.hosting-data.io","dbu346479","Nextstep12!$","dbs1318107");

// Check connection
if ($con -> connect_errno) {
    echo "Failed to connect to MySQL: " . $con -> connect_error;
    exit();
}
?>