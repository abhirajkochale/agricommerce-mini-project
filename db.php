<?php
$host = "localhost";
$user = "root";
$password = ""; // Default XAMPP password is empty
$database = "agroconnect";

// Suppress warnings — we handle errors manually below
$conn = @mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    // Try connecting without a database (it may not exist yet)
    $conn = @mysqli_connect($host, $user, $password);
    if (!$conn) {
        die("Connection Failed: " . mysqli_connect_error());
    }
    die("<div style='font-family:sans-serif;max-width:500px;margin:4rem auto;padding:2rem;border:2px solid #dc3545;border-radius:8px;text-align:center;'>"
      . "<h2 style='color:#dc3545;'>⚠️ Database Missing</h2>"
      . "<p>The <strong>agroconnect</strong> database does not exist.</p>"
      . "<p><a href='fix_database.php' style='color:#2e7d32;font-weight:bold;'>Run Database Recovery →</a></p>"
      . "</div>");
}

/**
 * Check that all required tables exist.
 * Call this at the top of any page that queries the DB.
 */
function check_tables_exist($conn) {
    $required = ['users', 'orders', 'cart', 'checkout_orders', 'order_items'];
    $missing  = [];
    foreach ($required as $table) {
        $result = @mysqli_query($conn, "SELECT 1 FROM `$table` LIMIT 1");
        if (!$result) {
            $missing[] = $table;
        }
    }
    if (!empty($missing)) {
        die("<div style='font-family:sans-serif;max-width:500px;margin:4rem auto;padding:2rem;border:2px solid #dc3545;border-radius:8px;text-align:center;'>"
          . "<h2 style='color:#dc3545;'>⚠️ Missing Tables</h2>"
          . "<p>The following tables are missing: <strong>" . implode(', ', $missing) . "</strong></p>"
          . "<p><a href='fix_database.php' style='color:#2e7d32;font-weight:bold;'>Run Database Recovery →</a></p>"
          . "</div>");
    }
}
?>