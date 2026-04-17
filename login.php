<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';
check_tables_exist($conn);

// CSRF Token generation for login form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$email = '';

// Feedback for unauthorized or timeout
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $errors['general'] = 'Please log in to access that page.';
    } elseif ($_GET['error'] === 'timeout') {
        $errors['general'] = 'Your session has expired. Please log in again.';
    }
}

// Auto-fill logic from cookie
if (isset($_COOKIE['user_email'])) {
    $email = $_COOKIE['user_email'];
}

$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    $user_token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $user_token)) {
        die("CSRF token validation failed.");
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $sql = "SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    // Session Fixation Protection
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    // Cookie: Remember Me (7 days)
                    if ($remember) {
                        setcookie("user_email", $email, time() + (86400 * 7), "/", "", false, true); // Added Secure & HttpOnly flags
                    } else {
                        if (isset($_COOKIE['user_email'])) {
                            setcookie("user_email", "", time() - 3600, "/");
                        }
                    }

                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } elseif ($user['role'] === 'farmer') {
                        header('Location: farmer_dashboard.php');
                    } else {
                        header('Location: user_dashboard.php');
                    }
                    exit;
                } else {
                    $errors['general'] = 'Invalid email or password.';
                }
            } else {
                $errors['general'] = 'Invalid email or password.';
            }
            mysqli_stmt_close($stmt);
        } else {
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
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                <?php if (!empty($errors['email'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['email']); ?></small>
                <?php endif; ?>
                <small class="error-message" id="emailError"></small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                    placeholder="Enter your password" required>
                <?php if (!empty($errors['password'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['password']); ?></small>
                <?php endif; ?>
                <small class="error-message" id="passwordError"></small>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="remember" name="remember" <?php echo isset($_COOKIE['user_email']) ? 'checked' : ''; ?>>
                <label for="remember" style="margin-bottom: 0;">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="loginSubmit">Login</button>
        </form>

        <p class="text-center mt-lg text-sm">
            Don't have an account? <a href="register.php" class="font-w-600">Register here</a>
        </p>

        <div class="mt-lg p-4 text-center"
            style="background: #f8f9fa; border-radius: var(--radius-sm); border: 1px dashed #ccc;">
            <p class="text-sm text-muted mb-sm"><strong>Demo Admin Access</strong></p>
            <p class="text-sm">Email: <strong>admin@agro.com</strong><br>Password: <strong>admin123</strong></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>