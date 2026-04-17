<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';
check_tables_exist($conn);

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$name = '';
$email = '';
$role = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    $user_token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $user_token)) {
        die("CSRF token validation failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Server-side Validation (consistent with JS)
    if (strlen($name) < 3) {
        $errors['name'] = 'Name must be at least 3 characters.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Explicit Duplicate Email Check
        $check_sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $errors['email'] = 'Email already exists. Please login instead.';
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: login.php?registered=1');
                    exit;
                } else {
                    $errors['general'] = 'Database error. Please try again.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    }
}

$pageTitle = "Create Account";
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center">Join AgroConnect</h2>
        <p class="text-muted text-center mb-lg">Start managing your agricultural business today.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?php echo htmlspecialchars($name); ?>"
                       placeholder="Enter your full name" required>
                <?php if (!empty($errors['name'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['name']); ?></small>
                <?php endif; ?>
                <small class="error-message" id="nameError"></small>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your email" required>
                <?php if (!empty($errors['email'])): ?>
                    <small class="error-message"><?php echo htmlspecialchars($errors['email']); ?></small>
                <?php endif; ?>
                <small class="error-message" id="emailError"></small>
            </div>

            <div class="form-group">
                <label for="role">Register As</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User (Buyer)</option>
                    <option value="farmer" <?php echo $role === 'farmer' ? 'selected' : ''; ?>>Farmer (Seller)</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Min 7 chars, A-Z, 123, &#33;" required>
                    <?php if (!empty($errors['password'])): ?>
                        <small class="error-message"><?php echo htmlspecialchars($errors['password']); ?></small>
                    <?php endif; ?>
                    <small class="error-message" id="passwordError"></small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                           placeholder="Re-enter" required>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <small class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></small>
                    <?php endif; ?>
                    <small class="error-message" id="confirmPasswordError"></small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="registerSubmit">Create Account</button>
        </form>

        <p class="text-center mt-lg text-sm">
            Already have an account? <a href="login.php" class="font-w-600">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
