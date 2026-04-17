<?php
require 'auth_check.php';
require 'db.php';

if ($_SESSION['role'] !== 'user') {
    header('Location: login.php?error=unauthorized');
    exit;
}

$pageTitle = "My Cart";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if (isset($_GET['remove']) && isset($_GET['csrf_token'])) {
    if (validate_csrf_token($_GET['csrf_token'])) {
        $remove_id = (int)$_GET['remove'];
        $del_stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($del_stmt, "ii", $remove_id, $user_id);
        mysqli_stmt_execute($del_stmt);
        header("Location: cart.php?success=removed");
        exit;
    } else {
        log_system_error("CSRF Failure in cart.php removal");
        die("Security validation failed.");
    }
}

$query = "SELECT c.id as cart_id, c.quantity, o.id as product_id, o.crop_name, o.price, o.category, u.name as farmer_name 
          FROM cart c 
          JOIN crops o ON c.product_id = o.id 
          JOIN users u ON o.user_id = u.id 
          WHERE c.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$total_price = 0;
?>

<div class="container">
    <div class="flex-between mb-2 mt-lg">
        <h1>Shopping Cart</h1>
        <a href="user_dashboard.php" class="btn btn-outline">Continue Shopping</a>
    </div>

    <?php if ($success === 'removed'): ?>
        <div class="alert alert-success">Item removed from cart.</div>
    <?php endif; ?>
    <?php if ($error === 'checkout_failed'): ?>
        <div class="alert alert-danger">Checkout failed. Please try again.</div>
    <?php endif; ?>

    <div class="card p-0 mb-lg overflow-hidden">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Farmer</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            $subtotal = $row['price'] * $row['quantity'];
                            $total_price += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['crop_name']); ?></strong><br>
                                    <span class="badge-category mt-sm" style="font-size: 0.7rem;"><?php echo htmlspecialchars($row['category']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?>/kg</td>
                                <td><?php echo (int)$row['quantity']; ?> kg</td>
                                <td class="font-w-600">Rs. <?php echo number_format($subtotal, 2); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $row['cart_id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                        class="btn btn-danger-light btn-small"
                                        onclick="return confirm('Remove this item?');">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-8">
                                <div class="mb-lg" style="font-size: 3rem; opacity: 0.2;">🛒</div>
                                <h3>Your cart is empty.</h3>
                                <p class="text-muted mb-lg">Looks like you haven't added anything yet.</p>
                                <a href="user_dashboard.php" class="btn btn-primary">Browse Marketplace</a>
                            </td>
                        </tr>
                    <?php endif; mysqli_stmt_close($stmt); ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_price > 0): ?>
            <div class="p-6 flex-between" style="border-top: 1px solid var(--border-color); background: #f8f9fa;">
                <div class="font-w-600" style="font-size: 1.2rem;">Total Amount:</div>
                <div class="font-w-700 text-primary-dark" style="font-size: 1.5rem;">Rs. <?php echo number_format($total_price, 2); ?></div>
            </div>
            <div class="p-6 flex-end" style="border-top: 1px solid var(--border-color);">
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>