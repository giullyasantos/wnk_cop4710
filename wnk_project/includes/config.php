<?php
/**
 * Database Configuration
 * WNK - Waste Not Kitchen
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); 
define('DB_NAME', 'wnk_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Get database connection
 */
function getDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        die("Database connection error. Please try again later.");
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_type'] === $role;
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        die("Access denied. You don't have permission to view this page.");
    }
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get user dashboard URL based on role
 */
function getUserDashboardURL() {
    if (!isLoggedIn()) {
        return 'index.php';
    }
    
    switch($_SESSION['user_type']) {
        case 'restaurant':
            return 'restaurant_dashboard.php';
        case 'customer':
            return 'customer_dashboard.php';
        case 'donner':
            return 'donner_dashboard.php';
        case 'needy':
            return 'needy_dashboard.php';
        case 'admin':
            return 'index.php';
        default:
            return 'index.php';
    }
}

/**
 * Get user role display name
 */
function getRoleDisplayName($role = null) {
    if ($role === null && isLoggedIn()) {
        $role = $_SESSION['user_type'];
    }
    
    $roleNames = [
        'restaurant' => 'Restaurant Partner',
        'customer' => 'Customer',
        'donner' => 'Donor',
        'needy' => 'Community Member',
        'admin' => 'Administrator'
    ];
    
    return $roleNames[$role] ?? 'User';
}
?>

