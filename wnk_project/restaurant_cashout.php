<?php
require_once 'includes/config.php';
requireRole('restaurant');

$page_title = 'Cashout - Restaurant Earnings';
$db = getDB();
$restaurant_id = $_SESSION['user_id'];

// Get summary statistics
$stmt = $db->prepare("SELECT 
                        COUNT(DISTINCT cr.reservation_id) as total_orders,
                        SUM(CASE WHEN cr.status = 'pending' THEN cr.total_amount ELSE 0 END) as pending_amount,
                        SUM(CASE WHEN cr.status = 'confirmed' THEN cr.total_amount ELSE 0 END) as confirmed_amount,
                        SUM(CASE WHEN cr.status = 'picked_up' THEN cr.total_amount ELSE 0 END) as completed_amount,
                        SUM(cr.total_amount) as total_amount
                     FROM Customer_Reservations cr
                     JOIN Plates p ON cr.plate_id = p.plate_id
                     WHERE p.restaurant_id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Get all orders by status
$stmt = $db->prepare("SELECT cr.reservation_id, cr.quantity, cr.total_amount, cr.status,
                            cr.reserved_at, cr.confirmed_at, cr.picked_up_at,
                            p.plate_name, u.first_name, u.last_name, u.email
                     FROM Customer_Reservations cr
                     JOIN Plates p ON cr.plate_id = p.plate_id
                     JOIN Customers c ON cr.customer_id = c.customer_id
                     JOIN Users u ON c.customer_id = u.user_id
                     WHERE p.restaurant_id = ?
                     ORDER BY cr.reserved_at DESC");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$all_orders = $stmt->get_result();

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

include 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 2rem auto;">
    <h1>💰 Earnings Dashboard</h1>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Total Orders Card -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Orders</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    <?php echo $summary['total_orders'] ?? 0; ?>
                </div>
            </div>
        </div>
        
        <!-- Pending Amount Card -->
        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Pending Payment</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    $<?php echo number_format($summary['pending_amount'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        
        <!-- Confirmed Amount Card -->
        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✓</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Confirmed Orders</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    $<?php echo number_format($summary['confirmed_amount'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        
        <!-- Completed Amount Card -->
        <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #1a1a1a;">
            <div style="padding: 1.5rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎉</div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Completed Orders</div>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">
                    $<?php echo number_format($summary['completed_amount'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Earnings Highlight -->
    <div class="card" style="background: var(--color-accent-light); border-left: 4px solid var(--color-accent); margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="font-size: 1.1rem;">Total Earnings</strong>
                <div style="color: var(--color-text-secondary); margin-top: 0.25rem;">All completed and confirmed orders</div>
            </div>
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-accent);">
                $<?php echo number_format(($summary['completed_amount'] ?? 0) + ($summary['confirmed_amount'] ?? 0), 2); ?>
            </div>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card">
        <h2>Order Details</h2>
        
        <div style="margin-bottom: 1.5rem;">
            <strong>Filter by Status:</strong>
            <a href="restaurant_cashout.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
            <a href="restaurant_cashout.php?filter=pending" class="btn btn-secondary <?php echo $filter === 'pending' ? 'btn-primary' : ''; ?>">Pending</a>
            <a href="restaurant_cashout.php?filter=confirmed" class="btn btn-secondary <?php echo $filter === 'confirmed' ? 'btn-primary' : ''; ?>">Confirmed</a>
            <a href="restaurant_cashout.php?filter=picked_up" class="btn btn-secondary <?php echo $filter === 'picked_up' ? 'btn-primary' : ''; ?>">Picked Up</a>
        </div>
        
        <?php if ($all_orders->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Meal</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $all_orders->data_seek(0);
                        while ($order = $all_orders->fetch_assoc()): 
                            // Apply filter
                            if ($filter !== 'all' && $order['status'] !== $filter) {
                                continue;
                            }
                        ?>
                            <tr>
                                <td><strong>#<?php echo $order['reservation_id']; ?></strong></td>
                                <td>
                                    <div><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                    <small style="color: var(--color-text-secondary);"><?php echo htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($order['plate_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="badge <?php 
                                        if ($order['status'] === 'pending') echo 'badge-pending';
                                        elseif ($order['status'] === 'confirmed') echo 'badge-confirmed';
                                        elseif ($order['status'] === 'picked_up') echo 'badge-success';
                                        else echo 'badge-inactive';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['reserved_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; padding: 2rem;">
                No orders found.
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Action Buttons -->
    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="restaurant_manage_plates.php" class="btn btn-secondary">
            ← Back to Plates
        </a>
        <a href="restaurant_dashboard.php" class="btn btn-secondary">
            Restaurant Dashboard
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
