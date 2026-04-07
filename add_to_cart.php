<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if item already in cart
$check_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    $row = mysqli_fetch_assoc($result);
    $new_quantity = $row['quantity'] + $quantity;
    $cart_id = $row['id'];

    $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ii", $new_quantity, $cart_id);
    if (mysqli_stmt_execute($update_stmt)) {
        echo json_encode(['success' => true, 'message' => 'Cart quantity updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "iii", $user_id, $product_id, $quantity);
    if (mysqli_stmt_execute($insert_stmt)) {
        echo json_encode(['success' => true, 'message' => 'Added to cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
    }
}
?>