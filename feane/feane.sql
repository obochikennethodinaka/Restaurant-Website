-- ==========================================
-- DATABASE SETUP SQL (run this in phpMyAdmin)
-- Create database and tables
-- ==========================================

CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- Users table (with role column for admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    image VARCHAR(255) DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (food items)
CREATE TABLE IF NOT EXISTS product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    header VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    footer VARCHAR(255),
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(50) DEFAULT 'burger',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
-- Run this after table creation, then change password
INSERT INTO users (firstName, lastName, email, phone, password, role) 
VALUES ('Admin', 'User', 'admin@feane.com', '1234567890', '$2y$10$YourHashedPasswordHere', 'admin');

-- To create admin password hash, use password_hash('admin123', PASSWORD_DEFAULT) in PHP
-- Or run this after registration and manually set role to 'admin'

-- Sample products for testing
INSERT INTO product (image, header, title, content, footer, price, category) VALUES
('f1.png', 'Classic Beef Burger', 'Juicy & Fresh', '100% beef patty with fresh lettuce and special sauce.', 'Served with fries', 4500.00, 'burger'),
('f2.png', 'Margherita Pizza', 'Cheesy Delight', 'Fresh mozzarella, tomato sauce, and basil.', 'Size: Large', 6500.00, 'pizza'),
('f3.png', 'Creamy Alfredo Pasta', 'Rich & Creamy', 'Fettuccine in creamy Alfredo sauce with parmesan.', 'Add chicken +₦1500', 5500.00, 'pasta');