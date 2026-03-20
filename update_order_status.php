<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$item_id = intval($_POST['item_id']);
$new_status = $_POST['status'];

// Validate status
$valid_statuses = ['Pending', 'Shipped', 'Delivered'];
if (!in_array($new_status, $valid_statuses)) {
    header('Location: farmer_dashboard.php?error=invalid_status');
    exit;
}

// Update the status only if the item belongs to the farmer
$update_query = "UPDATE order_items SET status = ? WHERE id = ? AND farmer_id = ?";
$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, "sii", $new_status, $item_id, $user_id);
mysqli_stmt_execute($stmt);

header('Location: farmer_dashboard.php?success=status_updated');
exit;
?>
