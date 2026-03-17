<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, show the landing page (current index.php logic)
if (!isset($_SESSION['user_id'])) {
    $pageTitle = "Home";
    include 'includes/header.php';
    ?>
    <div class="container">
        <section class="hero">
            <h1>Welcome to AgroConnect</h1>
            <p>
                AgroConnect is a modern agricultural marketplace portal connecting farmers directly with buyers.
                Explore fresh crops, compare prices, and manage your orders all in one place.
            </p>
            <div class="mt-2 flex-gap-2">
                <a href="login.php" class="btn btn-primary">Login</a>
                <a href="register.php" class="btn btn-outline">Register Now</a>
            </div>
        </section>

        <section class="highlights">
            <h2 class="text-center">Start Your Journey</h2>
            <div class="card-grid">
                <div class="card">
                    <h3>For Farmers</h3>
                    <p>Register as a seller to list your crops and reach buyers directly without middlemen.</p>
                </div>
                <div class="card">
                    <h3>For Buyers</h3>
                    <p>Browse a wide variety of fresh, local produce at transparent prices.</p>
                </div>
                <div class="card">
                    <h3>For Everyone</h3>
                    <p>Experience a transparent, sustainable, and fair agricultural marketplace.</p>
                </div>
            </div>
        </section>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

// If logged in, redirect based on role
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    header('Location: admin_dashboard.php');
} elseif ($role === 'farmer') {
    header('Location: farmer_dashboard.php');
} else {
    header('Location: user_dashboard.php');
}
exit;
?>
