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
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: my_orders.php?error=invalid_request');
    exit;
}

// 1. Verify ownership and check if ANY item has already been shipped
$check_query = "SELECT status FROM order_items WHERE order_id = ? AND status != 'Cancelled'";
$check_inner = "SELECT id FROM checkout_orders WHERE id = ? AND user_id = ?";

// Verify ownership first
$own_stmt = mysqli_prepare($conn, $check_inner);
mysqli_stmt_bind_param($own_stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($own_stmt);
$own_result = mysqli_stmt_get_result($own_stmt);
if (mysqli_num_rows($own_result) === 0) {
    header('Location: my_orders.php?error=access_denied');
    exit;
}
mysqli_stmt_close($own_stmt);

// Check statuses: All must be 'Pending' or 'Cancelled' to allow whole order cancellation
$status_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as non_cancellable FROM order_items WHERE order_id = ? AND status NOT IN ('Pending', 'Cancelled')");
mysqli_stmt_bind_param($status_stmt, "i", $order_id);
mysqli_stmt_execute($status_stmt);
$status_res = mysqli_stmt_get_result($status_stmt);
$row = mysqli_fetch_assoc($status_res);
$non_cancellable_count = $row['non_cancellable'];
mysqli_stmt_close($status_stmt);

if ($non_cancellable_count > 0) {
    header('Location: my_orders.php?error=cannot_cancel_shipped');
    exit;
}

// 2. Begin Cancellation
// The MySQL Trigger 'restore_stock_after_cancel' will handle the stock restoration automatically
mysqli_begin_transaction($conn);

try {
    $upd_stmt = mysqli_prepare($conn, "UPDATE order_items SET status = 'Cancelled' WHERE order_id = ? AND status = 'Pending'");
    mysqli_stmt_bind_param($upd_stmt, "i", $order_id);
    mysqli_stmt_execute($upd_stmt);
    
    if (mysqli_stmt_affected_rows($upd_stmt) === 0) {
        throw new Exception("No pending items found to cancel.");
    }
    mysqli_stmt_close($upd_stmt);

    mysqli_commit($conn);
    header('Location: my_orders.php?success=order_cancelled');
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    log_system_error("Trigger-Based Cancellation Failed: " . $e->getMessage());
    header('Location: my_orders.php?error=already_cancelled');
    exit;
}
?>
