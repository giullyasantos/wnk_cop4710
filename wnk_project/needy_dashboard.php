<?php
require_once 'includes/config.php';
requireRole('needy');

$page_title = 'Available Free Meals';
include 'includes/header.php';

$db = getDB();

// Get donated meals available for those in need
$query = "SELECT d.donation_id, p.plate_id, p.plate_name, p.description, 
                 r.restaurant_name, d.donated_at,
                 (d.quantity_available - COALESCE(SUM(CASE WHEN nc.status != 'cancelled' THEN nc.quantity ELSE 0 END), 0)) as available_count
          FROM Donations d
          JOIN Plates p ON d.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          LEFT JOIN Needy_Claim nc ON d.donation_id = nc.donation_id
          WHERE d.quantity_available > 0
          GROUP BY d.donation_id
          ORDER BY d.donated_at DESC";

$result = $db->query($query);
if (!$result) {
    die("Database query error: " . $db->error);
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>🤝 Free Meals Available</h1>
        <a href="needy_cashout.php" class="btn btn-success" style="background-color: #1a7f37; text-decoration: none; padding: 0.75rem 1.5rem;">
            📋 My Claims
        </a>
    </div>
    <p>Community members have donated delicious meals for you to claim at no cost!</p>
    
    <div class="needy-info">
        <p>✓ These meals are completely <strong>FREE</strong> thanks to generous donors</p>
        <p>✓ Simply click "Claim Meal" to receive your donation</p>
        <p>✓ Available for pickup or delivery (contact restaurant for details)</p>
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="meals-grid">
            <?php while ($donation = $result->fetch_assoc()): ?>
                <div class="meal-card free-meal-card">
                    <div class="meal-header">
                        <div class="free-badge">FREE 🎁</div>
                        <h3><?php echo htmlspecialchars($donation['plate_name']); ?></h3>
                        <p class="restaurant-name">
                            <strong><?php echo htmlspecialchars($donation['restaurant_name']); ?></strong>
                        </p>
                    </div>
                    
                    <p class="meal-description">
                        <?php echo htmlspecialchars($donation['description']); ?>
                    </p>
                    
                    <div class="donation-info">
                        <p class="donated-by">💝 Donated by a community member</p>
                        <p class="donated-time">
                            Available since: <?php echo date('M j, Y g:i A', strtotime($donation['donated_at'])); ?>
                        </p>
                        <?php if ($donation['available_count'] > 0): ?>
                            <p class="availability-count">Quantity available: <?php echo $donation['available_count']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="claim_meal.php?donation_id=<?php echo $donation['donation_id']; ?>" class="btn btn-claim">
                        Claim This Meal
                    </a>
                </div>
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
