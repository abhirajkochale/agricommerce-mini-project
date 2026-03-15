<?php

include 'db.php';

$id = $_POST['id'];
$name = $_POST['farmer_name'];
$crop = $_POST['crop_name'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$location = $_POST['location'];

mysqli_query($conn,"UPDATE orders SET
farmer_name='$name',
crop_name='$crop',
quantity='$quantity',
price='$price',
location='$location'
WHERE id=$id");

header("Location:index.php");

?>