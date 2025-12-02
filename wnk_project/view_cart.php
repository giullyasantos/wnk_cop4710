<?php
require_once 'includes/config.php';
require_once 'includes/cart.php';
requireLogin();

$page_title = 'Shopping Cart';
$db = getDB();
$user_type = $_SESSION['user_type'];

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $plate_id = isset($_POST['plate_id']) ? intval($_POST['plate_id']) : 0;
    
    if ($_POST['action'] === 'update' && $plate_id > 0) {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        updateCartQuantity($plate_id, $quantity, $user_type);
    } elseif ($_POST['action'] === 'remove' && $plate_id > 0) {
        removeFromCart($plate_id, $user_type);
    }
    
    header('Location: view_cart.php');
    exit;
}

$cart_items = getCartItems();

include 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 2rem auto;">
    <h1 style="color: #fff;">🛒 Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">
                Your cart is empty
            </p>
            <?php if ($user_type === 'customer'): ?>
                <a href="customer_dashboard.php" class="btn btn-primary" style="background-color: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px;">
                    Browse Meals
                </a>
            <?php elseif ($user_type === 'donner'): ?>
                <a href="donner_dashboard.php" class="btn btn-primary" style="background-color: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px;">
                    Donate Meals
                </a>
            <?php elseif ($user_type === 'needy'): ?>
                <a href="needy_dashboard.php" class="btn btn-primary" style="background-color: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px;">
                    Claim Free Meals
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px;">
            <!-- Cart Items -->
            <div class="card">
                <h2 style="margin-top: 0;">Cart Items (<?php echo count($cart_items); ?>)</h2>
                
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                                <th style="padding: 12px; text-align: left;">Meal</th>
                                <?php if ($user_type !== 'needy'): ?>
                                <th style="padding: 12px; text-align: center;">Price</th>
                                <?php endif; ?>
                                <th style="padding: 12px; text-align: center;">Qty</th>
                                <?php if ($user_type !== 'needy'): ?>
                                <th style="padding: 12px; text-align: right;">Total</th>
                                <?php endif; ?>
                                <th style="padding: 12px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $key => $item): 
                                $stmt = $db->prepare("SELECT p.plate_name, p.price, r.restaurant_name FROM Plates p JOIN Restaurants r ON p.restaurant_id = r.restaurant_id WHERE p.plate_id = ?");
                                $stmt->bind_param("i", $item['plate_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    $plate = $result->fetch_assoc();
                                    $item_total = $plate['price'] * $item['quantity'];
                            ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;">
                                        <div>
                                            <strong><?php echo htmlspecialchars($plate['plate_name']); ?></strong>
                                            <br>
                                            <small style="color: #666;"><?php echo htmlspecialchars($plate['restaurant_name']); ?></small>
                                        </div>
                                    </td>
                                    <?php if ($user_type !== 'needy'): ?>
                                    <td style="padding: 12px; text-align: center;">
                                        $<?php echo number_format($plate['price'], 2); ?>
                                    </td>
                                    <?php endif; ?>
                                    <td style="padding: 12px; text-align: center;">
                                        <form method="POST" style="display: inline; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="plate_id" value="<?php echo $item['plate_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                            <button type="submit" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                    <?php if ($user_type !== 'needy'): ?>
                                    <td style="padding: 12px; text-align: right; font-weight: bold;">
                                        $<?php echo number_format($item_total, 2); ?>
                                    </td>
                                    <?php endif; ?>
                                    <td style="padding: 12px; text-align: center;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="plate_id" value="<?php echo $item['plate_id']; ?>">
                                            <button type="submit" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php 
                                }
                                $stmt->close();
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="card" style="background: #f8f9fa; height: fit-content;">
                <?php if ($user_type === 'needy'): ?>
                <h3 style="margin-top: 0;">Meals Summary</h3>
                <?php else: ?>
                <h3 style="margin-top: 0;">Order Summary</h3>
                <?php endif; ?>
                
                <?php if ($user_type !== 'needy'): ?>
                <?php 
                $subtotal = getCartTotal($db);
                $tax = $subtotal * 0.07; // 7% tax
                $total = $subtotal + $tax;
                ?>
                
                <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: white; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Subtotal:</span>
                        <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <span>Tax (7%):</span>
                        <strong>$<?php echo number_format($tax, 2); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; color: #007bff;">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: white; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: bold; color: #4caf50;">
                        <span>Total Meals:</span>
                        <span><?php echo count($cart_items); ?> meals</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php 
                $checkout_page = match($user_type) {
                    'customer' => 'cashout.php',
                    'donner' => 'donner_cashout.php',
                    'needy' => 'needy_checkout.php',
                    default => '#'
                };
                ?>
                
                <a href="<?php echo $checkout_page; ?>" class="btn btn-success" style="display: block; width: 100%; background-color: #28a745; color: white; text-decoration: none; padding: 12px; border-radius: 4px; text-align: center; font-weight: bold; margin-bottom: 10px;">
                    <?php if ($user_type === 'needy'): ?>
                    Confirm Claims
                    <?php else: ?>
                    Proceed to Checkout
                    <?php endif; ?>
                </a>
                
                <?php if ($user_type === 'customer'): ?>
                    <a href="customer_dashboard.php" class="btn btn-secondary" style="display: block; width: 100%; background-color: #6c757d; color: white; text-decoration: none; padding: 12px; border-radius: 4px; text-align: center; font-weight: bold;">
                        Continue Shopping
                    </a>
                <?php elseif ($user_type === 'donner'): ?>
                    <a href="donner_dashboard.php" class="btn btn-secondary" style="display: block; width: 100%; background-color: #6c757d; color: white; text-decoration: none; padding: 12px; border-radius: 4px; text-align: center; font-weight: bold;">
                        Continue Donating
                    </a>
                <?php elseif ($user_type === 'needy'): ?>
                    <a href="needy_dashboard.php" class="btn btn-secondary" style="display: block; width: 100%; background-color: #6c757d; color: white; text-decoration: none; padding: 12px; border-radius: 4px; text-align: center; font-weight: bold;">
                        Continue Claiming
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
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
</style>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
