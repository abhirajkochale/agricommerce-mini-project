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

if (isset($_POST['statuses']) && is_array($_POST['statuses'])) {
    $update_query = "UPDATE order_items SET status = ? WHERE id = ? AND farmer_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    $valid_statuses = ['Pending', 'Shipped', 'Delivered'];
    
    mysqli_begin_transaction($conn);
    try {
        foreach ($_POST['statuses'] as $item_id => $status) {
            $item_id = intval($item_id);
            if (in_array($status, $valid_statuses)) {
                mysqli_stmt_bind_param($stmt, "sii", $status, $item_id, $user_id);
                mysqli_stmt_execute($stmt);
            }
        }
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header('Location: farmer_dashboard.php?error=update_failed');
        exit;
    }
}

header('Location: farmer_dashboard.php?success=status_updated');
exit;
?>
