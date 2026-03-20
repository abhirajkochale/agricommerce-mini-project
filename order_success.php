<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$txn = $_GET['txn'] ?? null;

if (!$txn || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Order Successful";
include 'includes/header.php';
?>

<div class="container text-center" style="margin-top: 4rem; margin-bottom: 4rem;">
    <div class="card d-inline-block p-8" style="max-width: 500px; margin: 0 auto;">
        <div style="font-size: 4rem; color: #2ecc71; margin-bottom: 1rem;">✅</div>
        <h1 class="mb-sm">Order Received!</h1>
        <p class="text-muted mb-lg">Thank you for your purchase. Your order has been securely processed and sent to the respective farmers.</p>
        
        <div class="p-4 mb-lg" style="background: var(--bg-light); border-radius: var(--radius-sm); border: 1px dashed var(--border-color);">
            <p class="text-sm mb-0">Transaction ID</p>
            <h3 class="mt-0" style="font-family: monospace; letter-spacing: 1px;"><?php echo htmlspecialchars($txn); ?></h3>
        </div>
        
        <div class="grid-2">
            <a href="my_orders.php" class="btn btn-outline">View My Orders</a>
            <a href="user_dashboard.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
