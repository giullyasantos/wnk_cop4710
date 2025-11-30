<?php
/**
 * Cart management helper functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Initialize cart if it doesn't exist
 */
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart
 */
function addToCart($plate_id, $quantity = 1, $user_type = 'customer') {
    initCart();
    
    $key = $plate_id . '_' . $user_type;
    
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'plate_id' => $plate_id,
            'quantity' => $quantity,
            'user_type' => $user_type,
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * Remove item from cart
 */
function removeFromCart($plate_id, $user_type = 'customer') {
    initCart();
    $key = $plate_id . '_' . $user_type;
    unset($_SESSION['cart'][$key]);
}

/**
 * Update quantity in cart
 */
function updateCartQuantity($plate_id, $quantity, $user_type = 'customer') {
    initCart();
    $key = $plate_id . '_' . $user_type;
    
    if ($quantity <= 0) {
        removeFromCart($plate_id, $user_type);
    } else {
        $_SESSION['cart'][$key]['quantity'] = $quantity;
    }
}

/**
 * Get cart items
 */
function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Get cart count
 */
function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Get cart total
 */
function getCartTotal($db) {
    initCart();
    $total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $db->prepare("SELECT price FROM Plates WHERE plate_id = ?");
        $stmt->bind_param("i", $item['plate_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $total += ($row['price'] * $item['quantity']);
        }
        $stmt->close();
    }
    
    return $total;
}
?>
