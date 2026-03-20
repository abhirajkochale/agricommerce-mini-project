<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$count = $row['total'] ? intval($row['total']) : 0;

echo json_encode(['count' => $count]);
?>
