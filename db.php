<?php
$host = "localhost";
$user = "root";
$password = ""; // Default XAMPP password is empty
$database = "agroconnect";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>