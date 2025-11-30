<?php
require_once 'includes/config.php';
requireRole('needy');

$page_title = 'My Claimed Meals';
$db = getDB();
$needy_id = $_SESSION['user_id'];

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT nc.claim_id, nc.quantity, nc.status,
                 nc.claimed_at, nc.confirmed_at, nc.picked_up_at,
                 p.plate_name, p.price, r.restaurant_name
          FROM Needy_Claim nc
          JOIN Donations d ON nc.donation_id = d.donation_id
          JOIN Plates p ON d.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          WHERE nc.needy_id = ?";

if ($filter === 'pending') {
    $query .= " AND nc.status = 'pending'";
} elseif ($filter === 'confirmed') {
    $query .= " AND nc.status = 'confirmed'";
} elseif ($filter === 'completed') {
    $query .= " AND nc.status = 'picked_up'";
} elseif ($filter === 'cancelled') {
    $query .= " AND nc.status = 'cancelled'";
}

$query .= " ORDER BY nc.claimed_at DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $needy_id);
$stmt->execute();
$claims = $stmt->get_result();

include 'includes/header.php';
?>

<div class="card">
    <h1>My Claimed Meals</h1>
    
    <div style="margin-bottom: 1.5rem;">
        <strong>Filter:</strong>
        <a href="my_claims.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
        <a href="my_claims.php?filter=pending" class="btn btn-secondary <?php echo $filter === 'pending' ? 'btn-primary' : ''; ?>">Pending</a>
        <a href="my_claims.php?filter=confirmed" class="btn btn-secondary <?php echo $filter === 'confirmed' ? 'btn-primary' : ''; ?>">Confirmed</a>
        <a href="my_claims.php?filter=completed" class="btn btn-secondary <?php echo $filter === 'completed' ? 'btn-primary' : ''; ?>">Completed</a>
        <a href="my_claims.php?filter=cancelled" class="btn btn-secondary <?php echo $filter === 'cancelled' ? 'btn-primary' : ''; ?>">Cancelled</a>
    </div>
    
    <?php if ($claims->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Meal</th>
                        <th>Restaurant</th>
                        <th>Quantity</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Claim Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($claim = $claims->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $claim['claim_id']; ?></td>
                            <td><?php echo htmlspecialchars($claim['plate_name']); ?></td>
                            <td><?php echo htmlspecialchars($claim['restaurant_name']); ?></td>
                            <td><?php echo $claim['quantity']; ?></td>
                            <td>$<?php echo number_format($claim['price'], 2); ?></td>
                            <td>
                                <span class="badge <?php 
                                    if ($claim['status'] === 'pending') echo 'badge-pending';
                                    elseif ($claim['status'] === 'confirmed') echo 'badge-confirmed';
                                    elseif ($claim['status'] === 'picked_up') echo 'badge-success';
                                    else echo 'badge-inactive';
                                ?>">
                                    <?php echo ucfirst($claim['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($claim['claimed_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No claims found. <a href="needy_dashboard.php">Browse available meals!</a></p>
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
