<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header('Location: login.php');
    exit;
}

require 'db.php';
require 'auth_check.php'; // Ensure CSRF functions are loaded

$pageTitle = "Farmer Dashboard";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
// Table structure check is handled centrally, but we can ensure column exists if needed
// mysqli_query($conn, "ALTER TABLE orders ADD COLUMN IF NOT EXISTS user_id INT");
?>

<div class="container">
    <div class="flex-between mb-2">
        <h1>Farmer Dashboard</h1>
        <div class="font-w-600 text-primary-dark">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
    </div>

    <!-- Alert System -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'inserted'): ?>
            <div class="alert alert-success">New crop listing added successfully!</div>
        <?php elseif ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success">Listing updated successfully!</div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">Listing removed successfully!</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">An error occurred: <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <section class="card mb-3">
        <h3>List New Crop</h3>
        <form action="insert.php" method="POST" id="listingForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="grid-auto">
                <input type="hidden" name="farmer_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                
                <div class="form-group">
                    <label>Crop Name</label>
                    <input type="text" name="crop_name" class="form-control" placeholder="e.g. Wheat" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option>Grains</option>
                        <option>Vegetables</option>
                        <option>Fruits</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input type="number" name="quantity" class="form-control" placeholder="kg" required min="1">
                </div>
                <div class="form-group">
                    <label>Price (Rs/kg)</label>
                    <input type="number" name="price" class="form-control" placeholder="Price" required min="1">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" placeholder="City/Village" required>
                </div>
                <div class="form-group flex-end">
                    <button type="submit" class="btn btn-primary btn-block" id="listCropSubmit">List Crop</button>
                </div>
            </div>
        </form>
    </section>

    <section>
        <h2>My Listed Crops</h2>
        <div class="card-grid">
            <?php
            // Secure query with user_id
            $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
            ?>
                <div class="card">
                    <div class="flex-between mb-sm">
                        <span class="badge-category"><?php echo htmlspecialchars($row['category']); ?></span>
                        <span class="text-sm text-muted">#<?php echo htmlspecialchars($row['id']); ?></span>
                    </div>
                    <h3 class="mb-sm"><?php echo htmlspecialchars($row['crop_name']); ?></h3>
                    <div class="grid-2 mb-lg">
                        <div>
                            <p class="text-xs text-muted">Quantity</p>
                            <p class="font-w-600"><?php echo htmlspecialchars($row['quantity']); ?> kg</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">Price</p>
                            <p class="font-w-600 text-primary-dark">Rs. <?php echo number_format($row['price'], 2); ?></p>
                        </div>
                    </div>
                    <p class="text-sm mb-lg">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                    <div class="grid-2">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-success-light btn-small">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                           class="btn btn-danger-light btn-small" 
                           onclick="return confirm('Delete this listing?');">Delete</a>
                    </div>
                </div>
            <?php
                endwhile;
            else: ?>
                <div class="card p-8 text-center" style="grid-column: 1 / -1;">
                    <p>No crops listed yet. Start by adding one above!</p>
                </div>
            <?php endif; mysqli_stmt_close($stmt); ?>
        </div>
    </section>

    <section class="mt-lg">
        <form action="update_all_orders_status.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="flex-between mb-sm mt-2">
            <h2 class="mb-0">Customer Orders</h2>
            <button type="submit" class="btn btn-primary font-w-600">Save All Changes</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Crop</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $order_query = "SELECT oi.id as item_id, oi.order_id, oi.quantity, oi.price, oi.status, 
                                   o.crop_name, u.name as customer_name 
                            FROM order_items oi 
                            JOIN checkout_orders co ON oi.order_id = co.id 
                            JOIN orders o ON oi.product_id = o.id 
                            JOIN users u ON co.user_id = u.id 
                            WHERE oi.farmer_id = ? 
                            ORDER BY co.created_at DESC";
                    $ostmt = mysqli_prepare($conn, $order_query);
                    mysqli_stmt_bind_param($ostmt, "i", $user_id);
                    mysqli_stmt_execute($ostmt);
                    $orders_result = mysqli_stmt_get_result($ostmt);

                    if (mysqli_num_rows($orders_result) > 0):
                        while ($orow = mysqli_fetch_assoc($orders_result)):
                    ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($orow['order_id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($orow['crop_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($orow['quantity']); ?> kg</td>
                            <td>Rs. <?php echo number_format($orow['price'] * $orow['quantity'], 2); ?></td>
                            <td><?php echo htmlspecialchars($orow['customer_name']); ?></td>
                            <td>
                                <?php 
                                    $bg = '#eff6ff'; $fg = '#2563eb';
                                    if($orow['status']=='Shipped') { $bg='#fff7ed'; $fg='#c2410c'; }
                                    if($orow['status']=='Delivered') { $bg='#f0fdf4'; $fg='#15803d'; }
                                    if($orow['status']=='Cancelled') { $bg='#f1f5f9'; $fg='#475569'; }
                                ?>
                                <span style="display:inline-block; padding: 4px 10px; border-radius: 12px; background: <?php echo $bg; ?>; color: <?php echo $fg; ?>; font-size: 0.75rem; font-weight: 700;">
                                    <?php echo htmlspecialchars($orow['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($orow['status'] !== 'Cancelled'): ?>
                                    <select name="statuses[<?php echo htmlspecialchars($orow['item_id']); ?>]" class="form-control" style="padding: 0.2rem; min-width: 100px; height:auto; font-size: 0.9rem;">
                                        <option value="Pending" <?php if($orow['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Shipped" <?php if($orow['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if($orow['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                <?php else: ?>
                                    <span class="text-xs text-muted">No actions available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else: ?>
                        <tr><td colspan="7" class="text-center p-8">No customer orders yet. Keep listing fresh crops!</td></tr>
                    <?php endif; mysqli_stmt_close($ostmt); ?>
                </tbody>
            </table>
        </div>
        </form>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
