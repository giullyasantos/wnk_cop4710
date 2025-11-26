<?php
require_once 'includes/config.php';
requireRole('needy');

$page_title = 'Cashout - Needy Claims';
$db = getDB();
$needy_id = $_SESSION['user_id'];

// Get summary statistics
$stmt = $db->prepare("SELECT 
                        COUNT(DISTINCT nc.claim_id) as total_claims,
                        SUM(CASE WHEN nc.status = 'pending' THEN 1 ELSE 0 END) as pending_claims,
                        SUM(CASE WHEN nc.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_claims,
                        SUM(CASE WHEN nc.status = 'picked_up' THEN 1 ELSE 0 END) as completed_claims
                     FROM Needy_Claim nc
                     WHERE nc.needy_id = ?");
$stmt->bind_param("i", $needy_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Get all claims
$stmt = $db->prepare("SELECT nc.claim_id, nc.quantity, nc.status,
                            nc.claimed_at, nc.confirmed_at, nc.picked_up_at,
                            d.donation_id, p.plate_name, r.restaurant_name
                     FROM Needy_Claim nc
                     JOIN Donations d ON nc.donation_id = d.donation_id
                     JOIN Plates p ON d.plate_id = p.plate_id
                     JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                     WHERE nc.needy_id = ?
                     ORDER BY nc.claimed_at DESC");
$stmt->bind_param("i", $needy_id);
$stmt->execute();
$all_claims = $stmt->get_result();

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

include 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 2rem auto;">
    <h1>🍽️ My Claimed Meals</h1>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Total Claims Card -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Claims</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['total_claims'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Pending Claims Card -->
        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Pending Claims</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['pending_claims'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Confirmed Claims Card -->
        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✓</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Confirmed Claims</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['confirmed_claims'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Picked Up Card -->
        <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #1a1a1a;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎉</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Picked Up</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['completed_claims'] ?? 0; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Claims Table -->
    <div class="card">
        <h2>Claim Details</h2>
        
        <div style="margin-bottom: 1.5rem;">
            <strong>Filter by Status:</strong>
            <a href="needy_cashout.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
            <a href="needy_cashout.php?filter=pending" class="btn btn-secondary <?php echo $filter === 'pending' ? 'btn-primary' : ''; ?>">Pending</a>
            <a href="needy_cashout.php?filter=confirmed" class="btn btn-secondary <?php echo $filter === 'confirmed' ? 'btn-primary' : ''; ?>">Confirmed</a>
            <a href="needy_cashout.php?filter=picked_up" class="btn btn-secondary <?php echo $filter === 'picked_up' ? 'btn-primary' : ''; ?>">Picked Up</a>
        </div>
        
        <?php if ($all_claims->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Claim ID</th>
                            <th>Meal</th>
                            <th>Restaurant</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Claimed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $all_claims->data_seek(0);
                        while ($claim = $all_claims->fetch_assoc()): 
                            // Apply filter
                            if ($filter !== 'all' && $claim['status'] !== $filter) {
                                continue;
                            }
                        ?>
                            <tr>
                                <td><strong>#<?php echo $claim['claim_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($claim['plate_name']); ?></td>
                                <td><?php echo htmlspecialchars($claim['restaurant_name']); ?></td>
                                <td><?php echo $claim['quantity']; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        if ($claim['status'] === 'pending') echo 'badge-pending';
                                        elseif ($claim['status'] === 'confirmed') echo 'badge-confirmed';
                                        elseif ($claim['status'] === 'picked_up') echo 'badge-success';
                                        else echo 'badge-inactive';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $claim['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($claim['claimed_at'])); ?></td>
                                <td>
                                    <a href="needy_dashboard.php" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.85rem;">
                                        View More
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; padding: 2rem;">
                No claims found. <a href="needy_dashboard.php">Browse available meals!</a>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Action Buttons -->
    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="needy_dashboard.php" class="btn btn-secondary">
            ← Back to Meals
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
