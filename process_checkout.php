<?php
require 'auth_check.php';
require 'db.php';

if ($_SESSION['role'] !== 'user' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF Validation
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    log_system_error("CSRF Failure in process_checkout.php");
    die("Security validation failed.");
}

$user_id = $_SESSION['user_id'];

// Get Cart Items
$cart_query = "SELECT c.quantity, o.id as product_id, o.price, o.user_id as farmer_id 
               FROM cart c 
               JOIN crops o ON c.product_id = o.id 
               WHERE c.user_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($cart_result) === 0) {
    header('Location: user_dashboard.php');
    exit;
}

// Calculate Total
$total_amount = 0;
$items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $subtotal = $row['price'] * $row['quantity'];
    $total_amount += $subtotal;
    $items[] = $row;
}

// Generate Dummy Payment ID
$payment_id = "TXN_" . strtoupper(uniqid()) . rand(1000, 9999);

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // 1. Insert into checkout_orders
    $order_ins_query = "INSERT INTO checkout_orders (user_id, payment_id, total_amount) VALUES (?, ?, ?)";
    $order_stmt = mysqli_prepare($conn, $order_ins_query);
    mysqli_stmt_bind_param($order_stmt, "isd", $user_id, $payment_id, $total_amount);
    mysqli_stmt_execute($order_stmt);
    $order_id = mysqli_insert_id($conn);
    
    // 2. Insert into order_items
    $item_ins_query = "INSERT INTO order_items (order_id, farmer_id, product_id, quantity, price, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
    $item_stmt = mysqli_prepare($conn, $item_ins_query);
    
    foreach ($items as $item) {
        mysqli_stmt_bind_param($item_stmt, "iiiid", $order_id, $item['farmer_id'], $item['product_id'], $item['quantity'], $item['price']);
        mysqli_stmt_execute($item_stmt);
        
        // Update product stock (quantity)
        $stock_upd = "UPDATE crops SET quantity = quantity - ? WHERE id = ? AND quantity >= ?";
        $stock_stmt = mysqli_prepare($conn, $stock_upd);
        mysqli_stmt_bind_param($stock_stmt, "iii", $item['quantity'], $item['product_id'], $item['quantity']);
        mysqli_stmt_execute($stock_stmt);
    }
    
    // 3. Clear the user's cart
    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = mysqli_prepare($conn, $clear_cart_query);
    mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
    mysqli_stmt_execute($clear_stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Redirect to success
    header("Location: order_success.php?txn=" . $payment_id);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    log_system_error("Checkout Failed: " . $e->getMessage());
    header("Location: cart.php?error=checkout_failed");
    exit;
}
?>
