<?php
require_once 'includes/config.php';
requireRole('donner');

$page_title = 'Donate a Meal';
$db = getDB();
$donner_id = $_SESSION['user_id'];

// Get plate information
if (!isset($_GET['plate_id']) && !isset($_POST['plate_id'])) {
    die("Invalid plate ID");
}

$plate_id = intval($_GET['plate_id'] ?? $_POST['plate_id'] ?? 0);

if ($plate_id === 0 || !is_numeric($plate_id)) {
    die("Invalid plate ID");
}

$query = "SELECT p.plate_id, p.plate_name, p.price, p.description, 
                 p.quantity_available, r.restaurant_name, r.restaurant_id,
                 (p.quantity_available - COALESCE(SUM(CASE WHEN cr.status != 'cancelled' THEN cr.quantity ELSE 0 END), 0)) as available_count
          FROM Plates p
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          LEFT JOIN Customer_Reservations cr ON p.plate_id = cr.plate_id
          WHERE p.plate_id = ? AND p.is_active = 1 
            AND NOW() BETWEEN p.available_from AND p.available_until
          GROUP BY p.plate_id";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $plate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Plate not found or no longer available");
}

$plate = $result->fetch_assoc();
$stmt->close();

// Handle donation submission
$donation_success = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $custom_amount = isset($_POST['custom_amount']) ? floatval($_POST['custom_amount']) : 0;
    
    // Validate quantity
    if ($quantity <= 0 || $quantity > $plate['available_count']) {
        $error_message = "Invalid quantity. Available: " . $plate['available_count'];
    } elseif ($custom_amount <= 0) {
        $error_message = "Amount must be greater than $0";
    } else {
        // Calculate total amount
        $total_amount = $custom_amount * $quantity;
        
        // Insert donation record
        $stmt = $db->prepare("INSERT INTO Donations (donner_id, plate_id, quantity_available, original_quantity, total_amount, donated_at)
                             VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiiid", $donner_id, $plate_id, $quantity, $quantity, $total_amount);
        
        if ($stmt->execute()) {
            $donation_id = $stmt->insert_id;
            $stmt->close();
            
            // Decrease the plate's available quantity
            $stmt = $db->prepare("UPDATE Plates SET quantity_available = quantity_available - ? WHERE plate_id = ?");
            $stmt->bind_param("ii", $quantity, $plate_id);
            $stmt->execute();
            $stmt->close();
            
            $donation_success = true;
        } else {
            $error_message = "Failed to process donation. Please try again.";
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <?php if ($donation_success): ?>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px; margin-bottom: 20px; color: #155724;">
            <strong>✓ Success!</strong> Your donation of <?php echo $quantity; ?> meal(s) has been created. 
            Donation ID: #<?php echo $donation_id; ?>
            <div style="margin-top: 15px;">
                <a href="donner_dashboard.php" class="btn btn-success" style="margin-right: 10px;">Back to Donate</a>
                <a href="donner_cashout.php" class="btn btn-info" style="background-color: #17a2b8;">View Donations</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <h1>Donate a Meal</h1>
            
            <?php if ($error_message): ?>
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin-bottom: 20px; color: #721c24;">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Meal Information -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                <h2 style="margin-top: 0;">🍽️ <?php echo htmlspecialchars($plate['plate_name']); ?></h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0;">
                    <div>
                        <p style="color: #666; margin: 0; font-size: 0.9rem;">RESTAURANT</p>
                        <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold;">
                            <?php echo htmlspecialchars($plate['restaurant_name']); ?>
                        </p>
                    </div>
                    <div>
                        <p style="color: #666; margin: 0; font-size: 0.9rem;">PRICE PER MEAL</p>
                        <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold; color: #ff6b6b;">
                            $<?php echo number_format($plate['price'], 2); ?>
                        </p>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">DESCRIPTION</p>
                    <p style="margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($plate['description']); ?>
                    </p>
                </div>
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">AVAILABLE TO DONATE</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold; color: #28a745;">
                        ✓ <?php echo $plate['available_count']; ?> meals available
                    </p>
                </div>
            </div>
            
            <!-- Donation Form -->
            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label for="quantity" style="display: block; margin-bottom: 8px; font-weight: bold;">
                        How many meals do you want to donate?
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $plate['available_count']; ?>" 
                               value="1" required style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                        <span style="color: #666; white-space: nowrap;">of <?php echo $plate['available_count']; ?> available</span>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        You can donate between 1 and <?php echo $plate['available_count']; ?> meals
                    </small>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <label for="custom_amount" style="display: block; margin-bottom: 8px; font-weight: bold;">
                        Amount per meal to donate
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span style="font-size: 1.2rem;">$</span>
                        <input type="number" id="custom_amount" name="custom_amount" 
                               min="0.01" step="0.01" value="<?php echo number_format($plate['price'], 2); ?>" 
                               required style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Restaurant's listed price: $<?php echo number_format($plate['price'], 2); ?> (you can donate more!)
                    </small>
                </div>
                
                <!-- Total calculation display -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: bold;">Total Donation:</span>
                        <span style="font-size: 1.5rem; font-weight: bold; color: #ff6b6b;">
                            $<span id="total-amount">0.00</span>
                        </span>
                    </div>
                    <small style="color: #666; display: block; margin-top: 8px;">
                        💝 This donation will help someone in need enjoy a meal from <?php echo htmlspecialchars($plate['restaurant_name']); ?>
                    </small>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="flex: 1; background-color: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight: bold;" onclick="return confirmDonation();">
                        ✓ Confirm Donation
                    </button>
                    <a href="donner_dashboard.php" class="btn btn-secondary" style="flex: 1; background-color: #6c757d; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight: bold; text-decoration: none; text-align: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn {
    transition: background 0.2s;
}

.btn-success:hover {
    background-color: #218838 !important;
}

.btn-secondary:hover {
    background-color: #5a6268 !important;
}

#quantity, #custom_amount {
    font-family: monospace;
}
</style>

<script>
// Update total calculation in real-time
function updateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const amount = parseFloat(document.getElementById('custom_amount').value) || 0;
    const total = (quantity * amount).toFixed(2);
    document.getElementById('total-amount').textContent = total;
}

function confirmDonation() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const amount = parseFloat(document.getElementById('custom_amount').value) || 0;
    const total = (quantity * amount).toFixed(2);
    const mealName = "<?php echo htmlspecialchars($plate['plate_name']); ?>";
    
    return confirm(`Confirm donation of ${quantity} meal(s) worth $${total} (${mealName})?`);
}

document.getElementById('quantity').addEventListener('input', updateTotal);
document.getElementById('custom_amount').addEventListener('input', updateTotal);

// Initialize total on page load
updateTotal();
</script>

<?php include 'includes/footer.php'; ?>
