<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Waste Not Kitchen</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <a href="index.php">🍽️ Waste Not Kitchen</a>
                </div>
                <ul class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-welcome">
                            <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                            <span class="role-badge"><?php echo getRoleDisplayName(); ?></span>
                        </li>
                        
                        <?php if (hasRole('restaurant')): ?>
                            <li><a href="restaurant_dashboard.php">Dashboard</a></li>
                            <li><a href="restaurant_add_plate.php">Add Plate</a></li>
                            <li><a href="restaurant_manage_plates.php">Manage Plates</a></li>
                        <?php elseif (hasRole('customer')): ?>
                            <li><a href="customer_dashboard.php">Browse Meals</a></li>
                            <li><a href="my_orders.php">My Orders</a></li>
                        <?php elseif (hasRole('donner')): ?>
                            <li><a href="donner_dashboard.php">Donate Meals</a></li>
                            <li><a href="my_donations.php">My Donations</a></li>
                        <?php elseif (hasRole('needy')): ?>
                            <li><a href="needy_dashboard.php">Available Meals</a></li>
                            <li><a href="my_claims.php">My Claimed Meals</a></li>
                        <?php endif; ?>
                        
                        <li><a href="profile_edit.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    
    <main class="container">
