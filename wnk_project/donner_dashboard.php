<?php
require_once 'includes/config.php';
requireRole('donner');

$page_title = 'Donate Meals';
include 'includes/header.php';

$db = getDB();

// Get available plates for donation
$query = "SELECT p.plate_id, p.plate_name, p.price, 
                 p.description, p.quantity_available, r.restaurant_name, r.restaurant_id,
                 (p.quantity_available - COALESCE(SUM(CASE WHEN cr.status != 'cancelled' THEN cr.quantity ELSE 0 END), 0)) as available_count
          FROM Plates p
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          LEFT JOIN Customer_Reservations cr ON p.plate_id = cr.plate_id
          WHERE p.is_active = 1 
            AND p.quantity_available > 0
            AND NOW() BETWEEN p.available_from AND p.available_until
          GROUP BY p.plate_id
          ORDER BY p.created_at DESC";

$result = $db->query($query);
if (!$result) {
    die("Database query error: " . $db->error);
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>❤️ Donate Meals to Those in Need</h1>
        <a href="donner_cashout.php" class="btn btn-success" style="background-color: #1a7f37; text-decoration: none; padding: 0.75rem 1.5rem;">
            💝 Donation History
        </a>
    </div>
    <p>Pay it forward by purchasing meals for community members in need. Your donation directly helps!</p>
    
    <div class="donor-info">
        <p>As a donor, you're helping bridge the gap between restaurants and those who need a meal. 
           When you purchase a meal below, it will be added to our donation pool for those in need to claim.</p>
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="meals-grid">
            <?php while ($plate = $result->fetch_assoc()): ?>
                <div class="meal-card donation-card">
                    <div class="meal-header">
                        <h3><?php echo htmlspecialchars($plate['plate_name']); ?></h3>
                        <p class="restaurant-name">
                            <strong><?php echo htmlspecialchars($plate['restaurant_name']); ?></strong>
                        </p>
                    </div>
                    
                    <p class="meal-description">
                        <?php echo htmlspecialchars($plate['description']); ?>
                    </p>
                    
                    <div class="meal-pricing">
                        <span class="donation-price">Donation amount: $<?php echo number_format($plate['price'], 2); ?></span>
                    </div>
                    
                    <div class="donor-message">
                        <p>💝 This meal will be given to someone in need</p>
                    </div>
                    
                    <p class="availability">
                        <?php if ($plate['available_count'] > 0): ?>
                            <span class="in-stock">✓ <?php echo $plate['available_count']; ?> available to donate</span>
                        <?php else: ?>
                            <span class="out-of-stock">✗ Out of stock</span>
                        <?php endif; ?>
                    </p>
                    
                    <?php if ($plate['available_count'] > 0): ?>
                        <a href="donate_meal.php?plate_id=<?php echo $plate['plate_id']; ?>" class="btn btn-success">
                            Donate This Meal
                        </a>
                    <?php else: ?>
                        <button class="btn btn-disabled" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No meals available to donate right now. Check back soon!</p>
    <?php endif; ?>
</div>

<style>
.donor-info {
    background: #e7f3ff;
    border-left: 4px solid #2196F3;
    padding: 15px;
    border-radius: 4px;
    margin: 20px 0;
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

.donation-card {
    border-left: 4px solid #ff6b6b;
}

.meal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.meal-header h3 {
    margin: 0 0 10px 0;
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

.meal-pricing {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin: 15px 0;
    padding: 10px;
    background: #fff9e6;
    border-radius: 4px;
}

.original-price {
    color: #999;
    font-size: 14px;
}

.donation-price {
    font-size: 18px;
    font-weight: bold;
    color: #ff6b6b;
}

.donor-message {
    background: #fff9e6;
    padding: 10px;
    border-radius: 4px;
    text-align: center;
    font-style: italic;
    color: #666;
    margin: 10px 0;
}

.availability {
    margin: 10px 0;
}

.in-stock {
    color: #28a745;
    font-weight: bold;
}

.out-of-stock {
    color: #dc3545;
    font-weight: bold;
}

.btn-success {
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}

.btn-success:hover {
    background: #218838;
}

.btn-disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<?php include 'includes/footer.php'; ?>
