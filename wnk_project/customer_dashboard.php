<?php
require_once 'includes/config.php';
requireRole('customer');

$page_title = 'Browse Meals';
include 'includes/header.php';

$db = getDB();

// Get available plates from restaurants
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
        <h1>🛒 Browse Available Meals</h1>
        <a href="view_cart.php" class="btn btn-success" style="background-color: #1a7f37; text-decoration: none; padding: 0.75rem 1.5rem;">
            🛍️ View Cart
        </a>
    </div>
    <p>Get delicious meals from local restaurants at discounted prices!</p>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="meals-grid">
            <?php while ($plate = $result->fetch_assoc()): ?>
                <?php if ($plate['available_count'] > 0): ?>
                <div class="meal-card">
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
                        <span class="discount-price">Price: $<?php echo number_format($plate['price'], 2); ?></span>
                    </div>
                    
                    <p class="availability">
                        <span class="in-stock">✓ <?php echo $plate['available_count']; ?> available</span>
                    </p>
                    
                    <form method="POST" action="add_to_cart.php" style="display: flex; gap: 5px;">
                        <input type="hidden" name="plate_id" value="<?php echo $plate['plate_id']; ?>">
                        <input type="number" name="quantity" min="1" max="10" value="1" style="flex: 0.5; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; padding: 8px;">
                            Add to Cart
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No meals available right now. Check back soon!</p>
    <?php endif; ?>
</div>

<style>
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
    background: #f9f9f9;
    border-radius: 4px;
}

.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 14px;
}

.discount-price {
    font-size: 20px;
    font-weight: bold;
    color: #28a745;
}

.savings {
    color: #ff6b6b;
    font-weight: bold;
    font-size: 14px;
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

.btn-disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<?php include 'includes/footer.php'; ?>
