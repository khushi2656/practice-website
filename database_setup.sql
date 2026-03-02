-- Database Setup for User Authentication System
-- This SQL script creates the MySQL database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS user_auth_db;

-- Use the database
USE user_auth_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Display tables
SHOW TABLES;

-- Display users table structure
DESCRIBE users;
