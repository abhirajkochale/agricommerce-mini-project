<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $result = mysqli_query($conn, "SELECT * FROM orders WHERE id=$id");
}
else {
    $result = mysqli_query($conn, "SELECT * FROM orders WHERE id=$id AND user_id=$user_id");
}

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$row = mysqli_fetch_assoc($result);

$pageTitle = "Edit Order #" . $row['id'];
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h1 class="text-center">Edit Crop Listing</h1>
        <p class="text-muted text-center mb-lg">Update the crop details for #<?php echo $id; ?></p>
        
        <form action="update.php" method="POST" class="mt-lg">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <input type="hidden" name="farmer_name" value="<?php echo htmlspecialchars($row['farmer_name']); ?>">
            
            <div class="form-group">
                <label>Crop Name</label>
                <input type="text" name="crop_name" class="form-control" value="<?php echo htmlspecialchars($row['crop_name']); ?>" required>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Price (Rs/kg)</label>
                    <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($row['location']); ?>" required>
            </div>
            
            <div class="grid-2 mt-2">
                <button type="submit" class="btn btn-primary">Update Listing</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
