<?php
/**
 * submit_feedback.php - Secure Submission of Buyer Feedback
 */
require 'auth_check.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_dashboard.php');
    exit;
}

// 1. CSRF Validation
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    log_system_error("CSRF Failure in submit_feedback.php");
    die("Security validation failed.");
}

// 2. Data Sanitization & Extraction
$user_id = $_SESSION['user_id'];
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$message = trim($_POST['message'] ?? '');

// Determine redirect destination (default to dashboard)
$redirect = $_POST['redirect'] ?? 'user_dashboard.php';
// Sanitize redirect to prevent open redirect
if (!preg_match('/^[a-zA-Z0-9_\-\.?=]+$/', $redirect)) {
    $redirect = 'user_dashboard.php';
}

// 3. If the user skipped both fields, just redirect silently
if ($rating === 0 && empty($message)) {
    header('Location: ' . $redirect);
    exit;
}

// Apply soft defaults for partial submissions
if ($rating < 1 || $rating > 5) $rating = 3;
if (empty($message)) $message = '(No comment)';

// 4. Persistence with Prepared Statement
$sql = "INSERT INTO feedback (user_id, rating, message) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "iis", $user_id, $rating, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header('Location: user_dashboard.php?success=feedback_submitted');
        exit;
    } else {
        log_system_error("Feedback Insertion Failed: " . mysqli_error($conn));
        header('Location: user_dashboard.php?error=submission_failed');
    }
    mysqli_stmt_close($stmt);
} else {
    log_system_error("Database Prepare Failed in submit_feedback.php");
    header('Location: user_dashboard.php?error=system_error');
}

exit;
?>
