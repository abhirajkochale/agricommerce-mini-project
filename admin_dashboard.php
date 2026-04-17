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
require 'auth_check.php'; // Ensure CSRF functions are loaded

$pageTitle = "Admin Control Panel";
include 'includes/header.php';

// Fetch stats using direct count for efficiency in dashboard view
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$cropCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM crops"))['c'];
$farmerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='farmer'"))['c'];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container">
    <h1>Admin Dashboard</h1>

    <?php if ($success === 'user_deleted'): ?>
        <div class="alert alert-success">User and their listings deleted successfully.</div>
    <?php endif; ?>
    <?php if ($success === 'deleted'): ?>
        <div class="alert alert-success">Listing removed successfully.</div>
    <?php endif; ?>
    <?php if ($error === 'self_delete'): ?>
        <div class="alert alert-danger">You cannot delete your own admin account!</div>
    <?php endif; ?>

    <div class="card-grid mb-3 mt-lg">
        <div class="card text-center border-top-primary">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo (int) $userCount; ?></h2>
            <p class="text-muted">Total Users</p>
        </div>
        <div class="card text-center border-top-accent">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo (int) $farmerCount; ?></h2>
            <p class="text-muted">Registered Farmers</p>
        </div>
        <div class="card text-center border-top-orange">
            <h2 class="mb-sm" style="font-size: 2.5rem;"><?php echo (int) $cropCount; ?></h2>
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
                    $users = mysqli_query($conn, "SELECT id, name, email, role FROM users");
                    while ($u = mysqli_fetch_assoc($users)):
                        ?>
                        <tr>
                            <td>#<?php echo (int) $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($u['role']); ?>">
                                    <?php echo strtoupper(htmlspecialchars($u['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="delete.php?type=user&id=<?php echo $u['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                    class="btn btn-danger-light btn-small"
                                    onclick="return confirm('Delete this user and all their crops?');">Delete</a>
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
                    $crops = mysqli_query($conn, "SELECT o.id, o.crop_name, o.price, u.name as farmer_name 
                                                 FROM crops o JOIN users u ON o.user_id = u.id");
                    while ($c = mysqli_fetch_assoc($crops)):
                        ?>
                        <tr>
                            <td>#<?php echo (int) $c['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($c['crop_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['farmer_name']); ?></td>
                            <td>Rs. <?php echo number_format($c['price'], 2); ?></td>
                            <td>
                                <a href="delete.php?id=<?php echo $c['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                    class="btn btn-danger-light btn-small"
                                    onclick="return confirm('Delete this listing?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>