# Waste Not Kitchen (WNK)

A web application for managing restaurant surplus food distribution to customers, donors, and the needy.

## Implementation by Role

### First Team Member
- Registration page
- Login page with session management
- Logout functionality
- Profile review and edit page (all user types)
- Restaurant dashboard
- Restaurant add plate page
- Restaurant manage plates page

### Second Team Member
- Customer webpages (browse plates, reservations, checkout)
- Donor webpages (browse plates, selections, checkout)
- Needy webpages (browse free plates, reservations, checkout)

### Third Team Member
- Administrator webpages
- Member lookup functionality
- Annual activity report for restaurants
- Annual purchase report for customers/donors
- Annual report of free plates received by needy
- Year-end donation report for donors

## Installation

### Using MAMP

1. Install MAMP from https://www.mamp.info
2. Copy project files to `/Applications/MAMP/htdocs/wnk_project/`
3. Create database `wnk_db` in phpMyAdmin (http://localhost:8888/phpMyAdmin)
4. Import the database schema file (optionally import the test data: test_data.sql)
5. Update database settings in `includes/config.php` if needed
6. Access the site at http://localhost:8888/wnk_project/

## Features

### Authentication
- User registration for user types (restaurant, customer, donor, needy)
- Email and password login with role-based redirects
- Session management
- Password hashing with bcrypt

### User Management
- Profile editing for all user types
- Role-specific data collection and updates
- Account status management

### Restaurant Features
- Dashboard with statistics
- Add surplus food plates with price, quantity, and time window
- Manage plates (view, update quantity, activate/deactivate, delete)
- Inventory tracking

## Security

- Password hashing with bcrypt
- SQL injection protection (prepared statements)
- XSS protection (htmlspecialchars)
- Session-based authentication
- Role-based access control
- Input sanitization

## Technologies

- PHP 7.4+
- MySQL 8.0
- HTML5, CSS3
- JavaScript (ES6)
- MAMP for local development