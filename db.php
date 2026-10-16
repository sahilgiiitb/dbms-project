<?php
mysqli_report(MYSQLI_REPORT_OFF);

$con = @mysqli_connect("127.0.0.1", "root", "", "covid_vaccination", 3306);

if (!$con) {
    $con = mysqli_connect("127.0.0.1", "root", "", "covid_vaccination", 3307);
}

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
