-- Test Users Data for Waste Not Kitchen (WNK)
-- This file contains INSERT statements for test users of all types
-- All test users have password: "password"
-- Password hash (bcrypt): $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ============================================
-- ADMIN USERS
-- ============================================
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES 
('admin1@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'John', 'Admin', '100 Admin Blvd', 'Orlando', 'FL', '32816'),
('admin2@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Sarah', 'Manager', '200 Management Ave', 'Orlando', 'FL', '32817');

-- ============================================
-- RESTAURANT USERS
-- ============================================
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES 
('italianbistro@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurant', 'Mario', 'Rossi', '300 Restaurant Row', 'Orlando', 'FL', '32801'),
('sushihouse@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurant', 'Kenji', 'Tanaka', '400 Food Court Dr', 'Orlando', 'FL', '32802'),
('bbqjoint@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurant', 'Bob', 'Smith', '500 Grill Street', 'Orlando', 'FL', '32803'),
('mexicancantina@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurant', 'Carlos', 'Rodriguez', '600 Taco Lane', 'Orlando', 'FL', '32804');

-- Insert restaurant details
INSERT INTO Restaurants (restaurant_id, phone_number, restaurant_name, description, cuisine_type)
VALUES 
((SELECT user_id FROM Users WHERE email = 'italianbistro@wnk.com'), '407-555-0101', 'Mario\'s Italian Bistro', 'Authentic Italian cuisine with fresh ingredients', 'Italian'),
((SELECT user_id FROM Users WHERE email = 'sushihouse@wnk.com'), '407-555-0102', 'Tanaka Sushi House', 'Fresh sushi and Japanese cuisine', 'Japanese'),
((SELECT user_id FROM Users WHERE email = 'bbqjoint@wnk.com'), '407-555-0103', 'Bob\'s BBQ Joint', 'Slow-smoked meats and classic BBQ sides', 'American BBQ'),
((SELECT user_id FROM Users WHERE email = 'mexicancantina@wnk.com'), '407-555-0104', 'Carlos\' Mexican Cantina', 'Traditional Mexican dishes and margaritas', 'Mexican');

-- ============================================
-- CUSTOMER USERS
-- ============================================
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES 
('customer1@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Alice', 'Johnson', '700 Customer St', 'Orlando', 'FL', '32805'),
('customer2@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Bob', 'Williams', '800 Buyer Ave', 'Orlando', 'FL', '32806'),
('customer3@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Carol', 'Brown', '900 Shopper Blvd', 'Orlando', 'FL', '32807'),
('customer4@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'David', 'Davis', '1000 Purchase Rd', 'Orlando', 'FL', '32808'),
('customer5@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Emma', 'Miller', '1100 Order Lane', 'Orlando', 'FL', '32809');

-- Insert customer details
INSERT INTO Customers (customer_id, phone_number, credit_card_number, card_expiry, card_cvv, billing_address)
VALUES 
((SELECT user_id FROM Users WHERE email = 'customer1@wnk.com'), '407-555-0201', '4111111111111111', '12/25', '123', '700 Customer St, Orlando, FL 32805'),
((SELECT user_id FROM Users WHERE email = 'customer2@wnk.com'), '407-555-0202', '4222222222222222', '06/26', '456', '800 Buyer Ave, Orlando, FL 32806'),
((SELECT user_id FROM Users WHERE email = 'customer3@wnk.com'), '407-555-0203', '4333333333333333', '09/27', '789', '900 Shopper Blvd, Orlando, FL 32807'),
((SELECT user_id FROM Users WHERE email = 'customer4@wnk.com'), '407-555-0204', '4444444444444444', '03/28', '321', '1000 Purchase Rd, Orlando, FL 32808'),
((SELECT user_id FROM Users WHERE email = 'customer5@wnk.com'), '407-555-0205', '4555555555555555', '11/29', '654', '1100 Order Lane, Orlando, FL 32809');

-- ============================================
-- DONNER USERS
-- ============================================
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES 
('donner1@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donner', 'Frank', 'Generous', '1200 Donation Way', 'Orlando', 'FL', '32810'),
('donner2@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donner', 'Grace', 'Kindheart', '1300 Charity Dr', 'Orlando', 'FL', '32811'),
('donner3@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donner', 'Henry', 'Philanthropist', '1400 Giving St', 'Orlando', 'FL', '32812'),
('donner4@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donner', 'Iris', 'Helper', '1500 Support Ave', 'Orlando', 'FL', '32813');

-- Insert donner details
INSERT INTO Donners (donner_id, phone_number, credit_card_number, card_expiry, card_cvv, billing_address)
VALUES 
((SELECT user_id FROM Users WHERE email = 'donner1@wnk.com'), '407-555-0301', '5111111111111111', '12/25', '111', '1200 Donation Way, Orlando, FL 32810'),
((SELECT user_id FROM Users WHERE email = 'donner2@wnk.com'), '407-555-0302', '5222222222222222', '06/26', '222', '1300 Charity Dr, Orlando, FL 32811'),
((SELECT user_id FROM Users WHERE email = 'donner3@wnk.com'), '407-555-0303', '5333333333333333', '09/27', '333', '1400 Giving St, Orlando, FL 32812'),
((SELECT user_id FROM Users WHERE email = 'donner4@wnk.com'), '407-555-0304', '5444444444444444', '03/28', '444', '1500 Support Ave, Orlando, FL 32813');

-- ============================================
-- NEEDY USERS
-- ============================================
INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code)
VALUES 
('needy1@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'needy', 'Jack', 'Needs', '1600 Help St', 'Orlando', 'FL', '32814'),
('needy2@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'needy', 'Kelly', 'Struggles', '1700 Hope Ave', 'Orlando', 'FL', '32815'),
('needy3@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'needy', 'Larry', 'Hardship', '1800 Relief Rd', 'Orlando', 'FL', '32816'),
('needy4@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'needy', 'Mary', 'Assistance', '1900 Aid Blvd', 'Orlando', 'FL', '32817'),
('needy5@wnk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'needy', 'Nancy', 'Support', '2000 Care Lane', 'Orlando', 'FL', '32818');

-- Insert needy details (phone number is optional, verification status varies)
INSERT INTO Needy (needy_id, phone_number, verification_status, verification_date)
VALUES 
((SELECT user_id FROM Users WHERE email = 'needy1@wnk.com'), '407-555-0401', 'verified', '2025-01-15'),
((SELECT user_id FROM Users WHERE email = 'needy2@wnk.com'), '407-555-0402', 'verified', '2025-01-20'),
((SELECT user_id FROM Users WHERE email = 'needy3@wnk.com'), '407-555-0403', 'pending', NULL),
((SELECT user_id FROM Users WHERE email = 'needy4@wnk.com'), NULL, 'pending', NULL),
((SELECT user_id FROM Users WHERE email = 'needy5@wnk.com'), '407-555-0405', 'verified', '2025-02-01');

-- ============================================
-- SUMMARY
-- ============================================
-- Total test users created:
-- - 2 Admin users
-- - 4 Restaurant users
-- - 5 Customer users
-- - 4 Donner users
-- - 5 Needy users
-- Total: 20 test users
--
-- All users have password: "password"

