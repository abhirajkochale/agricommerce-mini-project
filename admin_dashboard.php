<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require 'db.php';
$pageTitle = "Admin Control Panel";
include 'includes/header.php';

// Stats
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$cropCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$farmerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='farmer'"))['c'];
?>

<div class="container">
    <h1>Admin Dashboard</h1>
    
    <div class="card-grid mb-3 mt-lg">
        <div class="card text-center border-top-primary">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo $userCount; ?></h2>
            <p class="text-muted">Total Users</p>
        </div>
        <div class="card text-center border-top-accent">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo $farmerCount; ?></h2>
            <p class="text-muted">Registered Farmers</p>
        </div>
        <div class="card text-center border-top-orange">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo $cropCount; ?></h2>
            <p class="text-muted">Active Listings</p>
        </div>
    </div>

    <section class="mb-lg">
        <h2>Manage All Users</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = mysqli_query($conn, "SELECT * FROM users");
                    while($u = mysqli_fetch_assoc($users)):
                    ?>
                    <tr>
                        <td>#<?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                        <td>
                            <a href="delete.php?type=user&id=<?php echo $u['id']; ?>" class="btn btn-danger-light btn-small" onclick="return confirm('Delete this user and all their crops?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Manage All Crop Listings</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Crop</th>
                        <th>Farmer</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $crops = mysqli_query($conn, "SELECT o.*, u.name as farmer_name FROM orders o JOIN users u ON o.user_id = u.id");
                    while($c = mysqli_fetch_assoc($crops)):
                    ?>
                    <tr>
                        <td>#<?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['crop_name']); ?></td>
                        <td><?php echo htmlspecialchars($c['farmer_name']); ?></td>
                        <td>Rs. <?php echo $c['price']; ?></td>
                        <td>
                            <a href="delete.php?id=<?php echo $c['id']; ?>" class="btn btn-danger-light btn-small" onclick="return confirm('Delete this listing?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
