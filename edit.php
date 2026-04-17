<?php
require 'auth_check.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $sql = "SELECT * FROM crops WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    $sql = "SELECT * FROM crops WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        header('Location: index.php?error=not_found');
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    log_system_error("Prepare Failed in edit.php: " . mysqli_error($conn));
    header('Location: index.php?error=system_error');
    exit;
}

$pageTitle = "Edit Order #" . $id;
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h1 class="text-center">Edit Crop Listing</h1>
        <p class="text-muted text-center mb-lg">Update the crop details for #<?php echo $id; ?></p>
        
        <form action="update.php" method="POST" class="mt-lg" id="editForm">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="hidden" name="farmer_name" value="<?php echo htmlspecialchars($row['farmer_name']); ?>">
            
            <div class="form-group">
                <label>Crop Name</label>
                <input type="text" name="crop_name" class="form-control" value="<?php echo htmlspecialchars($row['crop_name']); ?>" required>
                <small class="error-message"></small>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($row['quantity']); ?>" required min="1">
                    <small class="error-message"></small>
                </div>
                
                <div class="form-group">
                    <label>Price (Rs/kg)</label>
                    <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($row['price']); ?>" required min="1">
                    <small class="error-message"></small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($row['location']); ?>" required>
                <small class="error-message"></small>
            </div>
            
            <div class="grid-2 mt-2">
                <button type="submit" class="btn btn-primary" id="editSubmit">Update Listing</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
