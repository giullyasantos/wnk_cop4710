<?php
require_once 'includes/config.php';
requireRole('needy');

$page_title = 'Available Free Meals';
include 'includes/header.php';

$db = getDB();

// Get donated meals available for those in need - grouped by plate to combine duplicates
$query = "SELECT p.plate_id, p.plate_name, p.description, 
                 r.restaurant_name, MAX(d.donated_at) as most_recent_donation,
                 SUM(d.quantity_available - COALESCE((SELECT SUM(CASE WHEN nc.status != 'cancelled' THEN nc.quantity ELSE 0 END) 
                                                       FROM Needy_Claim nc 
                                                       WHERE nc.donation_id = d.donation_id), 0)) as total_available
          FROM Donations d
          JOIN Plates p ON d.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          WHERE d.quantity_available > 0
          GROUP BY p.plate_id
          HAVING total_available > 0
          ORDER BY most_recent_donation DESC";

$result = $db->query($query);
if (!$result) {
    die("Database query error: " . $db->error);
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>🤝 Free Meals Available</h1>
        <a href="view_cart.php" class="btn btn-primary" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer;">
            View Cart
        </a>
    </div>
    <p>Community members have donated delicious meals for you to claim at no cost!</p>
    
    <?php if (isset($_SESSION['cart_error'])): ?>
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong>⚠️ <?php echo $_SESSION['cart_error']; ?></strong>
        </div>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>
    
    <div class="needy-info">
        <p>✓ These meals are completely <strong>FREE</strong> thanks to generous donors</p>
        <p>✓ You can claim up to <strong>2 meals per day</strong></p>
        <p>✓ Available for pickup or delivery (contact restaurant for details)</p>
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="meals-grid">
            <?php while ($meal = $result->fetch_assoc()): ?>
                <?php if ($meal['total_available'] > 0): ?>
                <div class="meal-card free-meal-card">
                    <div class="meal-header">
                        <div class="free-badge">FREE 🎁</div>
                        <h3><?php echo htmlspecialchars($meal['plate_name']); ?></h3>
                        <p class="restaurant-name">
                            <strong><?php echo htmlspecialchars($meal['restaurant_name']); ?></strong>
                        </p>
                    </div>
                    
                    <p class="meal-description">
                        <?php echo htmlspecialchars($meal['description']); ?>
                    </p>
                    
                    <div class="donation-info">
                        <p class="donated-by">💝 Donated by community members</p>
                        <p class="donated-time">
                            Most recent: <?php echo date('M j, Y g:i A', strtotime($meal['most_recent_donation'])); ?>
                        </p>
                        <p class="availability-count">Total available: <?php echo $meal['total_available']; ?></p>
                    </div>
                    
                    <form method="POST" action="add_to_cart.php" style="display: flex; gap: 5px;">
                        <input type="hidden" name="plate_id" value="<?php echo $meal['plate_id']; ?>">
                        <input type="number" name="quantity" min="1" max="<?php echo min(10, $meal['total_available']); ?>" value="1" style="flex: 0.5; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                        <button type="submit" class="btn btn-claim" style="flex: 1; background: #4caf50; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                            Add to Cart
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-meals">
            <p>No free meals available right now.</p>
            <p>Check back soon - donors are constantly adding meals to help the community!</p>
        </div>
    <?php endif; ?>
</div>

<style>
.needy-info {
    background: #e8f5e9;
    border-left: 4px solid #4caf50;
    padding: 20px;
    border-radius: 4px;
    margin: 20px 0;
}

.needy-info p {
    margin: 8px 0;
    color: #2e7d32;
    font-weight: 500;
}

.meals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.meal-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.free-meal-card {
    border: 2px solid #4caf50;
    background: linear-gradient(135deg, #ffffff 0%, #f1f8f6 100%);
}

.meal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.free-badge {
    display: inline-block;
    background: #4caf50;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 10px;
}

.meal-header h3 {
    margin: 10px 0 5px 0;
    color: #333;
}

.restaurant-name {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.meal-description {
    color: #555;
    font-size: 14px;
    margin: 15px 0;
}

.donation-info {
    background: #f0f7ff;
    padding: 12px;
    border-radius: 4px;
    margin: 15px 0;
    border-left: 3px solid #4caf50;
}

.donated-by {
    margin: 0 0 8px 0;
    color: #2e7d32;
    font-weight: bold;
    font-size: 14px;
}

.donated-time {
    margin: 0;
    color: #666;
    font-size: 12px;
}

.btn-claim {
    background: #4caf50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: block;
    text-align: center;
    width: 100%;
    font-weight: bold;
    transition: background 0.2s;
}

.btn-claim:hover {
    background: #45a049;
}

.no-meals {
    text-align: center;
    padding: 40px 20px;
    background: #f5f5f5;
    border-radius: 8px;
    margin-top: 30px;
}

.no-meals p {
    color: #666;
    font-size: 16px;
    margin: 10px 0;
}
</style>

<?php include 'includes/footer.php'; ?>
