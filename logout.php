<?php
/**
 * logout.php - Secure Session Termination
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Clear session data from memory
$_SESSION = array();

// 2. Destroy the session cookie on the client
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Keep 'user_email' cookie if you want "Remember Me" to persist email ONLY
// If you want total wipe, uncomment this:
// setcookie("user_email", "", time() - 3600, "/");

// 4. Destroy the session on the server
session_destroy();

// 5. Security headers to prevent back-button access to authenticated content
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Old date in the past

// 6. Redirect to login with feedback
header("Location: login.php?logged_out=1");
exit;
?>