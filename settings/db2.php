<?php
$con = new mysqli("db5002279518.hosting-data.io","dbu1534910","pAinIntheA$$12!","dbs1836601");

// Check connection
if ($con -> connect_errno) {
    echo "Failed to connect to MySQL: " . $con -> connect_error;
    exit();
}
?>