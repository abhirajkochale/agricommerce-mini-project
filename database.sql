-- ============================================================
--  AgroConnect — Full Database Setup Script
--  Run this file in phpMyAdmin (SQL tab) or via MySQL CLI
--  to set up the entire database from scratch on any device.
-- ============================================================

-- 1. Create & select the database
CREATE DATABASE IF NOT EXISTS agroconnect
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agroconnect;

-- ============================================================
-- 2. TABLE: users
--    Stores all accounts: admin / farmer / user (buyer)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       VARCHAR(20)   NOT NULL DEFAULT 'user',  -- 'admin' | 'farmer' | 'user'
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. TABLE: orders   (crop listings posted by farmers)
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    farmer_name  VARCHAR(100)   NOT NULL,
    email        VARCHAR(100)   NOT NULL,
    crop_name    VARCHAR(100)   NOT NULL,
    category     VARCHAR(50),                           -- 'Grains' | 'Vegetables' | 'Fruits'
    quantity     INT            NOT NULL,               -- available stock in kg
    price        DECIMAL(10,2)  NOT NULL,               -- price per kg (Rs)
    location     VARCHAR(100),
    user_id      INT,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. TABLE: cart
--    Temporary cart for buyer sessions
-- ============================================================
CREATE TABLE IF NOT EXISTS cart (
    id         INT       AUTO_INCREMENT PRIMARY KEY,
    user_id    INT       NOT NULL,
    product_id INT       NOT NULL,
    quantity   INT       DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. TABLE: checkout_orders
--    One record per placed order (payment header)
-- ============================================================
CREATE TABLE IF NOT EXISTS checkout_orders (
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    payment_id   VARCHAR(100)   NOT NULL,              -- dummy TXN ID generated at checkout
    total_amount DECIMAL(10,2)  NOT NULL,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. TABLE: order_items
--    Individual line items for each checkout_order
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id         INT            AUTO_INCREMENT PRIMARY KEY,
    order_id   INT            NOT NULL,               -- FK → checkout_orders.id
    farmer_id  INT            NOT NULL,               -- FK → users.id  (the seller)
    product_id INT            NOT NULL,               -- FK → orders.id (the listing)
    quantity   INT            NOT NULL,
    price      DECIMAL(10,2)  NOT NULL,               -- price per kg at time of purchase
    status     VARCHAR(50)    NOT NULL DEFAULT 'Pending', -- 'Pending' | 'Shipped' | 'Delivered'
    FOREIGN KEY (order_id)   REFERENCES checkout_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id)  REFERENCES users(id)          ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES orders(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO users (name, email, password, role) VALUES
  ('System Admin',
   'admin@agro.com',
   '$2y$10$CXoBVZHz4FZ3jDQXdMvgR2l1wRJ7ib40QIT7lLWPWfzE0sdqHZt7O',
   'admin'),
  ('Demo Farmer',
   'farmer@agro.com',
   '$2y$10$KsO9mYcZuj/ADkk1F5suu9O3V1F/wD9H7k5rI3XKWYnMvBxLqP4Ce',
   'farmer'),
  ('Demo Buyer',
   'buyer@agro.com',
   '$2y$10$6i1e4Ek7GD8eOmTLY.QHOIFgT7iB95.0Nx41V/wTDaFkE3r9xQ2Oq',
   'user')
ON DUPLICATE KEY UPDATE role = VALUES(role);

-- ============================================================
-- Done! All 5 tables created. Import this file in phpMyAdmin:
--   Database → Import → choose this file → Go
-- Or via CLI:
--   mysql -u root -p < database.sql
-- ============================================================