<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? '';

// Redirect Admin away from the public homepage
if ($role === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

$pageTitle = "Premium Agri-Commerce";
require 'db.php';
include 'includes/header.php';

// Prepare Farmer Stats if logged in as farmer
$stats = ['listings' => 0, 'orders' => 0, 'revenue' => 0];
if ($role === 'farmer') {
    // 1. Active Listings
    $stmt1 = mysqli_prepare($conn, "SELECT COUNT(*) FROM crops WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt1, "i", $user_id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_bind_result($stmt1, $stats['listings']);
    mysqli_stmt_fetch($stmt1);
    mysqli_stmt_close($stmt1);

    // 2. Total Orders (Items sold)
    $stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) FROM order_items WHERE farmer_id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $user_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_bind_result($stmt2, $stats['orders']);
    mysqli_stmt_fetch($stmt2);
    mysqli_stmt_close($stmt2);

    // 3. Potential Revenue (Delivered + Pending/Shipped)
    $stmt3 = mysqli_prepare($conn, "SELECT SUM(price * quantity) FROM order_items WHERE farmer_id = ? AND status != 'Cancelled'");
    mysqli_stmt_bind_param($stmt3, "i", $user_id);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_bind_result($stmt3, $stats['revenue']);
    mysqli_stmt_fetch($stmt3);
    mysqli_stmt_close($stmt3);
    $stats['revenue'] = $stats['revenue'] ?? 0;
}
?>

<div class="container">
    <?php if ($role === 'farmer'): ?>
        <!-- --- FARMER SELLER HUB --- -->
        <section class="hero-farmer">
            <h1>Maximize Your Harvest Reach</h1>
            <p>Empowering you with tools to manage listings, track orders, and grow your agricultural business.</p>
            <div class="mt-lg">
                <a href="farmer_dashboard.php" class="btn btn-primary"
                    style="padding: 1.25rem 2.5rem; font-size: 1.1rem;">List New Crop</a>
            </div>
        </section>

        <section class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">Active Listings</span>
                <span class="stat-value"><?php echo number_format($stats['listings']); ?></span>
                <p class="text-xs text-muted">Crops currently on market</p>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Sales</span>
                <span class="stat-value"><?php echo number_format($stats['orders']); ?></span>
                <p class="text-xs text-muted">Customer orders received</p>
            </div>
            <div class="stat-card">
                <span class="stat-label">Projected Revenue</span>
                <span class="stat-value">Rs. <?php echo number_format($stats['revenue'], 2); ?></span>
                <p class="text-xs text-muted">Total potential earnings</p>
            </div>
        </section>

        <section class="action-grid">
            <a href="farmer_dashboard.php" class="action-card">
                <div class="action-icon">🌽</div>
                <div class="action-content">
                    <h3>Manage Inventory</h3>
                    <p class="text-muted">Update quantities, prices, and crop details.</p>
                </div>
            </a>
            <a href="farmer_dashboard.php#orders" class="action-card">
                <div class="action-icon">🚚</div>
                <div class="action-content">
                    <h3>Track Deliveries</h3>
                    <p class="text-muted">Process pending orders and update statuses.</p>
                </div>
            </a>
        </section>

        <section class="highlights">
            <h2 class="text-center mb-lg">Seller Resources</h2>
            <div class="card-grid">
                <div class="card border-top-primary">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">📸</div>
                    <h3>Photography Tips</h3>
                    <p class="text-muted">Learn how high-quality photos can increase your sales conversion by 40%.</p>
                </div>
                <div class="card border-top-accent">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">📦</div>
                    <h3>Packaging Guide</h3>
                    <p class="text-muted">Best practices for sustainable and protective packaging for fresh products.</p>
                </div>
                <div class="card border-top-orange">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">💡</div>
                    <h3>Market Trends</h3>
                    <p class="text-muted">Currently in demand: Organic Tomatoes and Hybrid Grains are seeing a price surge.
                    </p>
                </div>
            </div>
        </section>

    <?php else: ?>
        <!-- --- GUEST / BUYER MARKETPLACE --- -->
        <?php if ($user_id): ?>
            <div class="welcome-bar mb-lg">
                <div>
                    <h2 class="m-0">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                    <p class="text-muted m-0">Ready to find some fresh products today?</p>
                </div>
                <div class="quick-links">
                    <a href="user_dashboard.php" class="quick-link-item">🛒 Marketplace</a>
                    <a href="my_orders.php" class="quick-link-item">📦 My Orders</a>
                </div>
            </div>
        <?php endif; ?>

        <section class="hero-v2">
            <h1>Fresh from Farms,<br>Direct to Your Door.</h1>
            <p>Connecting thousands of local farmers with buyers across the country. Experience the future of sustainable
                agriculture.</p>

            <form action="user_dashboard.php" method="GET" class="homepage-search">
                <input type="text" name="search" placeholder="Search for wheat, mangoes, tomatoes..." required>
                <button type="submit" class="search-btn">Find Products</button>
            </form>
        </section>

        <section class="category-section">
            <h2 class="text-center mb-lg">Browse by Category</h2>
            <div class="category-grid">
                <a href="user_dashboard.php?cat=Grains" class="category-card">
                    <span class="cat-icon">🌾</span>
                    <h4>Grains</h4>
                </a>
                <a href="user_dashboard.php?cat=Fruits" class="category-card">
                    <span class="cat-icon">🍎</span>
                    <h4>Fruits</h4>
                </a>
                <a href="user_dashboard.php?cat=Vegetables" class="category-card">
                    <span class="cat-icon">🥕</span>
                    <h4>Vegetables</h4>
                </a>
                <a href="user_dashboard.php?cat=Other" class="category-card">
                    <span class="cat-icon">📦</span>
                    <h4>Others</h4>
                </a>
            </div>
        </section>

        <section class="highlights mt-lg">
            <h2 class="text-center mb-lg">The AgroConnect Advantage</h2>
            <div class="card-grid">
                <div class="card border-top-primary">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">👨‍🌾</div>
                    <h3>Farmer Focused</h3>
                    <p class="text-muted">Direct-to-consumer sales with zero commission fees. List your crops in seconds and
                        manage orders effortlessly.</p>
                </div>
                <div class="card border-top-accent">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🛡️</div>
                    <h3>Secure Transactions</h3>
                    <p class="text-muted">Built-in fraud protection, encrypted sessions, and robust validation ensure your
                        data and payments are always safe.</p>
                </div>
                <div class="card border-top-orange">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📈</div>
                    <h3>Market Analytics</h3>
                    <p class="text-muted">Real-time stats for admins and transparent pricing for buyers, creating a fair and
                        efficient marketplace for all.</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>