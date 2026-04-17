<?php
$host = "localhost";
$user = "root";
$password = ""; // Default XAMPP password is empty
$database = "agroconnect";

// Error logging function - in a real app, this would write to a protected file
function log_system_error($msg)
{
    error_log("[" . date('Y-m-d H:i:s') . "] " . $msg . "\n", 3, __DIR__ . "/system_errors.log");
}


$conn = @mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    $error_msg = mysqli_connect_error();
    log_system_error("Connection Failed: " . $error_msg);

    // Check if it's just a missing database
    $temp_conn = @mysqli_connect($host, $user, $password);
    if ($temp_conn) {
        mysqli_close($temp_conn);
        die("<div style='font-family:sans-serif;max-width:500px;margin:4rem auto;padding:2rem;border:2px solid #dc3545;border-radius:8px;text-align:center;'>"
            . "<h2 style='color:#dc3545;'>⚠️ Database Missing</h2>"
            . "<p>The <strong>agroconnect</strong> database is not set up yet.</p>"
            . "<p><a href='fix_database.php' style='color:#2e7d32;font-weight:bold;'>Run Database Recovery →</a></p>"
            . "</div>");
    }

    die("<div style='font-family:sans-serif;max-width:500px;margin:4rem auto;padding:2rem;border:2px solid #dc3545;border-radius:8px;text-align:center;'>"
        . "<h2 style='color:#dc3545;'>⚠️ System Unavailable</h2>"
        . "<p>We are experiencing technical difficulties. Please try again later.</p>"
        . "</div>");
}

/**
 * Check that all required tables exist.
 * Call this at the top of any page that queries the DB.
 */
function check_tables_exist($conn)
{
    $required = ['users', 'crops', 'cart', 'checkout_orders', 'order_items'];
    $missing = [];
    foreach ($required as $table) {
        $result = @mysqli_query($conn, "SELECT 1 FROM `$table` LIMIT 1");
        if (!$result) {
            $missing[] = $table;
        }
    }
    if (!empty($missing)) {
        log_system_error("Missing Tables: " . implode(', ', $missing));
        die("<div style='font-family:sans-serif;max-width:500px;margin:4rem auto;padding:2rem;border:2px solid #dc3545;border-radius:8px;text-align:center;'>"
            . "<h2 style='color:#dc3545;'>⚠️ System Maintenance</h2>"
            . "<p>The database structure is incomplete.</p>"
            . "<p><a href='fix_database.php' style='color:#2e7d32;font-weight:bold;'>Complete Setup →</a></p>"
            . "</div>");
    }
}
?>