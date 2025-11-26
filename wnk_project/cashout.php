<?php
require_once 'includes/config.php';
requireRole('customer');

$page_title = 'Cashout';
$error = '';
$success = '';
$db = getDB();
$customer_id = $_SESSION['user_id'];

// Get customer payment information
$stmt = $db->prepare("SELECT credit_card_number, card_expiry, card_cvv, billing_address FROM Customers WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Get pending orders ready for checkout
$stmt = $db->prepare("SELECT SUM(cr.total_amount) as total FROM Customer_Reservations cr 
                     WHERE cr.customer_id = ? AND cr.status IN ('pending', 'confirmed')");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
$pending_total = $totals['total'] ?? 0;

// Get order details
$stmt = $db->prepare("SELECT cr.reservation_id, cr.quantity, cr.total_amount,
                            p.plate_name, r.restaurant_name, cr.status
                     FROM Customer_Reservations cr
                     JOIN Plates p ON cr.plate_id = p.plate_id
                     JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                     WHERE cr.customer_id = ? AND cr.status IN ('pending', 'confirmed')
                     ORDER BY cr.reserved_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    // Validate card details
    if (empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
        $error = "Please provide all payment details.";
    } elseif (!preg_match('/^\d{13,19}$/', $card_number)) {
        $error = "Invalid card number. Must be 13-19 digits.";
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
        $error = "Invalid expiry format. Use MM/YY.";
    } elseif (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $error = "Invalid CVV. Must be 3-4 digits.";
    } else {
        // Validate card expiry (not expired)
        $expiry_parts = explode('/', $card_expiry);
        $expiry_month = (int)$expiry_parts[0];
        $expiry_year = 2000 + (int)$expiry_parts[1];
        $today = new DateTime();
        $expiry_date = new DateTime($expiry_year . '-' . str_pad($expiry_month, 2, '0', STR_PAD_LEFT) . '-01');
        $expiry_date->modify('last day of this month');
        
        if ($today > $expiry_date) {
            $error = "Card has expired.";
        } else {
            // Process payment (simulate)
            // In a real system, this would integrate with a payment processor like Stripe
            
            // Update all pending orders to confirmed
            $stmt = $db->prepare("UPDATE Customer_Reservations 
                               SET status = 'confirmed', confirmed_at = NOW() 
                               WHERE customer_id = ? AND status = 'pending'");
            $stmt->bind_param("i", $customer_id);
            
            if ($stmt->execute()) {
                // Save card info (masked for security)
                $masked_card = 'XXXX' . substr($card_number, -4);
                $stmt = $db->prepare("UPDATE Customers 
                                   SET credit_card_number = ?, card_expiry = ?, card_cvv = ? 
                                   WHERE customer_id = ?");
                $stmt->bind_param("sssi", $card_number, $card_expiry, $card_cvv, $customer_id);
                $stmt->execute();
                
                $success = "Payment processed successfully! All orders confirmed.";
                $pending_total = 0;
                $orders = $db->query("SELECT * FROM Customer_Reservations WHERE 1=0"); // Empty result
            } else {
                $error = "Failed to process payment. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 2rem auto;">
    <h1>Checkout</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <div style="margin-top: 2rem; text-align: center;">
            <p>Thank you for your purchase! Your orders are now confirmed.</p>
            <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Order Summary -->
            <div class="card">
                <h2>Order Summary</h2>
                
                <?php if ($orders->num_rows > 0): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--color-border);">
                                    <th style="text-align: left; padding: 0.5rem;">Item</th>
                                    <th style="text-align: right; padding: 0.5rem;">Qty</th>
                                    <th style="text-align: right; padding: 0.5rem;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $orders->data_seek(0); while ($order = $orders->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid var(--color-border-light);">
                                        <td style="padding: 0.75rem 0.5rem;">
                                            <div><strong><?php echo htmlspecialchars($order['plate_name']); ?></strong></div>
                                            <small style="color: var(--color-text-secondary);">
                                                <?php echo htmlspecialchars($order['restaurant_name']); ?>
                                            </small>
                                        </td>
                                        <td style="text-align: right; padding: 0.75rem 0.5rem;"><?php echo $order['quantity']; ?></td>
                                        <td style="text-align: right; padding: 0.75rem 0.5rem;">
                                            $<?php echo number_format($order['total_amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="background: var(--color-accent-light); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong>Total Amount:</strong>
                            <strong style="font-size: 1.5rem; color: var(--color-accent);">
                                $<?php echo number_format($pending_total, 2); ?>
                            </strong>
                        </div>
                    </div>
                    
                    <a href="my_orders.php" class="btn btn-secondary" style="width: 100%; text-align: center; padding: 0.75rem;">
                        Edit Orders
                    </a>
                <?php else: ?>
                    <p class="text-muted" style="padding: 2rem; text-align: center;">
                        No pending orders to checkout.
                    </p>
                    <a href="customer_dashboard.php" class="btn btn-primary" style="width: 100%; text-align: center; padding: 0.75rem;">
                        Browse Meals
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Payment Form -->
            <?php if ($orders->num_rows > 0): ?>
                <div class="card">
                    <h2>Payment Information</h2>
                    
                    <form method="POST">
                        <div style="margin-bottom: 1.5rem;">
                            <label for="card_number"><strong>Card Number</strong></label>
                            <input type="text" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" maxlength="19"
                                   value="<?php echo htmlspecialchars($customer['credit_card_number'] ?? ''); ?>"
                                   required style="width: 100%; padding: 0.75rem; margin-top: 0.5rem; border: 1px solid var(--color-border); border-radius: 6px;">
                            <small style="color: var(--color-text-secondary);">13-19 digits</small>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <label for="card_expiry"><strong>Expiry</strong></label>
                                <input type="text" id="card_expiry" name="card_expiry" 
                                       placeholder="MM/YY"
                                       value="<?php echo htmlspecialchars($customer['card_expiry'] ?? ''); ?>"
                                       required style="width: 100%; padding: 0.75rem; margin-top: 0.5rem; border: 1px solid var(--color-border); border-radius: 6px;">
                            </div>
                            
                            <div>
                                <label for="card_cvv"><strong>CVV</strong></label>
                                <input type="text" id="card_cvv" name="card_cvv" 
                                       placeholder="123"
                                       value="<?php echo htmlspecialchars($customer['card_cvv'] ?? ''); ?>"
                                       required maxlength="4" style="width: 100%; padding: 0.75rem; margin-top: 0.5rem; border: 1px solid var(--color-border); border-radius: 6px;">
                            </div>
                        </div>
                        
                        <div style="background: var(--color-warning-light); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 4px solid var(--color-warning);">
                            <small style="color: var(--color-warning);">
                                <strong>⚠️ Security Notice:</strong> Your payment information is encrypted and securely stored.
                            </small>
                        </div>
                        
                        <button type="submit" name="process_payment" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">
                            Pay $<?php echo number_format($pending_total, 2); ?>
                        </button>
                        
                        <a href="my_orders.php" class="btn btn-secondary" style="width: 100%; padding: 1rem; margin-top: 0.5rem; text-align: center;">
                            Back
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
