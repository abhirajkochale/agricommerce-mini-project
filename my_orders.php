<?php
require 'auth_check.php';
require 'db.php';

if ($_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$pageTitle = "My Orders";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT co.id as order_id, co.payment_id, co.total_amount, co.created_at, 
                 oi.id as item_id, oi.product_id, oi.quantity, oi.price, oi.status, 
                 o.crop_name, o.category, u.name as farmer_name
          FROM checkout_orders co
          JOIN order_items oi ON co.id = oi.order_id
          JOIN crops o ON oi.product_id = o.id
          JOIN users u ON oi.farmer_id = u.id
          WHERE co.user_id = ?
          ORDER BY co.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[$row['order_id']]['details'] = [
            'payment_id' => $row['payment_id'],
            'total' => $row['total_amount'],
            'date' => $row['created_at']
        ];
        $orders[$row['order_id']]['items'][] = $row;
    }
}
mysqli_stmt_close($stmt);
?>

<div class="container mt-lg mb-lg">
    <div class="flex-between mb-2">
        <h1>My Order History</h1>
        <a href="user_dashboard.php" class="btn btn-outline">Continue Shopping</a>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
        <div class="alert alert-success mb-lg">Order item cancelled successfully. Stock has been returned to the farmer.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-lg">
            <?php 
                if($_GET['error'] === 'cannot_cancel') echo "This item cannot be cancelled as it is already being processed or shipped.";
                else if($_GET['error'] === 'access_denied') echo "You do not have permission to manage this order.";
                else echo "An error occurred while processing your cancellation. Please try again.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="card p-8 text-center">
            <div class="mb-lg" style="font-size: 3rem; opacity: 0.2;">📦</div>
            <h3>You haven't placed any orders yet.</h3>
            <p class="text-muted mb-lg">Go to the marketplace to start buying fresh produce!</p>
            <a href="user_dashboard.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card p-0 mb-lg overflow-hidden" style="border-top: 4px solid var(--primary-color);">
                <div class="p-6 flex-between" style="background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-color);">
                    <div class="header-details">
                        <div class="text-muted text-sm mb-sm">Order <strong class="text-primary-dark">#<?php echo $order_id; ?></strong> &bull; <?php echo date('M d, Y', strtotime($order['details']['date'])); ?></div>
                        <div class="font-w-700 text-primary-dark" style="font-size: 1.4rem;">Rs. <?php echo number_format($order['details']['total'], 2); ?></div>
                    </div>
                </div>
                
                <div class="table-container">
                    <table style="border: none;">
                        <thead style="background: rgba(0,0,0,0.01);">
                            <tr>
                                <th style="background: transparent; color: var(--text-muted); padding: 1.25rem 1.5rem;">Product Details</th>
                                <th style="background: transparent; color: var(--text-muted); padding: 1.25rem 1.5rem;">Seller</th>
                                <th style="background: transparent; color: var(--text-muted); padding: 1.25rem 1.5rem;">Price/Qty</th>
                                <th style="background: transparent; color: var(--text-muted); padding: 1.25rem 1.5rem;">Status & Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <strong><?php echo htmlspecialchars($item['crop_name']); ?></strong><br>
                                    <span class="badge-category mt-sm" style="font-size: 0.65rem;"><?php echo htmlspecialchars($item['category']); ?></span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; color: var(--text-main);"><?php echo htmlspecialchars($item['farmer_name']); ?></td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div class="font-w-600">Rs. <?php echo number_format($item['price'], 2); ?></div>
                                    <span class="text-muted text-sm">Qty: <?php echo (int)$item['quantity']; ?> kg</span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div class="flex-between" style="gap: 1rem;">
                                        <?php 
                                            $bg = '#eff6ff'; $fg = '#2563eb'; // Default Blue
                                            if($item['status']=='Shipped') { $bg='#fff7ed'; $fg='#c2410c'; }
                                            if($item['status']=='Delivered') { $bg='#f0fdf4'; $fg='#15803d'; }
                                            if($item['status']=='Cancelled') { $bg='#f1f5f9'; $fg='#475569'; }
                                        ?>
                                        <span style="display:inline-block; padding: 6px 14px; border-radius: 20px; background: <?php echo $bg; ?>; color: <?php echo $fg; ?>; font-size: 0.75rem; font-weight: 700;">
                                            <?php echo htmlspecialchars($item['status']); ?>
                                        </span>

                                        <?php if ($item['status'] === 'Pending'): ?>
                                            <form action="cancel_item.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this item? The stock will be returned to the farmer.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <button type="submit" class="btn btn-outline btn-small" style="color: var(--error-color); border-color: var(--error-color); padding: 4px 12px; font-size: 0.7rem;">Cancel Item</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-5" style="background: rgba(0,0,0,0.01); text-align: right; border-top: 1px solid var(--border-color);">
                    <span class="text-sm text-muted">Transaction ID: <code style="background: var(--bg-white); border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 6px;"><?php echo htmlspecialchars($order['details']['payment_id']); ?></code></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
