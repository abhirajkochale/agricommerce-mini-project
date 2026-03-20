<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'user';
$type = $_GET['type'] ?? 'order';

// Handle user deletion by admin
if ($type === 'user' && $role === 'admin') {
    if ($id == $user_id) {
        header("Location: admin_dashboard.php?error=self_delete");
        exit;
    }
    // Delete user's listings first to avoid FK issues
    mysqli_query($conn, "DELETE FROM orders WHERE user_id=$id");
    // Delete user
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");

    header("Location: admin_dashboard.php");
    exit;
}

// Handle order deletion
if ($role === 'admin') {
    mysqli_query($conn, "DELETE FROM orders WHERE id=$id");
}
else {
    // Basic owner check
    mysqli_query($conn, "DELETE FROM orders WHERE id=$id AND user_id=$user_id");
}

header("Location: index.php");
?>