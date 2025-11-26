-- Waste Not Kitchen (WNK) Database Schema - IMPROVED VERSION
-- COP4710 Team Project
-- Date: November 4, 2025

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS Needy_Claim;
DROP TABLE IF EXISTS Donations;
DROP TABLE IF EXISTS Customer_Reservations;
DROP TABLE IF EXISTS Plates;
DROP TABLE IF EXISTS Needy;
DROP TABLE IF EXISTS Donners;
DROP TABLE IF EXISTS Customers;
DROP TABLE IF EXISTS Restaurants;
DROP TABLE IF EXISTS Users;

-- Users table (base table for all user types)
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'restaurant', 'customer', 'donner', 'needy') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Restaurants table
CREATE TABLE Restaurants (
    restaurant_id INT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    restaurant_name VARCHAR(255) NOT NULL,
    description TEXT,
    cuisine_type VARCHAR(100),
    FOREIGN KEY (restaurant_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Customers table
CREATE TABLE Customers (
    customer_id INT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    credit_card_number VARCHAR(19),
    card_expiry VARCHAR(7),
    card_cvv VARCHAR(4),
    billing_address VARCHAR(255),
    FOREIGN KEY (customer_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Donners table
CREATE TABLE Donners (
    donner_id INT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    credit_card_number VARCHAR(19),
    card_expiry VARCHAR(7),
    card_cvv VARCHAR(4),
    billing_address VARCHAR(255),
    FOREIGN KEY (donner_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Needy table
CREATE TABLE Needy (
    needy_id INT PRIMARY KEY,
    phone_number VARCHAR(20),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_date DATE,
    FOREIGN KEY (needy_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Plates table (surplus food items)
CREATE TABLE Plates (
    plate_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    plate_name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity_available INT NOT NULL,
    original_quantity INT NOT NULL,
    available_from DATETIME NOT NULL,
    available_until DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES Restaurants(restaurant_id) ON DELETE CASCADE,
    CHECK (quantity_available >= 0),
    CHECK (quantity_available <= original_quantity)
);

-- Customer Reservations table (for customer purchases only)
CREATE TABLE Customer_Reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    plate_id INT NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10, 2),
    status ENUM('pending', 'confirmed', 'picked_up', 'cancelled') DEFAULT 'pending',
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    picked_up_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL, 
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id) ON DELETE CASCADE,  
    FOREIGN KEY (plate_id) REFERENCES Plates(plate_id) ON DELETE CASCADE,
    CHECK (quantity > 0)
);

-- Donations table (donation pool from donners)
CREATE TABLE Donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donner_id INT NOT NULL,
    plate_id INT NOT NULL,
    quantity_available INT NOT NULL,
    original_quantity INT NOT NULL,
    total_amount DECIMAL(10, 2),
    donated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donner_id) REFERENCES Donners(donner_id) ON DELETE CASCADE,
    FOREIGN KEY (plate_id) REFERENCES Plates(plate_id) ON DELETE CASCADE,
    CHECK (quantity_available >= 0),
    CHECK (quantity_available <= original_quantity)
);

-- Needy Claim table (linking needy claims to donations)
CREATE TABLE Needy_Claim (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    needy_id INT NOT NULL,
    donation_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'confirmed', 'picked_up', 'cancelled') DEFAULT 'pending',
    claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    picked_up_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    FOREIGN KEY (needy_id) REFERENCES Needy(needy_id) ON DELETE CASCADE,
    FOREIGN KEY (donation_id) REFERENCES Donations(donation_id) ON DELETE CASCADE,
    CHECK (quantity > 0)
);

-- Create indexes for better query performance
CREATE INDEX idx_users_email ON Users(email);
CREATE INDEX idx_users_type ON Users(user_type);
CREATE INDEX idx_plates_restaurant ON Plates(restaurant_id);
CREATE INDEX idx_plates_active ON Plates(is_active);
CREATE INDEX idx_plates_availability ON Plates(available_from, available_until);
CREATE INDEX idx_customer_reservations_customer ON Customer_Reservations(customer_id);
CREATE INDEX idx_customer_reservations_plate ON Customer_Reservations(plate_id);
CREATE INDEX idx_customer_reservations_status ON Customer_Reservations(status);
CREATE INDEX idx_donations_donner ON Donations(donner_id);
CREATE INDEX idx_donations_plate ON Donations(plate_id);
CREATE INDEX idx_needy_claim_needy ON Needy_Claim(needy_id);
CREATE INDEX idx_needy_claim_donation ON Needy_Claim(donation_id);
CREATE INDEX idx_needy_claim_status ON Needy_Claim(status);

-- Insert a default admin user (password: admin123 - should be hashed in production)
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES ('admin@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', '123 Admin St', 'Orlando', 'FL', '32816');
