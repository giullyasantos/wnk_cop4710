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
-- PLATES (Surplus Food Items)
-- ============================================
-- Insert plates from various restaurants with different availability windows
INSERT INTO Plates (restaurant_id, plate_name, description, price, quantity_available, original_quantity, available_from, available_until, is_active)
VALUES 
-- Plates from Mario's Italian Bistro
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Mario\'s Italian Bistro'), 
 'Surplus Lasagna', 'Fresh lasagna with meat sauce, made this morning', 8.99, 5, 10, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Mario\'s Italian Bistro'), 
 'Leftover Pasta Carbonara', 'Creamy pasta carbonara, still warm', 7.50, 3, 8, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Mario\'s Italian Bistro'), 
 'Extra Pizza Margherita', 'Large pizza, half eaten but fresh', 6.99, 0, 4, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', FALSE),

-- Plates from Tanaka Sushi House
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Tanaka Sushi House'), 
 'Surplus Sushi Platter', 'Assorted sushi rolls, made fresh today', 12.99, 8, 15, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Tanaka Sushi House'), 
 'Extra Tempura Set', 'Vegetable and shrimp tempura', 9.99, 4, 6, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Tanaka Sushi House'), 
 'Leftover Teriyaki Bowl', 'Chicken teriyaki with rice', 8.50, 0, 5, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', FALSE),

-- Plates from Bob's BBQ Joint
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Bob\'s BBQ Joint'), 
 'Surplus Pulled Pork', 'Slow-smoked pulled pork, 2 lbs', 10.99, 6, 10, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Bob\'s BBQ Joint'), 
 'Extra BBQ Ribs', 'Full rack of ribs with sides', 15.99, 2, 5, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Bob\'s BBQ Joint'), 
 'Leftover Brisket', 'Texas-style brisket, 1.5 lbs', 12.50, 0, 3, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', FALSE),

-- Plates from Carlos' Mexican Cantina
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Carlos\' Mexican Cantina'), 
 'Surplus Taco Platter', 'Assorted tacos (beef, chicken, fish)', 9.99, 7, 12, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Carlos\' Mexican Cantina'), 
 'Extra Burrito Bowl', 'Large burrito bowl with all toppings', 8.99, 5, 8, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', TRUE),
((SELECT restaurant_id FROM Restaurants WHERE restaurant_name = 'Carlos\' Mexican Cantina'), 
 'Leftover Quesadillas', 'Cheese and chicken quesadillas', 7.50, 0, 6, 
 '2025-12-03 09:00:00', '2025-12-04 09:00:00', FALSE);

-- ============================================
-- CUSTOMER RESERVATIONS
-- ============================================
-- Customers making reservations for plates
INSERT INTO Customer_Reservations (customer_id, plate_id, quantity, total_amount, status, reserved_at, confirmed_at, picked_up_at, cancelled_at)
VALUES 
-- Customer 1 reservations
((SELECT user_id FROM Users WHERE email = 'customer1@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Lasagna' LIMIT 1), 2, 17.98, 'confirmed',
 '2025-11-15 10:30:00', '2025-11-15 10:35:00', '2025-11-15 14:00:00', NULL),
((SELECT user_id FROM Users WHERE email = 'customer1@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Sushi Platter' LIMIT 1), 1, 12.99, 'picked_up',
 '2025-11-15 11:15:00', '2025-11-15 11:20:00', '2025-11-15 15:30:00', NULL),

-- Customer 2 reservations
((SELECT user_id FROM Users WHERE email = 'customer2@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Pulled Pork' LIMIT 1), 1, 10.99, 'confirmed',
 '2025-11-15 14:30:00', '2025-11-15 14:35:00', NULL, NULL),
((SELECT user_id FROM Users WHERE email = 'customer2@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Taco Platter' LIMIT 1), 2, 19.98, 'pending',
 '2025-11-15 16:15:00', NULL, NULL, NULL),

-- Customer 3 reservations
((SELECT user_id FROM Users WHERE email = 'customer3@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Leftover Pasta Carbonara' LIMIT 1), 1, 7.50, 'pending',
 '2025-11-16 11:30:00', NULL, NULL, NULL),
((SELECT user_id FROM Users WHERE email = 'customer3@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Tempura Set' LIMIT 1), 1, 9.99, 'cancelled',
 '2025-11-16 12:15:00', NULL, NULL, '2025-11-16 12:45:00'),

-- Customer 4 reservations
((SELECT user_id FROM Users WHERE email = 'customer4@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra BBQ Ribs' LIMIT 1), 1, 15.99, 'pending',
 '2025-11-16 15:30:00', NULL, NULL, NULL),

-- Customer 5 reservations
((SELECT user_id FROM Users WHERE email = 'customer5@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Burrito Bowl' LIMIT 1), 2, 17.98, 'confirmed',
 '2025-11-16 17:30:00', '2025-11-16 17:35:00', NULL, NULL);

