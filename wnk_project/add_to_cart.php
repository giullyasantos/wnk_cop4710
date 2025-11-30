<?php
require_once 'includes/config.php';
require_once 'includes/cart.php';
requireLogin();

$db = getDB();

// Verify plate exists and is available
if (!isset($_POST['plate_id']) || !is_numeric($_POST['plate_id'])) {
    die("Invalid plate ID");
}

$plate_id = intval($_POST['plate_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Check plate availability
$stmt = $db->prepare("SELECT p.quantity_available, p.plate_id
                     FROM Plates p
                     WHERE p.plate_id = ? AND p.is_active = 1 
                       AND NOW() BETWEEN p.available_from AND p.available_until");
$stmt->bind_param("i", $plate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Plate not found or no longer available");
}

$plate = $result->fetch_assoc();
$stmt->close();

// Validate quantity
if ($quantity <= 0 || $quantity > 10) {
    die("Invalid quantity");
}

// For needy users, check 2-meal/day limit
if ($_SESSION['user_type'] === 'needy') {
    // Count current daily claims (including items already in cart)
    $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as total_claimed 
                         FROM Needy_Claim 
                         WHERE needy_id = ? AND DATE(claimed_at) = CURDATE()");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $daily_claims = $result->fetch_assoc()['total_claimed'];
    $stmt->close();
    
    // Also count items already in cart for today
    $cart_total_qty = 0;
    foreach (getCartItems() as $item) {
        $cart_total_qty += $item['quantity'];
    }
    
    // Check if adding more items would exceed 2-meal limit
    if ($daily_claims + $cart_total_qty + $quantity > 2) {
        $_SESSION['cart_error'] = "You can only claim up to 2 meals per day. You have already claimed/cart " . ($daily_claims + $cart_total_qty) . " meal(s) today.";
        header('Location: needy_dashboard.php');
        exit;
    }
}

// Add to cart
addToCart($plate_id, $quantity, $_SESSION['user_type']);

// Redirect back with success message
$_SESSION['cart_message'] = "Added {$quantity} item(s) to cart!";
header('Location: view_cart.php');
$db->close();
exit;
?>
