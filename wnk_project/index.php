<?php
require_once 'includes/config.php';
$page_title = 'Home';
include 'includes/header.php';
?>

<div class="hero">
    <h1>Welcome to Waste Not Kitchen</h1>
    <p>Connect surplus food with those who need it</p>
    
    <?php if (!isLoggedIn()): ?>
        <div>
            <a href="register.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-secondary">Login</a>
        </div>
    <?php endif; ?>
</div>

<div class="grid">
    <div class="grid-item">
        <h3>🍽️ For Restaurants</h3>
        <p>Reduce waste and help your community by selling surplus plates at discounted prices.</p>
    </div>
    
    <div class="grid-item">
        <h3>🛒 For Customers</h3>
        <p>Get delicious meals from local restaurants at significantly reduced prices.</p>
    </div>
    
    <div class="grid-item">
        <h3>❤️ For Donors</h3>
        <p>Pay it forward by purchasing meals for those in need.</p>
    </div>
    
    <div class="grid-item">
        <h3>🤝 For Those in Need</h3>
        <p>Access free meals donated by generous community members.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