-- ============================================
-- DONATIONS (Donners buying plates for needy)
-- ============================================
-- Donners purchasing plates to donate to needy
INSERT INTO Donations (donner_id, plate_id, quantity_available, original_quantity, total_amount, donated_at)
VALUES 
-- Donner 1 donations
((SELECT user_id FROM Users WHERE email = 'donner1@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Lasagna' LIMIT 1), 2, 3, 26.97,
 '2025-11-15 09:00:00'),
((SELECT user_id FROM Users WHERE email = 'donner1@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Sushi Platter' LIMIT 1), 3, 5, 64.95,
 '2025-11-15 10:00:00'),

-- Donner 2 donations
((SELECT user_id FROM Users WHERE email = 'donner2@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Pulled Pork' LIMIT 1), 2, 4, 43.96,
 '2025-11-15 13:00:00'),
((SELECT user_id FROM Users WHERE email = 'donner2@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Taco Platter' LIMIT 1), 4, 5, 49.95,
 '2025-11-15 15:00:00'),

-- Donner 3 donations
((SELECT user_id FROM Users WHERE email = 'donner3@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Tempura Set' LIMIT 1), 1, 2, 19.98,
 '2025-11-16 11:00:00'),
((SELECT user_id FROM Users WHERE email = 'donner3@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra BBQ Ribs' LIMIT 1), 1, 1, 15.99,
 '2025-11-16 14:00:00'),

-- Donner 4 donations
((SELECT user_id FROM Users WHERE email = 'donner4@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Burrito Bowl' LIMIT 1), 2, 3, 26.97,
 '2025-11-16 16:00:00'),
((SELECT user_id FROM Users WHERE email = 'donner4@wnk.com'),
 (SELECT plate_id FROM Plates WHERE plate_name = 'Leftover Pasta Carbonara' LIMIT 1), 1, 2, 15.00,
 '2025-11-16 11:30:00');

-- ============================================
-- NEEDY CLAIMS (Needy claiming donated plates)
-- ============================================
-- Needy users claiming plates from donations
INSERT INTO Needy_Claim (needy_id, donation_id, quantity, status, claimed_at, confirmed_at, picked_up_at)
VALUES 
-- Needy 1 claims
((SELECT user_id FROM Users WHERE email = 'needy1@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner1@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Lasagna' LIMIT 1) LIMIT 1),
 1, 'picked_up', '2025-11-15 09:30:00', '2025-11-15 09:35:00', '2025-11-15 13:00:00'),
((SELECT user_id FROM Users WHERE email = 'needy1@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner1@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Sushi Platter' LIMIT 1) LIMIT 1),
 1, 'confirmed', '2025-11-15 10:30:00', '2025-11-15 10:35:00', NULL),

-- Needy 2 claims
((SELECT user_id FROM Users WHERE email = 'needy2@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner2@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Pulled Pork' LIMIT 1) LIMIT 1),
 1, 'confirmed', '2025-11-15 13:30:00', '2025-11-15 13:35:00', NULL),
((SELECT user_id FROM Users WHERE email = 'needy2@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner2@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Taco Platter' LIMIT 1) LIMIT 1),
 1, 'picked_up', '2025-11-15 15:30:00', '2025-11-15 15:35:00', '2025-11-15 18:00:00'),

-- Needy 3 claims
((SELECT user_id FROM Users WHERE email = 'needy3@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner3@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Tempura Set' LIMIT 1) LIMIT 1),
 1, 'pending', '2025-11-16 11:30:00', NULL, NULL),

-- Needy 4 claims
((SELECT user_id FROM Users WHERE email = 'needy4@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner4@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Extra Burrito Bowl' LIMIT 1) LIMIT 1),
 1, 'pending', '2025-11-16 16:30:00', NULL, NULL),

-- Needy 5 claims
((SELECT user_id FROM Users WHERE email = 'needy5@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner1@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Surplus Lasagna' LIMIT 1) LIMIT 1),
 1, 'confirmed', '2025-11-15 09:45:00', '2025-11-15 09:50:00', NULL),
((SELECT user_id FROM Users WHERE email = 'needy5@wnk.com'),
 (SELECT donation_id FROM Donations WHERE donner_id = (SELECT user_id FROM Users WHERE email = 'donner4@wnk.com') AND plate_id = (SELECT plate_id FROM Plates WHERE plate_name = 'Leftover Pasta Carbonara' LIMIT 1) LIMIT 1),
 1, 'picked_up', '2025-11-16 12:00:00', '2025-11-16 12:05:00', '2025-11-16 16:00:00');

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
-- Test data created:
-- - 12 Plates (from 4 restaurants, various availability windows)
-- - 8 Customer Reservations (various statuses: pending, confirmed, picked_up, cancelled)
-- - 9 Donations (from 4 donners)
-- - 8 Needy Claims (various statuses: pending, confirmed, picked_up)
--
-- All users have password: "password"

