<?php
session_start();
include 'db.php';

if (!isset($_POST['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$id = $_POST['id'];
$name = $_POST['farmer_name'];
$crop = $_POST['crop_name'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$location = $_POST['location'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Only owner or admin can update
if ($role === 'admin') {
    mysqli_query($conn, "UPDATE orders SET
        crop_name='$crop',
        quantity='$quantity',
        price='$price',
        location='$location'
        WHERE id=$id");
}
else {
    mysqli_query($conn, "UPDATE orders SET
        crop_name='$crop',
        quantity='$quantity',
        price='$price',
        location='$location'
        WHERE id=$id AND user_id=$user_id");
}

header("Location: index.php");
?>