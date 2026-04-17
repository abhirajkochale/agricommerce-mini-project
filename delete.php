<?php
require 'auth_check.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// CSRF Validation (for GET request)
$token = $_GET['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    log_system_error("CSRF Failure in delete.php");
    die("Security validation failed.");
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$type = $_GET['type'] ?? 'order';

// Handle user deletion by admin
if ($type === 'user' && $role === 'admin') {
    if ($id == $user_id) {
        header("Location: admin_dashboard.php?error=self_delete");
        exit;
    }
    
    // Use transaction for atomic deletion
    mysqli_begin_transaction($conn);
    try {
        // Delete user's listings first to avoid FK issues
        $s1 = mysqli_prepare($conn, "DELETE FROM orders WHERE user_id=?");
        mysqli_stmt_bind_param($s1, "i", $id);
        mysqli_stmt_execute($s1);
        
        // Delete user
        $s2 = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
        mysqli_stmt_bind_param($s2, "i", $id);
        mysqli_stmt_execute($s2);
        
        mysqli_commit($conn);
        header("Location: admin_dashboard.php?success=user_deleted");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        log_system_error("User Delete Failed: " . $e->getMessage());
        header("Location: admin_dashboard.php?error=delete_failed");
    }
    exit;
}

// Handle order/listing deletion
if ($role === 'admin') {
    $sql = "DELETE FROM orders WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    // Basic owner check
    $sql = "DELETE FROM orders WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
}

if ($stmt) {
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: index.php?success=deleted");
        } else {
            header("Location: index.php?error=not_found");
        }
    } else {
        log_system_error("Delete Failed: " . mysqli_error($conn));
        header("Location: index.php?error=db_error");
    }
    mysqli_stmt_close($stmt);
} else {
    log_system_error("Prepare Failed: " . mysqli_error($conn));
    header("Location: index.php?error=system_error");
}
?>