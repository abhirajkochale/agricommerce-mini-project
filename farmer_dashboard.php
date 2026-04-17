<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header('Location: login.php');
    exit;
}

require 'db.php';
$pageTitle = "Farmer Dashboard";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
mysqli_query($conn, "ALTER TABLE orders ADD COLUMN IF NOT EXISTS user_id INT");
?>

<div class="container">
    <div class="flex-between mb-2">
        <h1>Farmer Dashboard</h1>
        <div class="font-w-600 text-primary-dark">Welcome, Seller!</div>
    </div>

    <section class="card mb-3">
        <h3>List New Crop</h3>
        <form action="insert.php" method="POST">
            <div class="grid-auto">
                <!-- Pre-fill farmer name from session -->
                <input type="hidden" name="farmer_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                <input type="hidden" name="email" value=""> <!-- We'll handle this in logic or remove if redundant -->
                
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
                    <input type="number" name="quantity" class="form-control" placeholder="kg" required>
                </div>
                <div class="form-group">
                    <label>Price (Rs/kg)</label>
                    <input type="number" name="price" class="form-control" placeholder="Price" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" placeholder="City/Village" required>
                </div>
                <div class="form-group flex-end">
                    <button type="submit" class="btn btn-primary btn-block">List Crop</button>
                </div>
            </div>
        </form>
    </section>

    <section>
        <h2>My Listed Crops</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Crop</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
// Optimization: Filter by user_id
$result = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
if (mysqli_num_rows($result) > 0):
    while ($row = mysqli_fetch_assoc($result)):
?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['crop_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?> kg</td>
                        <td>Rs. <?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td>
                            <div class="flex-gap-1">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-success-light btn-small">Edit</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger-light btn-small" onclick="return confirm('Delete this listing?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php
    endwhile;
else: ?>
                    <tr><td colspan="6" class="text-center p-8">No crops listed yet. Start by adding one above!</td></tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-lg">
        <form action="update_all_orders_status.php" method="POST">
        <div class="flex-between mb-sm">
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
                WHERE oi.farmer_id = $user_id 
                ORDER BY co.created_at DESC";
$orders_result = mysqli_query($conn, $order_query);

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
                                $bg = '#3498db'; 
                                if($orow['status']=='Shipped') $bg='#f39c12';
                                if($orow['status']=='Delivered') $bg='#2ecc71';
                            ?>
                            <span style="display:inline-block; padding: 4px 10px; border-radius: 12px; background: <?php echo $bg; ?>; color: white; font-size: 0.8rem; font-weight: 600;">
                                <?php echo htmlspecialchars($orow['status']); ?>
                            </span>
                        </td>
                        <td>
                            <select name="statuses[<?php echo htmlspecialchars($orow['item_id']); ?>]" class="form-control" style="padding: 0.2rem; min-width: 100px; height:auto;">
                                <option value="Pending" <?php if($orow['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Shipped" <?php if($orow['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="Delivered" <?php if($orow['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                            </select>
                        </td>
                    </tr>
                    <?php
    endwhile;
else: ?>
                    <tr><td colspan="7" class="text-center p-8">No customer orders yet. Keep listing fresh crops!</td></tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
        </form>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
