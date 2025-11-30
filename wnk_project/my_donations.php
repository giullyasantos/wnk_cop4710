<?php
require_once 'includes/config.php';
requireRole('donner');

$page_title = 'My Donations';
$db = getDB();
$donner_id = $_SESSION['user_id'];

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT d.donation_id, d.quantity_available, d.original_quantity,
                 d.total_amount, d.donated_at,
                 p.plate_name, r.restaurant_name,
                 COALESCE(SUM(nc.quantity), 0) as claimed_qty
          FROM Donations d
          JOIN Plates p ON d.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          LEFT JOIN Needy_Claim nc ON d.donation_id = nc.donation_id AND nc.status != 'cancelled'
          WHERE d.donner_id = ?";

if ($filter === 'active') {
    $query .= " AND d.quantity_available > 0";
} elseif ($filter === 'completed') {
    $query .= " AND d.quantity_available = 0";
}

$query .= " GROUP BY d.donation_id ORDER BY d.donated_at DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $donner_id);
$stmt->execute();
$donations = $stmt->get_result();

include 'includes/header.php';
?>

<div class="card">
    <h1>My Donations</h1>
    
    <div style="margin-bottom: 1.5rem;">
        <strong>Filter:</strong>
        <a href="my_donations.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
        <a href="my_donations.php?filter=active" class="btn btn-secondary <?php echo $filter === 'active' ? 'btn-primary' : ''; ?>">Active</a>
        <a href="my_donations.php?filter=completed" class="btn btn-secondary <?php echo $filter === 'completed' ? 'btn-primary' : ''; ?>">Completed</a>
    </div>
    
    <?php if ($donations->num_rows > 0): ?>
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
                        <th>Total Value</th>
                        <th>Donation Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($donation = $donations->fetch_assoc()): 
                        $remaining = $donation['quantity_available'];
                        $claimed = $donation['claimed_qty'];
                    ?>
                        <tr>
                            <td><strong>#<?php echo $donation['donation_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($donation['plate_name']); ?></td>
                            <td><?php echo htmlspecialchars($donation['restaurant_name']); ?></td>
                            <td><?php echo $donation['original_quantity']; ?></td>
                            <td>
                                <span class="badge <?php echo $remaining > 0 ? 'badge-confirmed' : 'badge-inactive'; ?>">
                                    <?php echo $remaining; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    <?php echo $claimed; ?>
                                </span>
                            </td>
                            <td><strong>$<?php echo number_format($donation['total_amount'], 2); ?></strong></td>
                            <td><?php echo date('M d, Y H:i', strtotime($donation['donated_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No donations found. <a href="donner_dashboard.php">Start donating meals!</a></p>
    <?php endif; ?>
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

