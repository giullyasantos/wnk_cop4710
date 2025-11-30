<?php
require_once 'includes/config.php';
requireRole('customer');

$page_title = 'Buy Now';
$error = '';
$success = '';

$db = getDB();
$customer_id = $_SESSION['user_id'];

// Get plate details
if (!isset($_GET['plate_id'])) {
    header("Location: customer_dashboard.php");
    exit;
}

$plate_id = intval($_GET['plate_id']);

// Get plate information
$stmt = $db->prepare("SELECT p.plate_id, p.plate_name, p.price, p.description, 
                            p.quantity_available, p.available_from, p.available_until,
                            r.restaurant_name, r.phone_number
                     FROM Plates p
                     JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                     WHERE p.plate_id = ? AND p.is_active = 1 AND NOW() BETWEEN p.available_from AND p.available_until");
$stmt->bind_param("i", $plate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: customer_dashboard.php");
    exit;
}

$plate = $result->fetch_assoc();

// Calculate available quantity (accounting for existing reservations)
$stmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN cr.status != 'cancelled' THEN cr.quantity ELSE 0 END), 0) as reserved_qty
                     FROM Customer_Reservations cr
                     WHERE cr.plate_id = ?");
$stmt->bind_param("i", $plate_id);
$stmt->execute();
$reserved = $stmt->get_result()->fetch_assoc();
$available_qty = $plate['quantity_available'] - $reserved['reserved_qty'];

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    $quantity = intval($_POST['quantity']);
    
    if ($quantity <= 0) {
        $error = "Please enter a valid quantity.";
    } elseif ($quantity > $available_qty) {
        $error = "Insufficient quantity available. Only " . $available_qty . " remaining.";
    } else {
        $total_amount = $quantity * $plate['price'];
        
        // Create reservation
        $stmt = $db->prepare("INSERT INTO Customer_Reservations (customer_id, plate_id, quantity, total_amount, status)
                            VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiid", $customer_id, $plate_id, $quantity, $total_amount);
        
        if ($stmt->execute()) {
            $success = "Purchase successful! Your order is pending confirmation from the restaurant.";
            $plate['quantity_available'] -= $quantity;
            $available_qty -= $quantity;
        } else {
            $error = "Failed to process purchase. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 2rem auto;">
    <h1><?php echo htmlspecialchars($plate['plate_name']); ?></h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <a href="my_orders.php" class="btn btn-primary" style="margin-top: 1rem;">View My Orders</a>
    <?php else: ?>
        <div style="background: var(--color-bg-secondary); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem;">
                <strong>Restaurant:</strong> <?php echo htmlspecialchars($plate['restaurant_name']); ?><br>
                <small class="text-muted">Phone: <?php echo htmlspecialchars($plate['phone_number']); ?></small>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Description:</strong><br>
                <?php echo htmlspecialchars($plate['description']); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Price per meal:</strong> $<?php echo number_format($plate['price'], 2); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Available until:</strong> 
                <?php echo date('M d, Y H:i', strtotime($plate['available_until'])); ?>
            </div>
            
            <div style="padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid var(--color-success);">
                <strong style="color: var(--color-success);">✓ <?php echo $available_qty; ?> Meals Available</strong>
            </div>
        </div>
        
        <form method="POST" style="margin-top: 2rem;">
            <div style="margin-bottom: 1.5rem;">
                <label for="quantity"><strong>Quantity:</strong></label><br>
                <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $available_qty; ?>" 
                       value="1" required style="width: 100%; padding: 0.75rem; margin-top: 0.5rem; border: 1px solid var(--color-border); border-radius: 6px;">
                <small style="color: var(--color-text-secondary);">Maximum: <?php echo $available_qty; ?></small>
            </div>
            
            <div style="background: var(--color-accent-light); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Total:</span>
                    <strong id="total-price" style="font-size: 1.25rem;">$<?php echo number_format($plate['price'], 2); ?></strong>
                </div>
            </div>
            
            <button type="submit" name="purchase" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">
                Complete Purchase
            </button>
            
            <a href="customer_dashboard.php" class="btn btn-secondary" style="width: 100%; padding: 1rem; margin-top: 0.5rem; text-align: center;">
                Back to Browse
            </a>
        </form>
        
        <script>
            const pricePerMeal = <?php echo $plate['price']; ?>;
            const quantityInput = document.getElementById('quantity');
            const totalPrice = document.getElementById('total-price');
            
            quantityInput.addEventListener('change', function() {
                const qty = parseInt(this.value) || 1;
                const total = (qty * pricePerMeal).toFixed(2);
                totalPrice.textContent = '$' + parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        </script>
    <?php endif; ?>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
