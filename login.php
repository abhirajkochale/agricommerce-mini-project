<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

$errors = [];
$email = '';
// Create users table if it doesn't exist (safety check)
$createSql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
mysqli_query($conn, $createSql);

// Add role column if it doesn't exist
mysqli_query($conn, "ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user'");

// Look up user by email
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $user_id, $user_name, $hashed_password, $user_role);

            if (mysqli_stmt_fetch($stmt)) {
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['role'] = $user_role;
                    mysqli_stmt_close($stmt);

                    if ($user_role === 'admin') {
                        header('Location: admin_dashboard.php');
                    }
                    elseif ($user_role === 'farmer') {
                        header('Location: farmer_dashboard.php');
                    }
                    else {
                        header('Location: user_dashboard.php');
                    }
                    exit;
                }
                else {
                    $errors['general'] = 'Invalid email or password.';
                }
            }
            else {
                $errors['general'] = 'Invalid email or password.';
            }
            mysqli_stmt_close($stmt);
        }
        else {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle = "Login";
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center">Welcome Back</h2>
        <p class="text-muted text-center mb-lg">Login to manage your agricultural portal.</p>

        <?php if ($registered): ?>
            <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php
endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php
endif; ?>

        <form method="POST" action="login.php" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your email" required>
                <?php if (!empty($errors['email'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['email']); ?></small>
                <?php
endif; ?>
                <small class="error-message" id="emailError"></small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
                <?php if (!empty($errors['password'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['password']); ?></small>
                <?php
endif; ?>
                <small class="error-message" id="passwordError"></small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="text-center mt-lg text-sm">
            Don't have an account? <a href="register.php" class="font-w-600">Register here</a>
        </p>
        
        <div class="mt-lg p-4 text-center" style="background: #f8f9fa; border-radius: var(--radius-sm); border: 1px dashed #ccc;">
            <p class="text-sm text-muted mb-sm"><strong>Demo Admin Access</strong></p>
            <p class="text-sm">Email: <strong>admin@agro.com</strong><br>Password: <strong>admin123</strong></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
