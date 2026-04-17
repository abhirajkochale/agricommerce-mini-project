<?php
require 'auth_check.php';
require 'db.php';

if ($_SESSION['role'] !== 'farmer' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF Validation
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    log_system_error("CSRF Failure in update_all_orders_status.php");
    die("Security validation failed.");
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['statuses']) && is_array($_POST['statuses'])) {
    $update_query = "UPDATE order_items SET status = ? WHERE id = ? AND farmer_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    $valid_statuses = ['Pending', 'Shipped', 'Delivered'];
    
    mysqli_begin_transaction($conn);
    try {
        foreach ($_POST['statuses'] as $item_id => $status) {
            $item_id = (int)$item_id;
            if (in_array($status, $valid_statuses)) {
                mysqli_stmt_bind_param($stmt, "sii", $status, $item_id, $user_id);
                mysqli_stmt_execute($stmt);
            }
        }
        mysqli_commit($conn);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        log_system_error("Batch Status Update Failed: " . $e->getMessage());
        header('Location: farmer_dashboard.php?error=update_failed');
        exit;
    }
}

header('Location: farmer_dashboard.php?success=updated');
exit;
?>
