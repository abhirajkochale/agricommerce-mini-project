<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Check if logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

require 'db.php';
$pageTitle = "Buyer Marketplace";
include 'includes/header.php';
?>

<div class="container">
    <div class="flex-between mb-2">
        <h1>Marketplace</h1>
        <div class="font-w-600 text-primary-dark">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
    </div>

    <section>
        <p class="text-muted mb-lg">Browse fresh produce listed by verified farmers.</p>
        
        <?php
        $cat_query = "SELECT DISTINCT category FROM orders WHERE category IS NOT NULL AND category != ''";
        $cat_result = mysqli_query($conn, $cat_query);
        $categories = [];
        while ($cr = mysqli_fetch_assoc($cat_result)) {
            $categories[] = $cr['category'];
        }
        ?>
        <div class="product-toolbar card mb-lg flex-between p-4 mb-2">
            <input type="text" id="searchInput" class="form-control max-w-300" placeholder="Search crops...">
            <div style="display:flex; gap:1rem;">
                <select id="categoryFilter" class="form-control" style="min-width: 150px;">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="sortFilter" class="form-control" style="min-width: 150px;">
                    <option value="default">Sort By: Recommended</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="name_asc">Name: A to Z</option>
                </select>
            </div>
        </div>

        <div id="productGrid" class="card-grid">
            <?php
// Fetch products from database instead of JS array for real system
$result = mysqli_query($conn, "SELECT o.*, u.name as farmer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
if (mysqli_num_rows($result) > 0):
    while ($row = mysqli_fetch_assoc($result)):
?>
            <div class="card p-0 overflow-hidden product-card" data-category="<?php echo htmlspecialchars($row['category']); ?>" data-name="<?php echo htmlspecialchars(strtolower($row['crop_name'])); ?>" data-price="<?php echo $row['price']; ?>">
                <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=400&q=80" class="product-img">
                <div class="p-6">
                    <span class="badge-category"><?php echo htmlspecialchars($row['category']); ?></span>
                    <h3 class="mb-sm"><?php echo htmlspecialchars($row['crop_name']); ?></h3>
                    <p class="text-muted text-sm mb-sm">Farmer: <strong><?php echo htmlspecialchars($row['farmer_name']); ?></strong></p>
                    <div class="flex-between mt-sm">
                        <span class="font-w-700 text-primary-dark" style="font-size: 1.1rem;">Rs. <?php echo $row['price']; ?>/kg</span>
                        <button class="btn btn-primary btn-small add-to-cart-btn" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['crop_name']); ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
            <?php
    endwhile;
else: ?>
            <div class="card p-8 text-center" style="grid-column: 1 / -1;">
                <h3>No crops available right now.</h3>
                <p>Check back later for fresh produce!</p>
            </div>
            <?php
endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
