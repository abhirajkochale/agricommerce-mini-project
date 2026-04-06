-- ============================================================
-- SQL FOR MYSQL/MARIADB (XAMPP Default)
-- ============================================================

-- 1. Create Users Table (MySQL Syntax)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL, 
    role       VARCHAR(20)   NOT NULL DEFAULT 'user',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Add password column (if table already exists)
-- ALTER TABLE users ADD COLUMN password VARCHAR(255);

-- 3. Seed Data (MySQL Syntax)
-- Password for all is 'password123'
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
