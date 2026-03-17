<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Check if logged in and is farmer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header('Location: login.php');
    exit;
}

require 'db.php';
$pageTitle = "Farmer Dashboard";
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Add user_id column to orders if it doesn't exist
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
                        while($row = mysqli_fetch_assoc($result)):
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
                    <?php endwhile; 
                    else: ?>
                    <tr><td colspan="6" class="text-center p-8">No crops listed yet. Start by adding one above!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
