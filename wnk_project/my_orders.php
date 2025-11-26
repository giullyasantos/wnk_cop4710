<?php
require_once 'includes/config.php';
requireRole('customer');

$page_title = 'My Orders';
$db = getDB();
$customer_id = $_SESSION['user_id'];

// Handle status updates (confirm, cancel pickup)
$success = '';
$error = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    
    // Verify ownership
    $stmt = $db->prepare("SELECT r.reservation_id FROM Customer_Reservations r WHERE r.reservation_id = ? AND r.customer_id = ?");
    $stmt->bind_param("ii", $reservation_id, $customer_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 1) {
        switch ($_GET['action']) {
            case 'pickup':
                $stmt = $db->prepare("UPDATE Customer_Reservations SET status = 'picked_up', picked_up_at = NOW() WHERE reservation_id = ? AND status = 'confirmed'");
                $stmt->bind_param("i", $reservation_id);
                if ($stmt->execute()) {
                    $success = "Marked as picked up!";
                } else {
                    $error = "Could not update pickup status.";
                }
                break;
                
            case 'cancel':
                $stmt = $db->prepare("UPDATE Customer_Reservations SET status = 'cancelled', cancelled_at = NOW() WHERE reservation_id = ? AND status IN ('pending', 'confirmed')");
                $stmt->bind_param("i", $reservation_id);
                if ($stmt->execute()) {
                    $success = "Order cancelled.";
                } else {
                    $error = "Could not cancel order.";
                }
                break;
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT cr.reservation_id, cr.quantity, cr.total_amount, cr.status,
                 cr.reserved_at, cr.confirmed_at, cr.picked_up_at,
                 p.plate_name, p.price, r.restaurant_name
          FROM Customer_Reservations cr
          JOIN Plates p ON cr.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          WHERE cr.customer_id = ?";

if ($filter === 'pending') {
    $query .= " AND cr.status = 'pending'";
} elseif ($filter === 'confirmed') {
    $query .= " AND cr.status = 'confirmed'";
} elseif ($filter === 'completed') {
    $query .= " AND cr.status = 'picked_up'";
} elseif ($filter === 'cancelled') {
    $query .= " AND cr.status = 'cancelled'";
}

$query .= " ORDER BY cr.reserved_at DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();

include 'includes/header.php';
?>

<div class="card">
    <h1>My Orders</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="margin-bottom: 1.5rem;">
        <strong>Filter:</strong>
        <a href="my_orders.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
        <a href="my_orders.php?filter=pending" class="btn btn-secondary <?php echo $filter === 'pending' ? 'btn-primary' : ''; ?>">Pending</a>
        <a href="my_orders.php?filter=confirmed" class="btn btn-secondary <?php echo $filter === 'confirmed' ? 'btn-primary' : ''; ?>">Confirmed</a>
        <a href="my_orders.php?filter=completed" class="btn btn-secondary <?php echo $filter === 'completed' ? 'btn-primary' : ''; ?>">Completed</a>
        <a href="my_orders.php?filter=cancelled" class="btn btn-secondary <?php echo $filter === 'cancelled' ? 'btn-primary' : ''; ?>">Cancelled</a>
    </div>
    
    <?php if ($orders->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Meal</th>
                        <th>Restaurant</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['reservation_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['plate_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge <?php 
                                    if ($order['status'] === 'pending') echo 'badge-pending';
                                    elseif ($order['status'] === 'confirmed') echo 'badge-confirmed';
                                    elseif ($order['status'] === 'picked_up') echo 'badge-success';
                                    else echo 'badge-inactive';
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['reserved_at'])); ?></td>
                            <td>
                                <?php if ($order['status'] === 'confirmed'): ?>
                                    <a href="?action=pickup&id=<?php echo $order['reservation_id']; ?>" class="btn btn-success" style="padding: 4px 8px; font-size: 0.85rem;">
                                        Mark Picked Up
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <a href="?action=cancel&id=<?php echo $order['reservation_id']; ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.85rem;" 
                                       onclick="return confirm('Cancel this order?')">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No orders found. <a href="customer_dashboard.php">Browse and buy meals!</a></p>
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
