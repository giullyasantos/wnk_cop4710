<?php
require_once 'includes/config.php';
require_once 'includes/cart.php';
requireRole('needy');

$page_title = 'Confirm Claims';
$error = '';
$success = '';
$db = getDB();
$needy_id = $_SESSION['user_id'];

// Get cart items
$cart_items = getCartItems();

if (empty($cart_items)) {
    // No items in cart, redirect back
    header('Location: needy_dashboard.php');
    exit;
}

// Calculate total meals and check daily limit
$total_qty = 0;
$cart_details = [];

foreach ($cart_items as $item) {
    $stmt = $db->prepare("SELECT p.plate_name, p.description, r.restaurant_name FROM Plates p 
                         JOIN Restaurants r ON p.restaurant_id = r.restaurant_id WHERE p.plate_id = ?");
    $stmt->bind_param("i", $item['plate_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $plate = $result->fetch_assoc();
        $total_qty += $item['quantity'];
        $cart_details[] = [
            'plate_id' => $item['plate_id'],
            'plate_name' => $plate['plate_name'],
            'description' => $plate['description'],
            'restaurant_name' => $plate['restaurant_name'],
            'quantity' => $item['quantity']
        ];
    }
    $stmt->close();
}

// Check daily limit
$stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as total_claimed 
                     FROM Needy_Claim 
                     WHERE needy_id = ? AND DATE(claimed_at) = CURDATE()");
$stmt->bind_param("i", $needy_id);
$stmt->execute();
$result = $stmt->get_result();
$daily_claims = $result->fetch_assoc()['total_claimed'];
$stmt->close();

if ($daily_claims + $total_qty > 2) {
    $error = "Cannot claim these meals. You have already claimed " . $daily_claims . " meal(s) today and can only claim 2 per day. Requesting " . $total_qty . " meal(s).";
}

// Handle claim confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_claims']) && empty($error)) {
    $all_success = true;
    
    // For each item in cart, find an available donation and create a claim
    foreach ($cart_details as $item) {
        $qty_needed = $item['quantity'];
        
        // Get available donations for this plate that have quantity
        $stmt = $db->prepare("SELECT d.donation_id, 
                                     (d.quantity_available - COALESCE(SUM(CASE WHEN nc.status != 'cancelled' THEN nc.quantity ELSE 0 END), 0)) as available
                              FROM Donations d
                              LEFT JOIN Needy_Claim nc ON d.donation_id = nc.donation_id
                              WHERE d.plate_id = ? AND d.quantity_available > 0
                              GROUP BY d.donation_id
                              HAVING available > 0
                              ORDER BY d.donated_at ASC");
        $stmt->bind_param("i", $item['plate_id']);
        $stmt->execute();
        $donations = $stmt->get_result();
        $stmt->close();
        
        // Claim from donations
        while ($qty_needed > 0 && $donation = $donations->fetch_assoc()) {
            $claim_qty = min($qty_needed, $donation['available']);
            
            $stmt = $db->prepare("INSERT INTO Needy_Claim (needy_id, donation_id, quantity, status, claimed_at) 
                                VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iii", $needy_id, $donation['donation_id'], $claim_qty);
            
            if (!$stmt->execute()) {
                $all_success = false;
                $error = "Failed to claim " . htmlspecialchars($item['plate_name']);
                break 2;
            }
            $stmt->close();
            
            $qty_needed -= $claim_qty;
        }
        
        if ($qty_needed > 0) {
            $all_success = false;
            $error = "Could not find enough available meals for " . htmlspecialchars($item['plate_name']);
            break;
        }
    }
    
    if ($all_success) {
        // Clear the cart
        clearCart();
        
        $success = "Claims confirmed successfully! You've claimed " . $total_qty . " meal(s).";
        $cart_items = [];
        $cart_details = [];
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 2rem auto;">
    <h1 style="color: #fff;">Confirm Your Claims</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            ⚠️ <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            ✓ <?php echo $success; ?>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <p style="color: #fff;">Your claims have been submitted and are pending confirmation.</p>
            <a href="my_claims.php" class="btn btn-primary" style="background-color: #28a745; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px;">
                View My Claims
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Claims Summary -->
            <div class="card">
                <h2>Claims Summary</h2>
                
                <?php if (!empty($cart_details)): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($cart_details as $item): ?>
                                <div style="padding: 1rem; background: #f9f9f9; border-radius: 4px; border-left: 4px solid #4caf50;">
                                    <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($item['plate_name']); ?></div>
                                    <small style="color: #666;"><?php echo htmlspecialchars($item['restaurant_name']); ?></small>
                                    <div style="margin-top: 0.5rem; font-size: 1.1rem; color: #4caf50; font-weight: bold;">
                                        Qty: <?php echo $item['quantity']; ?> meal<?php echo $item['quantity'] > 1 ? 's' : ''; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #4caf50;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: bold; color: #2e7d32;">
                            <span>Total Meals:</span>
                            <span><?php echo $total_qty; ?> meal<?php echo $total_qty > 1 ? 's' : ''; ?></span>
                        </div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #1b5e20;">
                            Daily limit: <?php echo (2 - $daily_claims); ?> meal<?php echo (2 - $daily_claims) > 1 ? 's' : ''; ?> remaining today
                        </div>
                    </div>
                    
                    <a href="view_cart.php" class="btn btn-secondary" style="width: 100%; text-align: center; padding: 0.75rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                        Back to Cart
                    </a>
                <?php else: ?>
                    <p class="text-muted" style="padding: 2rem; text-align: center;">
                        No items in cart.
                    </p>
                    <a href="needy_dashboard.php" class="btn btn-primary" style="width: 100%; text-align: center; padding: 0.75rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                        Browse Meals
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Confirmation -->
            <?php if (!empty($cart_details) && empty($error)): ?>
                <div class="card">
                    <h2>Ready to Claim?</h2>
                    
                    <div style="background: #fff9e6; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 4px solid #ffc107;">
                        <p style="color: #856404; margin: 0;">
                            <strong>📋 Important:</strong> By confirming, you are claiming these free meals from our donation pool. 
                            The restaurant will contact you with pickup or delivery details.
                        </p>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="confirm_claims" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-bottom: 0.5rem;">
                            ✓ Confirm Claims (<?php echo $total_qty; ?> meal<?php echo $total_qty > 1 ? 's' : ''; ?>)
                        </button>
                        
                        <a href="view_cart.php" class="btn btn-secondary" style="width: 100%; padding: 1rem; text-align: center; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                            Cancel
                        </a>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
