<?php
mysqli_report(MYSQLI_REPORT_OFF);

$con = @mysqli_connect("127.0.0.1", "root", "", "property_tax", 3306);
if (!$con) {
    $con = @mysqli_connect("127.0.0.1", "root", "", "property_tax", 3307);
}
if (!$con) {
    $con = @mysqli_connect("localhost", "root", "", "property_tax");
}

if (!$con) {
    die("Database connection failed. Please check that MySQL is running in XAMPP.");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
