    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <a href="index.php" class="logo" style="color: white; margin-bottom: 1.5rem; display: block;">AgroConnect</a>
                <p>Empowering local farmers with modern technology to build a more sustainable and transparent food supply chain for everyone.</p>
                <div class="social-links" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <span style="font-size: 1.5rem; opacity: 0.6; cursor: pointer;">🌐</span>
                    <span style="font-size: 1.5rem; opacity: 0.6; cursor: pointer;">📸</span>
                    <span style="font-size: 1.5rem; opacity: 0.6; cursor: pointer;">🐦</span>
                </div>
            </div>
            <div class="footer-nav">
                <h4>Marketplace</h4>
                <a href="user_dashboard.php">Browse All Crops</a>
                <a href="index.php#categories">Featured Grains</a>
                <a href="index.php#categories">Fresh Fruits</a>
                <a href="index.php#categories">Vegetables</a>
            </div>
            <div class="footer-nav">
                <h4>Quick Links</h4>
                <a href="index.php">About Us</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_orders.php">My Orders</a>
                    <a href="cart.php">Basket</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Join as Farmer</a>
                <?php endif; ?>
            </div>
            <div class="footer-nav">
                <h4>Support</h4>
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
                <p style="font-size: 0.85rem; margin-top: 1rem; opacity: 0.7; color: var(--primary-light);">support@agroconnect.farm</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> AgroConnect. All rights reserved. Designed for a greener tomorrow.</p>
        </div>
    </footer>
    
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
