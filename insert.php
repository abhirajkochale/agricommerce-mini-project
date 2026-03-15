<?php
include 'db.php';

$name = $_POST['farmer_name'];
$email = $_POST['email'];
$crop = $_POST['crop_name'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$location = $_POST['location'];

$query = "INSERT INTO orders(farmer_name,email,crop_name,category,quantity,price,location)
VALUES('$name','$email','$crop','$category','$quantity','$price','$location')";

mysqli_query($conn,$query);

header("Location:index.php");
?>