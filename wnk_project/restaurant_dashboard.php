<?php
require_once 'includes/config.php';
requireRole('restaurant');

$page_title = 'Restaurant Dashboard';

$db = getDB();
$restaurant_id = $_SESSION['user_id'];

// Get restaurant info
$stmt = $db->prepare("SELECT * FROM Restaurants WHERE restaurant_id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();

// Get statistics
$stmt = $db->prepare("SELECT COUNT(*) as total_plates FROM Plates WHERE restaurant_id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$stmt = $db->prepare("SELECT COUNT(*) as active_plates FROM Plates WHERE restaurant_id = ? AND is_active = TRUE");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$active_stats = $stmt->get_result()->fetch_assoc();

$stmt = $db->prepare("SELECT SUM(original_quantity - quantity_available) as total_sold FROM Plates WHERE restaurant_id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$sold_stats = $stmt->get_result()->fetch_assoc();

// Get recent plates
$stmt = $db->prepare("SELECT * FROM Plates WHERE restaurant_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$recent_plates = $stmt->get_result();

include 'includes/header.php';
?>

<div class="card">
    <h1>Welcome, <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>!</h1>
    
    <div class="grid">
        <div class="grid-item">
            <h3><?php echo $stats['total_plates']; ?></h3>
            <p>Total Plates Listed</p>
        </div>
        
        <div class="grid-item">
            <h3><?php echo $active_stats['active_plates']; ?></h3>
            <p>Currently Active</p>
        </div>
        
        <div class="grid-item">
            <h3><?php echo $sold_stats['total_sold'] ?? 0; ?></h3>
            <p>Total Items Sold</p>
        </div>
    </div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="restaurant_add_plate.php" class="btn btn-primary">Add New Plate</a>
        <a href="restaurant_manage_plates.php" class="btn btn-secondary">Manage All Plates</a>
        <a href="profile_edit.php" class="btn btn-secondary">Edit Profile</a>
    </div>
</div>

<div class="card">
    <h2>Recent Plates</h2>
    
    <?php if ($recent_plates->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Plate Name</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($plate = $recent_plates->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($plate['plate_name']); ?></td>
                        <td>$<?php echo number_format($plate['price'], 2); ?></td>
                        <td><?php echo $plate['quantity_available']; ?> / <?php echo $plate['original_quantity']; ?></td>
                        <td>
                            <?php if ($plate['is_active']): ?>
                                <span class="badge badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($plate['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No plates added yet. <a href="restaurant_add_plate.php">Add your first plate!</a></p>
    <?php endif; ?>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
