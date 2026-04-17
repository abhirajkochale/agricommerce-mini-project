<?php
require 'auth_check.php';
require 'db.php';
check_tables_exist($conn);

$pageTitle = "Buyer Marketplace";
include 'includes/header.php';

// Global alerts based on URL
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container">
    <!-- Define CSRF token for AJAX script.js -->
    <script>const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token']; ?>";</script>

    <div class="flex-between mb-2">
        <h1>Marketplace</h1>
        <div class="font-w-600 text-primary-dark">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
    </div>

    <?php if ($success === 'order_placed'): ?>
        <div class="alert alert-success">Order placed successfully! Check your orders for updates.</div>
    <?php endif; ?>
    <?php if ($success === 'feedback_submitted'): ?>
        <div class="alert alert-success">Thank you for your feedback! It helps us improve.</div>
    <?php endif; ?>
    <?php if ($error === 'not_found'): ?>
        <div class="alert alert-danger">The item you requested could not be found.</div>
    <?php endif; ?>

    <section>
        <p class="text-muted mb-lg">Browse fresh produce listed by verified farmers.</p>
        
        <?php
        $cat_query = "SELECT DISTINCT category FROM crops WHERE category IS NOT NULL AND category != ''";
        $cat_stmt = mysqli_prepare($conn, $cat_query);
        $categories = [];
        if ($cat_stmt) {
            mysqli_stmt_execute($cat_stmt);
            $cat_result = mysqli_stmt_get_result($cat_stmt);
            while ($cr = mysqli_fetch_assoc($cat_result)) {
                $categories[] = $cr['category'];
            }
            mysqli_stmt_close($cat_stmt);
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
            // Fetch products from database
            $sql = "SELECT o.*, u.name as farmer_name FROM crops o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC";
            $p_stmt = mysqli_prepare($conn, $sql);
            
            if ($p_stmt) {
                mysqli_stmt_execute($p_stmt);
                $result = mysqli_stmt_get_result($p_stmt);
                
                if ($result && mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <div class="card p-0 overflow-hidden product-card" 
                         data-category="<?php echo htmlspecialchars($row['category']); ?>" 
                         data-name="<?php echo htmlspecialchars(strtolower($row['crop_name'])); ?>" 
                         data-price="<?php echo (float)$row['price']; ?>">
                        <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=400&q=80" 
                             class="product-img" alt="<?php echo htmlspecialchars($row['crop_name']); ?>">
                        <div class="p-6">
                            <span class="badge-category"><?php echo htmlspecialchars($row['category']); ?></span>
                            <h3 class="mb-sm"><?php echo htmlspecialchars($row['crop_name']); ?></h3>
                            <p class="text-muted text-sm mb-sm">Farmer: <strong><?php echo htmlspecialchars($row['farmer_name']); ?></strong></p>
                            <p class="text-muted text-sm mb-lg">Location: <strong><?php echo htmlspecialchars($row['location']); ?></strong></p>
                            <div class="flex-between mt-sm">
                                <span class="font-w-700 text-primary-dark" style="font-size: 1.1rem;">Rs. <?php echo number_format($row['price'], 2); ?>/kg</span>
                                <button class="btn btn-primary btn-small add-to-cart-btn" 
                                        data-id="<?php echo $row['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($row['crop_name']); ?>">Add to Cart</button>
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
                <?php endif; 
                mysqli_stmt_close($p_stmt);
            } ?>
        </div>
    </section>

    <!-- Permanent Feedback Section (All time there) -->
    <hr style="opacity: 0.1; margin: 4rem 0;">
    <section class="feedback-section card mb-lg">
        <h2 class="text-center">Share Your Feedback</h2>
        <p class="text-muted text-center mb-lg">Have suggestions? We'd love to hear from you anytime.</p>
        
        <form action="submit_feedback.php" method="POST" id="staticFeedbackForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="star-rating">
                <input type="radio" id="s_star5" name="rating" value="5">
                <label for="s_star5" title="5 stars">&#9733;</label>
                <input type="radio" id="s_star4" name="rating" value="4">
                <label for="s_star4" title="4 stars">&#9733;</label>
                <input type="radio" id="s_star3" name="rating" value="3">
                <label for="s_star3" title="3 stars">&#9733;</label>
                <input type="radio" id="s_star2" name="rating" value="2">
                <label for="s_star2" title="2 stars">&#9733;</label>
                <input type="radio" id="s_star1" name="rating" value="1">
                <label for="s_star1" title="1 star">&#9733;</label>
            </div>

            <div class="form-group">
                <label for="staticMessage">Your Message <span style="color:#aaa; font-weight:400;">(optional)</span></label>
                <textarea id="staticMessage" name="message" class="form-control" rows="4" placeholder="How can we improve?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-lg" id="staticFeedbackSubmit">Submit Feedback</button>
        </form>
    </section>

    <!-- Post-Order Feedback Modal (Popup) -->
    <div id="feedbackModal" class="modal-backdrop">
        <div class="modal-content">
            <button class="modal-close" onclick="closeFeedbackModal()">&times;</button>
            <h2 class="text-center">Order Successful! 🎉</h2>
            <p class="text-muted text-center mb-lg">How was your ordering experience? Your feedback helps us grow.</p>
            
            <form action="submit_feedback.php" method="POST" id="modalFeedbackForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="star-rating">
                    <input type="radio" id="m_star5" name="rating" value="5" required>
                    <label for="m_star5" title="5 stars">&#9733;</label>
                    <input type="radio" id="m_star4" name="rating" value="4">
                    <label for="m_star4" title="4 stars">&#9733;</label>
                    <input type="radio" id="m_star3" name="rating" value="3">
                    <label for="m_star3" title="3 stars">&#9733;</label>
                    <input type="radio" id="m_star2" name="rating" value="2">
                    <label for="m_star2" title="2 stars">&#9733;</label>
                    <input type="radio" id="m_star1" name="rating" value="1">
                    <label for="m_star1" title="1 star">&#9733;</label>
                </div>

                <div class="form-group">
                    <label for="modalMessage">Quick Feedback</label>
                    <textarea id="modalMessage" name="message" class="form-control" rows="3" placeholder="Tell us about your experience..." required minlength="5"></textarea>
                    <small class="error-message"></small>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-lg" id="modalFeedbackSubmit">Send Feedback</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>