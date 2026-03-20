<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

require 'db.php';
$pageTitle = "My Orders";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT co.id as order_id, co.payment_id, co.total_amount, co.created_at, 
                 oi.product_id, oi.quantity, oi.price, oi.status, 
                 o.crop_name, o.category, u.name as farmer_name
          FROM checkout_orders co
          JOIN order_items oi ON co.id = oi.order_id
          JOIN orders o ON oi.product_id = o.id
          JOIN users u ON oi.farmer_id = u.id
          WHERE co.user_id = ?
          ORDER BY co.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['order_id']]['details'] = [
        'payment_id' => $row['payment_id'],
        'total' => $row['total_amount'],
        'date' => $row['created_at']
    ];
    $orders[$row['order_id']]['items'][] = $row;
}
?>

<div class="container mt-lg mb-lg">
    <div class="flex-between mb-2">
        <h1>My Order History</h1>
        <a href="user_dashboard.php" class="btn btn-outline">Continue Shopping</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card p-8 text-center">
            <h3>You haven't placed any orders yet.</h3>
            <p>Go to the marketplace to start buying fresh produce!</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card p-4 mb-lg" style="border-left: 4px solid var(--primary-color);">
                <div class="flex-between mb-sm" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <div>
                        <span class="text-muted text-sm">Order Placed: <strong><?php echo date('M d, Y', strtotime($order['details']['date'])); ?></strong></span><br>
                        <span class="text-muted text-sm">Transaction: <strong style="font-family: monospace;"><?php echo htmlspecialchars($order['details']['payment_id']); ?></strong></span>
                    </div>
                    <div class="text-right">
                        <span class="text-muted text-sm">Total Amount:</span><br>
                        <strong class="text-primary-dark" style="font-size: 1.2rem;">Rs. <?php echo number_format($order['details']['total'], 2); ?></strong>
                    </div>
                </div>
                
                <div class="table-container mt-sm">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Sold By</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['crop_name']); ?></strong><br>
                                    <span class="badge badge-category mt-sm" style="font-size: 0.65rem; padding: 2px 6px;"><?php echo htmlspecialchars($item['category']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($item['farmer_name']); ?></td>
                                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?> kg</td>
                                <td>
                                    <?php 
                                        $badge_class = 'badge-user'; // default blue
                                        if ($item['status'] === 'Shipped') $badge_class = 'badge-farmer'; // yellow/orange
                                        if ($item['status'] === 'Delivered') $badge_class = 'badge-admin'; // red (just using existing styles)
                                        // or simple custom styles
                                        $color = ($item['status'] === 'Delivered') ? '#2ecc71' : (($item['status'] === 'Shipped') ? '#f39c12' : '#3498db');
                                    ?>
                                    <span style="display:inline-block; padding: 4px 10px; border-radius: 12px; background: <?php echo $color; ?>; color: white; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($item['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
