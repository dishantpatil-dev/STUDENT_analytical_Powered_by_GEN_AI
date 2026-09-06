<?php
$host = "sql309.infinityfree.com";
$user = "if0_42677234";
$pass = "NyPfKmNNp1EqJg"; // Ensure your DB password is placed here
$dbname = "if0_42677234_studentanalytics";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>