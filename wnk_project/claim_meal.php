<?php
require_once 'includes/config.php';
requireRole('needy');

$page_title = 'Claim a Meal';
$db = getDB();
$needy_id = $_SESSION['user_id'];

// Get donation information
if (!isset($_GET['donation_id']) || !is_numeric($_GET['donation_id'])) {
    die("Invalid donation ID");
}

$donation_id = intval($_GET['donation_id']);

$query = "SELECT d.donation_id, d.quantity_available, d.total_amount,
                 p.plate_id, p.plate_name, p.description, p.price,
                 r.restaurant_name, r.restaurant_id,
                 (d.quantity_available - COALESCE(SUM(CASE WHEN nc.status != 'cancelled' THEN nc.quantity ELSE 0 END), 0)) as available_count
          FROM Donations d
          JOIN Plates p ON d.plate_id = p.plate_id
          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
          LEFT JOIN Needy_Claim nc ON d.donation_id = nc.donation_id
          WHERE d.donation_id = ? AND d.quantity_available > 0
          GROUP BY d.donation_id";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Donation not found or no longer available");
}

$donation = $result->fetch_assoc();
$stmt->close();

// Check how many meals the needy user has claimed today
$stmt = $db->prepare("SELECT COALESCE(SUM(nc.quantity), 0) as claimed_today
                     FROM Needy_Claim nc
                     WHERE nc.needy_id = ? AND DATE(nc.claimed_at) = CURDATE()");
$stmt->bind_param("i", $needy_id);
$stmt->execute();
$today_claims = $stmt->get_result()->fetch_assoc();
$stmt->close();

$claimed_today = $today_claims['claimed_today'] ?? 0;
$daily_limit = 2;
$can_claim_today = max(0, $daily_limit - $claimed_today);

// Handle claim submission
$claim_success = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate quantity
    if ($quantity <= 0 || $quantity > $donation['available_count']) {
        $error_message = "Invalid quantity. Available: " . $donation['available_count'];
    } elseif ($quantity > $can_claim_today) {
        $error_message = "You can only claim " . $daily_limit . " meals per day. You have already claimed " . $claimed_today . " meal(s) today. You can claim " . $can_claim_today . " more.";
    } else {
        // Insert claim record
        $stmt = $db->prepare("INSERT INTO Needy_Claim (needy_id, donation_id, quantity, status, claimed_at)
                             VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iii", $needy_id, $donation_id, $quantity);
        
        if ($stmt->execute()) {
            $claim_id = $stmt->insert_id;
            $stmt->close();
            
            // Decrease the donation's available quantity
            $stmt = $db->prepare("UPDATE Donations SET quantity_available = quantity_available - ? WHERE donation_id = ?");
            $stmt->bind_param("ii", $quantity, $donation_id);
            $stmt->execute();
            $stmt->close();
            
            $claim_success = true;
        } else {
            $error_message = "Failed to claim meal. Please try again.";
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <?php if ($claim_success): ?>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px; margin-bottom: 20px; color: #155724;">
            <strong>✓ Success!</strong> You have claimed <?php echo $quantity; ?> meal(s)!
            <div style="margin-top: 15px;">
                <a href="needy_dashboard.php" class="btn btn-success" style="margin-right: 10px;">Back to Meals</a>
                <a href="my_claims.php" class="btn btn-info" style="background-color: #17a2b8;">View My Claims</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <h1>Claim This Meal</h1>
            
            <?php if ($error_message): ?>
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin-bottom: 20px; color: #721c24;">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Meal Information -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                <h2 style="margin-top: 0;">🎁 <?php echo htmlspecialchars($donation['plate_name']); ?></h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0;">
                    <div>
                        <p style="color: #666; margin: 0; font-size: 0.9rem;">RESTAURANT</p>
                        <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold;">
                            <?php echo htmlspecialchars($donation['restaurant_name']); ?>
                        </p>
                    </div>
                    <div>
                        <p style="color: #666; margin: 0; font-size: 0.9rem;">VALUE</p>
                        <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold; color: #28a745;">
                            $<?php echo number_format($donation['total_amount'] / $donation['quantity_available'], 2); ?> each
                        </p>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">DESCRIPTION</p>
                    <p style="margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($donation['description']); ?>
                    </p>
                </div>
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">AVAILABLE TO CLAIM</p>
                    <p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold; color: #28a745;">
                        ✓ <?php echo $donation['available_count']; ?> meals available
                    </p>
                </div>
            </div>
            
            <!-- Claim Form -->
            <form method="POST">
                <div style="margin-bottom: 30px;">
                    <label for="quantity" style="display: block; margin-bottom: 8px; font-weight: bold;">
                        How many meals do you want to claim?
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" id="quantity" name="quantity" min="1" max="<?php echo min($donation['available_count'], $can_claim_today); ?>" 
                               value="1" required style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                        <span style="color: #666; white-space: nowrap;">of <?php echo $donation['available_count']; ?> available</span>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        You can claim between 1 and <?php echo min($donation['available_count'], $can_claim_today); ?> meals today
                    </small>
                </div>
                
                <?php if ($claimed_today > 0): ?>
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #856404;">
                        <strong>Daily Limit:</strong> You have claimed <?php echo $claimed_today; ?> of <?php echo $daily_limit; ?> meals today. You can claim <?php echo $can_claim_today; ?> more.
                    </p>
                </div>
                <?php endif; ?>
                
                <div style="background: #e8f5e9; border: 1px solid #4caf50; border-radius: 4px; padding: 15px; margin-bottom: 30px;">
                    <p style="margin: 0; color: #2e7d32; font-weight: bold;">
                        ✓ This meal is completely FREE thanks to a generous donor!
                    </p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="flex: 1; background-color: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight: bold;" onclick="return confirmClaim();">
                        ✓ Claim Meal
                    </button>
                    <a href="needy_dashboard.php" class="btn btn-secondary" style="flex: 1; background-color: #6c757d; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight: bold; text-decoration: none; text-align: center;">
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

.btn-info:hover {
    background-color: #138496 !important;
}
</style>

<script>
function confirmClaim() {
    const quantity = document.getElementById('quantity').value;
    return confirm(`Claim ${quantity} meal(s)?`);
}
</script>

<?php include 'includes/footer.php'; ?>
