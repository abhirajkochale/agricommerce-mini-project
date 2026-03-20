<?php
session_start();
if (!isset($_SESSION['user_id']))
    exit;
include 'db.php';

$name = $_POST['farmer_name'];
$email = $_POST['email'];
$crop = $_POST['crop_name'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$location = $_POST['location'];

$user_id = $_SESSION['user_id'];

$query = "INSERT INTO orders(farmer_name,email,crop_name,category,quantity,price,location,user_id)
VALUES('$name','$email','$crop','$category','$quantity','$price','$location','$user_id')";

mysqli_query($conn, $query);

header("Location:index.php");
?>