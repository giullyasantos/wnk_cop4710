<?php
require_once 'includes/config.php';
requireRole('restaurant');

$page_title = 'Manage Plates';
$success = '';
$error = '';

$db = getDB();
$restaurant_id = $_SESSION['user_id'];

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $plate_id = intval($_GET['id']);
    
    // Verify this plate belongs to this restaurant
    $stmt = $db->prepare("SELECT plate_id FROM Plates WHERE plate_id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $plate_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        switch($_GET['action']) {
            case 'deactivate':
                $stmt = $db->prepare("UPDATE Plates SET is_active = FALSE WHERE plate_id = ?");
                $stmt->bind_param("i", $plate_id);
                if ($stmt->execute()) {
                    $success = "Plate deactivated successfully.";
                }
                break;
                
            case 'activate':
                $stmt = $db->prepare("UPDATE Plates SET is_active = TRUE WHERE plate_id = ?");
                $stmt->bind_param("i", $plate_id);
                if ($stmt->execute()) {
                    $success = "Plate activated successfully.";
                }
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM Plates WHERE plate_id = ?");
                $stmt->bind_param("i", $plate_id);
                if ($stmt->execute()) {
                    $success = "Plate deleted successfully.";
                } else {
                    $error = "Could not delete plate. It may have existing reservations.";
                }
                break;
        }
    }
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $plate_id = intval($_POST['plate_id']);
    $new_quantity = intval($_POST['quantity_available']);
    
    // Verify ownership and get original quantity
    $stmt = $db->prepare("SELECT original_quantity FROM Plates WHERE plate_id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $plate_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $plate = $result->fetch_assoc();
        
        if ($new_quantity >= 0 && $new_quantity <= $plate['original_quantity']) {
            $stmt = $db->prepare("UPDATE Plates SET quantity_available = ? WHERE plate_id = ?");
            $stmt->bind_param("ii", $new_quantity, $plate_id);
            
            if ($stmt->execute()) {
                $success = "Quantity updated successfully.";
                
                // Auto-deactivate if quantity is 0
                if ($new_quantity == 0) {
                    $stmt = $db->prepare("UPDATE Plates SET is_active = FALSE WHERE plate_id = ?");
                    $stmt->bind_param("i", $plate_id);
                    $stmt->execute();
                }
            }
        } else {
            $error = "Invalid quantity. Must be between 0 and original quantity.";
        }
    }
}

// Get all plates
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT * FROM Plates WHERE restaurant_id = ?";

if ($filter === 'active') {
    $query .= " AND is_active = TRUE";
} elseif ($filter === 'inactive') {
    $query .= " AND is_active = FALSE";
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$plates = $stmt->get_result();

// Function to determine plate status
function getPlateStatus($plate) {
    $now = new DateTime();
    $expiration = new DateTime($plate['available_until']);
    $is_expired = $now > $expiration;
    $out_of_stock = $plate['quantity_available'] <= 0;
    
    if ($is_expired || $out_of_stock) {
        return [
            'status' => 'Inactive',
            'badge_class' => 'badge-inactive',
            'reason' => $is_expired ? 'Expired' : 'Out of Stock'
        ];
    } elseif ($plate['is_active']) {
        return [
            'status' => 'Active',
            'badge_class' => 'badge-active',
            'reason' => ''
        ];
    } else {
        return [
            'status' => 'Inactive',
            'badge_class' => 'badge-inactive',
            'reason' => 'Disabled'
        ];
    }
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Manage Your Plates</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="margin-bottom: 1rem; display: flex; gap: 1rem; justify-content: space-between;">
        <div>
            <a href="restaurant_add_plate.php" class="btn btn-primary">Add New Plate</a>
        </div>
        <div>
            <a href="restaurant_cashout.php" class="btn btn-success" style="background-color: #1a7f37;">
                💰 View Earnings
            </a>
        </div>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <strong>Filter:</strong>
        <a href="restaurant_manage_plates.php?filter=all" class="btn btn-secondary <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>">All</a>
        <a href="restaurant_manage_plates.php?filter=active" class="btn btn-secondary <?php echo $filter === 'active' ? 'btn-primary' : ''; ?>">Active</a>
        <a href="restaurant_manage_plates.php?filter=inactive" class="btn btn-secondary <?php echo $filter === 'inactive' ? 'btn-primary' : ''; ?>">Inactive</a>
    </div>
    
    <?php if ($plates->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Plate Name</th>
                    <th>Price</th>
                    <th>Available Qty</th>
                    <th>Time Window</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($plate = $plates->fetch_assoc()): 
                    $plate_status = getPlateStatus($plate);
                ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($plate['plate_name']); ?></strong>
                            <?php if ($plate['description']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($plate['description'], 0, 50)); ?><?php echo strlen($plate['description']) > 50 ? '...' : ''; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($plate['price'], 2); ?></td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="plate_id" value="<?php echo $plate['plate_id']; ?>">
                                <input type="number" name="quantity_available" value="<?php echo $plate['quantity_available']; ?>" min="0" max="<?php echo $plate['original_quantity']; ?>" style="width: 60px; padding: 2px;">
                                / <?php echo $plate['original_quantity']; ?>
                                <button type="submit" name="update_quantity" class="btn btn-secondary" style="padding: 2px 8px; font-size: 0.8rem;">Update</button>
                            </form>
                        </td>
                        <td>
                            <small>
                                <?php echo date('M d, g:ia', strtotime($plate['available_from'])); ?><br>
                                to <?php echo date('M d, g:ia', strtotime($plate['available_until'])); ?>
                            </small>
                        </td>
                        <td>
                            <small><?php echo date('M d, Y H:i', strtotime($plate['available_until'])); ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo $plate_status['badge_class']; ?>">
                                <?php echo $plate_status['status']; ?>
                                <?php if ($plate_status['reason']): ?>
                                    <br><small><?php echo $plate_status['reason']; ?></small>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($plate['is_active']): ?>
                                <a href="?action=deactivate&id=<?php echo $plate['plate_id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.85rem;" onclick="return confirm('Deactivate this plate?')">Deactivate</a>
                            <?php else: ?>
                                <a href="?action=activate&id=<?php echo $plate['plate_id']; ?>" class="btn btn-success" style="padding: 4px 8px; font-size: 0.85rem;">Activate</a>
                            <?php endif; ?>
                            
                            <a href="?action=delete&id=<?php echo $plate['plate_id']; ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.85rem;" onclick="return confirmDelete('<?php echo htmlspecialchars($plate['plate_name']); ?>')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No plates found. <a href="restaurant_add_plate.php">Add your first plate!</a></p>
    <?php endif; ?>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
