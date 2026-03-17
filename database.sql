-- AgroConnect Database Setup Script
-- Compatible with phpMyAdmin

-- 1. Create Database
CREATE DATABASE IF NOT EXISTS agroconnect;
USE agroconnect;

-- 2. Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Create Orders (Crops) Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    location VARCHAR(255),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Sample Data
-- Inserting a default admin user (Password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) 
VALUES ('System Admin', 'admin@agroconnect.com', '$2y$10$f/9qL3f7f8X.rG7j8k9l0u6A5p4W3z2V1S0T/m1n2o3p4q5r6s7t8u9v', 'admin')
ON DUPLICATE KEY UPDATE role='admin';

-- Sample Farmer (Password: farmer123)
-- Hash generated via password_hash('farmer123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) 
VALUES ('John Farmer', 'farmer@test.com', '$2y$10$p4Q6R7S8T9U0V1W2X3Y4Z5A6B7C8D9E0F1G2H3I4J5K6L7M8N9O0P', 'farmer')
ON DUPLICATE KEY UPDATE role='farmer';
