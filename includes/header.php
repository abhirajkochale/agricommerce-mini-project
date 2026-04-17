<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$activePage = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - AgroConnect" : "AgroConnect - AgriCommerce Portal"; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="<?php echo isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark' ? 'dark-theme' : ''; ?>">
    <header class="main-header">
        <div class="logo-container">
            <a href="index.php" class="logo">AgroConnect</a>
        </div>
        <nav class="main-nav">
            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                <a href="index.php" class="<?php echo($activePage == 'index') ? 'active' : ''; ?>">Home</a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="<?php echo($activePage == 'admin_dashboard') ? 'active' : ''; ?>">Admin Panel</a>
                <?php
    elseif ($_SESSION['role'] === 'farmer'): ?>
                    <a href="farmer_dashboard.php" class="<?php echo($activePage == 'farmer_dashboard') ? 'active' : ''; ?>">My Listings</a>
                <?php
    else: ?>
                    <a href="user_dashboard.php" class="<?php echo($activePage == 'user_dashboard') ? 'active' : ''; ?>">Marketplace</a>
                    <a href="my_orders.php" class="<?php echo($activePage == 'my_orders') ? 'active' : ''; ?>">My Orders</a>
                <?php
    endif; ?>
                
                <div class="user-menu">
                    <span class="welcome-user">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            <?php
else: ?>
                <a href="login.php" class="<?php echo($activePage == 'login') ? 'active' : ''; ?>">Login</a>
                <a href="register.php" class="register-nav-btn <?php echo($activePage == 'register') ? 'active' : ''; ?>">Register</a>
            <?php
endif; ?>
        </nav>
        <div class="header-actions">
            <button id="themeToggle" class="theme-toggle" title="Toggle Dark/Light Mode">
                <span class="icon">🌙</span>
            </button>
            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] === 'user'): ?>
            <a href="cart.php" class="cart-info" title="View Cart" style="text-decoration:none; color:inherit;">
                🛒 <span id="cartCount">0</span>
            </a>
            <?php endif; ?>
        </div>
    </header>
