<?php
$host = "localhost";   // usually localhost
$user = "root";        // default XAMPP user
$pass = "";            // default XAMPP password is empty
$dbname = "u582110486_anavitch"; // <-- put your database name here

$con = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone to Central US (Chicago)
date_default_timezone_set('America/Chicago');

// Set MySQL timezone to match PHP (automatically handles DST)
$now = new DateTime('now', new DateTimeZone('America/Chicago'));
$mysql_timezone = $now->format('P'); // Returns offset like -06:00 or -05:00
mysqli_query($con, "SET time_zone = '$mysql_timezone'");
?>
