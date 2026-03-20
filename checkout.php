<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Check if cart is empty
$user_id = $_SESSION['user_id'];
$query = "SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row['total'] || $row['total'] <= 0) {
    header("Location: user_dashboard.php");
    exit;
}

$pageTitle = "Checkout";
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width: 600px;">
        <h1 class="text-center">Secure Checkout</h1>
        <p class="text-muted text-center mb-lg">Complete your purchase</p>

        <form action="process_checkout.php" method="POST" id="checkoutForm">
            <h3 class="mb-sm">Shipping Information</h3>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" class="form-control" required rows="3" placeholder="Enter full address..."></textarea>
            </div>
            
            <h3 class="mb-sm mt-lg">Payment Details (Dummy Gateway)</h3>
            <div class="alert alert-info mb-sm">This is a simulated checkout. No real money will be charged.</div>
            
            <div class="form-group">
                <label>Name on Card</label>
                <input type="text" name="card_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Card Number</label>
                <input type="text" name="card_number" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX" required pattern="\d{4}-\d{4}-\d{4}-\d{4}|[0-9]{16}" title="16 digit card number">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Expiry (MM/YY)</label>
                    <input type="text" name="expiry" class="form-control" placeholder="12/26" required>
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="4" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-lg" style="font-size: 1.1rem; padding: 1rem;">Pay Now</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
