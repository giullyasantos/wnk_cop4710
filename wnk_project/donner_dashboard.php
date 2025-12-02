<?php
require_once 'includes/config.php';
require_once 'includes/cart.php';
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
        <h1 style="color: #fff;">❤️ Donate Meals to Those in Need</h1>
        <a href="view_cart.php" class="btn btn-primary">
            🛒 View Cart
        </a>
    </div>
    <p style="color: #fff;">Pay it forward by purchasing meals for community members in need. Your donation directly helps!</p>
    
    <div class="donor-info">
        <p>As a donor, you're helping bridge the gap between restaurants and those who need a meal. 
           When you purchase a meal below, it will be added to our donation pool for those in need to claim.</p>
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="meals-grid">
            <?php while ($plate = $result->fetch_assoc()): ?>
                <?php if ($plate['available_count'] > 0): ?>
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
                        <span class="donation-price">Price per meal: $<?php echo number_format($plate['price'], 2); ?></span>
                    </div>
                    
                    <div class="donor-message">
                        <p>💝 This meal will be given to someone in need</p>
                    </div>
                    
                    <p class="availability">
                        <span class="in-stock">✓ <?php echo $plate['available_count']; ?> available to donate</span>
                    </p>
                    
                    <form method="POST" action="add_to_cart.php" style="display: flex; gap: 5px;">
                        <input type="hidden" name="plate_id" value="<?php echo $plate['plate_id']; ?>">
                        <input type="number" name="quantity" min="1" max="10" value="1" style="flex: 0.5; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                        <button type="submit" class="btn btn-success" style="flex: 1; background: #28a745; color: white; border: none; border-radius: 4px; padding: 8px; cursor: pointer; font-weight: bold;">
                            Add to Cart
                        </button>
                    </form>
                </div>
                <?php endif; ?>
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
    display: flex;
    flex-direction: column;
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
    flex-grow: 1;
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

.donation-form {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.form-group {
    margin-bottom: 12px;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-group input:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.total-display {
    background: #fff3cd;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
    font-weight: bold;
    color: #ff6b6b;
    text-align: center;
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
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    display: inline-block;
}
</style>

<?php include 'includes/footer.php'; ?>

