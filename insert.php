<?php
require 'auth_check.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        log_system_error("CSRF Failure in insert.php");
        die("Security validation failed.");
    }

    $name = trim($_POST['farmer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $crop = trim($_POST['crop_name'] ?? '');
    $category = $_POST['category'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Basic Validation
    if ($name === '' || $crop === '' || $quantity <= 0 || $price <= 0) {
        header("Location: farmer_dashboard.php?error=invalid_data");
        exit;
    }

    $sql = "INSERT INTO crops (farmer_name, email, crop_name, category, quantity, price, location, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssidss", $name, $email, $crop, $category, $quantity, $price, $location, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: farmer_dashboard.php?success=inserted");
        } else {
            log_system_error("Insert Failed: " . mysqli_error($conn));
            header("Location: farmer_dashboard.php?error=db_error");
        }
    } else {
        log_system_error("Prepare Failed: " . mysqli_error($conn));
        header("Location: farmer_dashboard.php?error=system_error");
    }
} else {
    header("Location: farmer_dashboard.php");
}
?>