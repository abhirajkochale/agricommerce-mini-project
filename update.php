<?php
require 'auth_check.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        log_system_error("CSRF Failure in update.php");
        die("Security validation failed.");
    }

    $id = (int)($_POST['id'] ?? 0);
    $crop = trim($_POST['crop_name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($id <= 0 || $crop === '' || $quantity <= 0 || $price <= 0) {
        header("Location: index.php?error=invalid_data");
        exit;
    }

    // Only owner or admin can update
    if ($role === 'admin') {
        $sql = "UPDATE orders SET crop_name=?, quantity=?, price=?, location=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sidis", $crop, $quantity, $price, $location, $id);
    } else {
        $sql = "UPDATE orders SET crop_name=?, quantity=?, price=?, location=? WHERE id=? AND user_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sidisi", $crop, $quantity, $price, $location, $id, $user_id);
    }

    if ($stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            if ($affected > 0) {
                header("Location: index.php?success=updated");
            } else {
                header("Location: index.php?error=no_changes");
            }
        } else {
            log_system_error("Update Failed: " . mysqli_error($conn));
            header("Location: index.php?error=db_error");
        }
    } else {
        log_system_error("Prepare Failed: " . mysqli_error($conn));
        header("Location: index.php?error=system_error");
    }
} else {
    header("Location: index.php");
}
?>