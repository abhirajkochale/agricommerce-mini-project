<?php
require 'auth_check.php';
require 'db.php';

// CSRF Validation
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    die("Security validation failed.");
}

if ($_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);

if (!$item_id) {
    header('Location: my_orders.php?error=invalid_request');
    exit;
}

// 1. Verify ownership and check status
// We need to check if the order_item belongs to a checkout_order that belongs to this user.
$query = "SELECT oi.status, co.user_id 
          FROM order_items oi 
          JOIN checkout_orders co ON oi.order_id = co.id 
          WHERE oi.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$item || $item['user_id'] != $user_id) {
    header('Location: my_orders.php?error=access_denied');
    exit;
}

if ($item['status'] !== 'Pending') {
    header('Location: my_orders.php?error=cannot_cancel');
    exit;
}

// 2. Cancellation
// The MySQL Trigger 'restore_stock_after_cancel' handles the stock restoration automatically!
$upd_stmt = mysqli_prepare($conn, "UPDATE order_items SET status = 'Cancelled' WHERE id = ?");
mysqli_stmt_bind_param($upd_stmt, "i", $item_id);

if (mysqli_stmt_execute($upd_stmt)) {
    header('Location: my_orders.php?success=cancelled');
} else {
    log_system_error("Per-Item Cancellation Failed: " . mysqli_error($conn));
    header('Location: my_orders.php?error=system_error');
}
mysqli_stmt_close($upd_stmt);
?>
