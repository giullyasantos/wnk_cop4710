<?php
require_once 'includes/config.php';
requireRole('restaurant');

$page_title = 'Add New Plate';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $restaurant_id = $_SESSION['user_id'];
    
    $plate_name = sanitize($_POST['plate_name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $available_from = $_POST['available_from'];
    $available_until = $_POST['available_until'];
    
    // Validation
    if (empty($plate_name) || empty($price) || empty($quantity) || empty($available_from) || empty($available_until)) {
        $error = "Please fill in all required fields.";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0.";
    } elseif ($quantity <= 0) {
        $error = "Quantity must be greater than 0.";
    } elseif (strtotime($available_from) >= strtotime($available_until)) {
        $error = "End time must be after start time.";
    } else {
        // Insert plate
        $stmt = $db->prepare("INSERT INTO Plates (restaurant_id, plate_name, description, price, quantity_available, original_quantity, available_from, available_until, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)");
        $stmt->bind_param("issdiiss", $restaurant_id, $plate_name, $description, $price, $quantity, $quantity, $available_from, $available_until);        
        if ($stmt->execute()) {
            $success = "Plate added successfully! <a href='restaurant_manage_plates.php'>View all plates</a>";
            
            // Clear form
            $_POST = array();
        } else {
            $error = "Failed to add plate. Please try again.";
        }
        
        $stmt->close();
    }
    
    $db->close();
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Add New Surplus Plate</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="restaurant_add_plate.php">
        
        <div class="form-group">
            <label for="plate_name">Plate Name *</label>
            <input type="text" id="plate_name" name="plate_name" value="<?php echo isset($_POST['plate_name']) ? htmlspecialchars($_POST['plate_name']) : ''; ?>" required>
            <small>e.g., "Margherita Pizza", "Chicken Caesar Salad"</small>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            <small>Describe what's included in this plate</small>
        </div>
        
        <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
            <small>Discounted price for surplus food</small>
        </div>
        
        <div class="form-group">
            <label for="quantity">Quantity Available *</label>
            <input type="number" id="quantity" name="quantity" min="1" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>" required>
            <small>How many plates are available?</small>
        </div>
        
        <div class="form-group">
            <label for="available_from">Available From *</label>
            <input type="datetime-local" id="available_from" name="available_from" value="<?php echo isset($_POST['available_from']) ? htmlspecialchars($_POST['available_from']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="available_until">Available Until *</label>
            <input type="datetime-local" id="available_until" name="available_until" value="<?php echo isset($_POST['available_until']) ? htmlspecialchars($_POST['available_until']) : ''; ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Add Plate</button>
        <a href="restaurant_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
