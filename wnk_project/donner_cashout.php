<?php
require_once 'includes/config.php';
requireRole('donner');

$page_title = 'Cashout - Donation History';
$db = getDB();
$donner_id = $_SESSION['user_id'];

// Get summary statistics
$stmt = $db->prepare("SELECT 
                        COUNT(DISTINCT d.donation_id) as total_donations,
                        SUM(d.quantity_available) as total_meals_donated,
                        SUM(d.total_amount) as total_value,
                        COUNT(DISTINCT p.plate_id) as unique_meals
                     FROM Donations d
                     JOIN Plates p ON d.plate_id = p.plate_id
                     WHERE d.donner_id = ?");
$stmt->bind_param("i", $donner_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Get claims on donations
$stmt = $db->prepare("SELECT 
                        COUNT(DISTINCT nc.claim_id) as total_claims,
                        SUM(nc.quantity) as claimed_qty
                     FROM Needy_Claim nc
                     JOIN Donations d ON nc.donation_id = d.donation_id
                     WHERE d.donner_id = ?");
$stmt->bind_param("i", $donner_id);
$stmt->execute();
$claims_summary = $stmt->get_result()->fetch_assoc();

// Get all donations
$stmt = $db->prepare("SELECT d.donation_id, d.quantity_available, d.original_quantity,
                            d.total_amount, d.donated_at,
                            p.plate_name, r.restaurant_name,
                            COALESCE(SUM(nc.quantity), 0) as claimed_qty
                     FROM Donations d
                     JOIN Plates p ON d.plate_id = p.plate_id
                     JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                     LEFT JOIN Needy_Claim nc ON d.donation_id = nc.donation_id
                     WHERE d.donner_id = ?
                     GROUP BY d.donation_id
                     ORDER BY d.donated_at DESC");
$stmt->bind_param("i", $donner_id);
$stmt->execute();
$all_donations = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 2rem auto;">
    <h1>💝 Donation History</h1>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Total Donations Card -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Donations</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['total_donations'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Meals Donated Card -->
        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🍽️</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Meals Donated</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['total_meals_donated'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Total Value Card -->
        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Value</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    $<?php echo number_format($summary['total_value'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        
        <!-- Claims Card -->
        <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #1a1a1a;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✓</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Claimed Meals</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $claims_summary['claimed_qty'] ?? 0; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Impact Summary -->
    <div class="card" style="background: var(--color-success-light); border-left: 4px solid var(--color-success); margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="font-size: 1.1rem;">🌟 Your Impact</strong>
                <div style="color: var(--color-text-secondary); margin-top: 0.25rem;">
                    You've donated <?php echo $summary['total_meals_donated'] ?? 0; ?> meals worth $<?php echo number_format($summary['total_value'] ?? 0, 2); ?> to people in need!
                </div>
            </div>
        </div>
    </div>
    
    <!-- Donations Table -->
    <div class="card">
        <h2>Donation Details</h2>
        
        <?php if ($all_donations->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Meal</th>
                            <th>Restaurant</th>
                            <th>Original Qty</th>
                            <th>Remaining</th>
                            <th>Claimed</th>
                            <th>Value</th>
                            <th>Donated Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($donation = $all_donations->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $donation['donation_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($donation['plate_name']); ?></td>
                                <td><?php echo htmlspecialchars($donation['restaurant_name']); ?></td>
                                <td><?php echo $donation['original_quantity']; ?></td>
                                <td>
                                    <span class="badge <?php echo $donation['quantity_available'] > 0 ? 'badge-confirmed' : 'badge-inactive'; ?>">
                                        <?php echo $donation['quantity_available']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php echo $donation['claimed_qty']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($donation['total_amount'], 2); ?></strong>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($donation['donated_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; padding: 2rem;">
                No donations found. <a href="donner_dashboard.php">Make your first donation!</a>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Action Buttons -->
    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="donner_dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-pending {
    background-color: #fff8c5;
    color: #9a6700;
}

.badge-confirmed {
    background-color: #ddf4ff;
    color: #0969da;
}

.badge-success {
    background-color: #dafbe1;
    color: #1a7f37;
}

.badge-inactive {
    background-color: #f0f3f5;
    color: #57606a;
}
</style>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
