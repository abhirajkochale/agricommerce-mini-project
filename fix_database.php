<?php
/**
 * AgroConnect — Emergency Database Recovery Script
 * 
 * Run this once via browser: http://localhost/agroconnect/fix_database.php
 * It will recreate all tables and seed the demo accounts.
 * DELETE THIS FILE after use.
 */

$host     = "localhost";
$user     = "root";
$password = "";

// Connect WITHOUT selecting a database first
$conn = mysqli_connect($host, $user, $password);
if (!$conn) {
    die("❌ MySQL connection failed: " . mysqli_connect_error());
}

echo "<pre style='font-family:monospace;font-size:14px;line-height:1.8;padding:2rem;'>";
echo "========================================\n";
echo " AgroConnect — Database Recovery\n";
echo "========================================\n\n";

// 1. Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS agroconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "✅ Database 'agroconnect' ensured.\n";
} else {
    die("❌ Failed to create database: " . mysqli_error($conn));
}

mysqli_select_db($conn, "agroconnect");

// 2. Disable FK checks so we can drop/recreate in any order
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
echo "🔧 Foreign key checks disabled.\n\n";

// 3. Drop all existing (possibly corrupted) tables
$tables = ['order_items', 'checkout_orders', 'cart', 'orders', 'users'];
foreach ($tables as $table) {
    if (mysqli_query($conn, "DROP TABLE IF EXISTS `$table`")) {
        echo "🗑️  Dropped table '$table' (if it existed).\n";
    } else {
        echo "⚠️  Could not drop '$table': " . mysqli_error($conn) . "\n";
    }
}

echo "\n--- Recreating tables ---\n\n";

// 4. Recreate all tables

// USERS
$sql_users = "CREATE TABLE users (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       VARCHAR(20)   NOT NULL DEFAULT 'user',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_users)) {
    echo "✅ Table 'users' created.\n";
} else {
    echo "❌ Failed 'users': " . mysqli_error($conn) . "\n";
}

// ORDERS (crop listings)
$sql_orders = "CREATE TABLE orders (
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    farmer_name  VARCHAR(100)   NOT NULL,
    email        VARCHAR(100)   NOT NULL,
    crop_name    VARCHAR(100)   NOT NULL,
    category     VARCHAR(50),
    quantity     INT            NOT NULL,
    price        DECIMAL(10,2)  NOT NULL,
    location     VARCHAR(100),
    user_id      INT,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_orders)) {
    echo "✅ Table 'orders' created.\n";
} else {
    echo "❌ Failed 'orders': " . mysqli_error($conn) . "\n";
}

// CART
$sql_cart = "CREATE TABLE cart (
    id         INT       AUTO_INCREMENT PRIMARY KEY,
    user_id    INT       NOT NULL,
    product_id INT       NOT NULL,
    quantity   INT       DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_cart)) {
    echo "✅ Table 'cart' created.\n";
} else {
    echo "❌ Failed 'cart': " . mysqli_error($conn) . "\n";
}

// CHECKOUT_ORDERS
$sql_checkout = "CREATE TABLE checkout_orders (
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    payment_id   VARCHAR(100)   NOT NULL,
    total_amount DECIMAL(10,2)  NOT NULL,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_checkout)) {
    echo "✅ Table 'checkout_orders' created.\n";
} else {
    echo "❌ Failed 'checkout_orders': " . mysqli_error($conn) . "\n";
}

// ORDER_ITEMS
$sql_items = "CREATE TABLE order_items (
    id         INT            AUTO_INCREMENT PRIMARY KEY,
    order_id   INT            NOT NULL,
    farmer_id  INT            NOT NULL,
    product_id INT            NOT NULL,
    quantity   INT            NOT NULL,
    price      DECIMAL(10,2)  NOT NULL,
    status     VARCHAR(50)    NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (order_id)   REFERENCES checkout_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id)  REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES orders(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_items)) {
    echo "✅ Table 'order_items' created.\n";
} else {
    echo "❌ Failed 'order_items': " . mysqli_error($conn) . "\n";
}

echo "\n--- Seeding demo accounts ---\n\n";

// 5. Seed demo users (passwords: admin123, farmer123, buyer123)
$seeds = [
    ['System Admin',  'admin@agro.com',  'admin123',  'admin'],
    ['Demo Farmer',   'farmer@agro.com', 'farmer123', 'farmer'],
    ['Demo Buyer',    'buyer@agro.com',  'buyer123',  'user'],
];

$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
foreach ($seeds as $s) {
    $hash = password_hash($s[2], PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "ssss", $s[0], $s[1], $hash, $s[3]);
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ User '{$s[0]}' ({$s[1]}) created — password: {$s[2]}\n";
    } else {
        echo "⚠️  User '{$s[0]}': " . mysqli_error($conn) . "\n";
    }
}
mysqli_stmt_close($stmt);

// 6. Seed a few sample crop listings for the demo farmer
$farmer_id = 2; // Demo Farmer's ID
$crops = [
    ['Demo Farmer', 'farmer@agro.com', 'Organic Wheat',  'Grains',      500, 35.00, 'Pune, Maharashtra'],
    ['Demo Farmer', 'farmer@agro.com', 'Fresh Tomatoes',  'Vegetables',  200, 25.00, 'Nashik, Maharashtra'],
    ['Demo Farmer', 'farmer@agro.com', 'Alphonso Mangoes','Fruits',      100, 120.00,'Ratnagiri, Maharashtra'],
];

echo "\n--- Seeding sample crop listings ---\n\n";
$stmt = mysqli_prepare($conn, "INSERT INTO orders (farmer_name, email, crop_name, category, quantity, price, location, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($crops as $c) {
    mysqli_stmt_bind_param($stmt, "ssssiids", $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $farmer_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Crop '{$c[2]}' listed.\n";
    } else {
        echo "⚠️  Crop '{$c[2]}': " . mysqli_error($conn) . "\n";
    }
}
mysqli_stmt_close($stmt);

// 7. Re-enable FK checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
echo "\n🔧 Foreign key checks re-enabled.\n";

// 8. Verify
echo "\n--- Verification ---\n\n";
$result = mysqli_query($conn, "SHOW TABLES FROM agroconnect");
while ($row = mysqli_fetch_row($result)) {
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `{$row[0]}`");
    $count = mysqli_fetch_assoc($count_result)['cnt'];
    echo "📋 {$row[0]} — {$count} row(s)\n";
}

echo "\n========================================\n";
echo " ✅ DATABASE RECOVERY COMPLETE!\n";
echo "========================================\n";
echo "\n🔗 <a href='login.php'>Go to Login Page</a>\n";
echo "\n⚠️  DELETE this file (fix_database.php) after use!\n";
echo "</pre>";

mysqli_close($conn);
?>
