<?php
require_once 'includes/config.php';
requireLogin();

$page_title = 'Edit Profile';
$error = '';
$success = '';

$db = getDB();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get current user data
$stmt = $db->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get role-specific data
$role_data = null;
switch($user_type) {
    case 'restaurant':
        $stmt = $db->prepare("SELECT * FROM Restaurants WHERE restaurant_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
        break;
        
    case 'customer':
        $stmt = $db->prepare("SELECT * FROM Customers WHERE customer_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
        break;
        
    case 'donner':
        $stmt = $db->prepare("SELECT * FROM Donners WHERE donner_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
        break;
        
    case 'needy':
        $stmt = $db->prepare("SELECT * FROM Needy WHERE needy_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
        break;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $street = sanitize($_POST['street']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    
    // Update password if provided
    $update_password = false;
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            if (strlen($_POST['new_password']) >= 6) {
                $update_password = true;
                $new_password_hash = hashPassword($_POST['new_password']);
            } else {
                $error = "Password must be at least 6 characters.";
            }
        } else {
            $error = "Passwords do not match.";
        }
    }
    
    if (empty($error)) {
        $db->begin_transaction();
        
        try {
            // Update Users table
            if ($update_password) {
                $stmt = $db->prepare("UPDATE Users SET first_name = ?, last_name = ?, street = ?, city = ?, state = ?, zip_code = ?, password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("sssssssi", $first_name, $last_name, $street, $city, $state, $zip_code, $new_password_hash, $user_id);
            } else {
                $stmt = $db->prepare("UPDATE Users SET first_name = ?, last_name = ?, street = ?, city = ?, state = ?, zip_code = ? WHERE user_id = ?");
                $stmt->bind_param("ssssssi", $first_name, $last_name, $street, $city, $state, $zip_code, $user_id);
            }
            $stmt->execute();
            
            // Update role-specific table
            switch($user_type) {
                case 'restaurant':
                    $phone_number = sanitize($_POST['phone_number']);
                    $restaurant_name = sanitize($_POST['restaurant_name']);
                    $description = sanitize($_POST['description']);
                    $cuisine_type = sanitize($_POST['cuisine_type']);
                    
                    $stmt = $db->prepare("UPDATE Restaurants SET phone_number = ?, restaurant_name = ?, description = ?, cuisine_type = ? WHERE restaurant_id = ?");
                    $stmt->bind_param("ssssi", $phone_number, $restaurant_name, $description, $cuisine_type, $user_id);
                    $stmt->execute();
                    break;
                    
                case 'customer':
                    $phone_number = sanitize($_POST['phone_number']);
                    $credit_card = sanitize($_POST['credit_card_number']);
                    $expiry = sanitize($_POST['card_expiry']);
                    $cvv = sanitize($_POST['card_cvv']);
                    $billing_address = sanitize($_POST['billing_address']);
                    
                    $stmt = $db->prepare("UPDATE Customers SET phone_number = ?, credit_card_number = ?, card_expiry = ?, card_cvv = ?, billing_address = ? WHERE customer_id = ?");
                    $stmt->bind_param("sssssi", $phone_number, $credit_card, $expiry, $cvv, $billing_address, $user_id);
                    $stmt->execute();
                    break;
                    
                case 'donner':
                    $phone_number = sanitize($_POST['phone_number']);
                    $credit_card = sanitize($_POST['credit_card_number']);
                    $expiry = sanitize($_POST['card_expiry']);
                    $cvv = sanitize($_POST['card_cvv']);
                    $billing_address = sanitize($_POST['billing_address']);
                    
                    $stmt = $db->prepare("UPDATE Donners SET phone_number = ?, credit_card_number = ?, card_expiry = ?, card_cvv = ?, billing_address = ? WHERE donner_id = ?");
                    $stmt->bind_param("sssssi", $phone_number, $credit_card, $expiry, $cvv, $billing_address, $user_id);
                    $stmt->execute();
                    break;
                    
                case 'needy':
                    $phone_number = !empty($_POST['phone_number']) ? sanitize($_POST['phone_number']) : null;
                    
                    $stmt = $db->prepare("UPDATE Needy SET phone_number = ? WHERE needy_id = ?");
                    $stmt->bind_param("si", $phone_number, $user_id);
                    $stmt->execute();
                    break;
            }
            
            $db->commit();
            
            // Update session
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            
            $success = "Profile updated successfully!";
            
            // Refresh data
            $stmt = $db->prepare("SELECT * FROM Users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            // Refresh role data
            switch($user_type) {
                case 'restaurant':
                    $stmt = $db->prepare("SELECT * FROM Restaurants WHERE restaurant_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $role_data = $stmt->get_result()->fetch_assoc();
                    break;
                case 'customer':
                    $stmt = $db->prepare("SELECT * FROM Customers WHERE customer_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $role_data = $stmt->get_result()->fetch_assoc();
                    break;
                case 'donner':
                    $stmt = $db->prepare("SELECT * FROM Donners WHERE donner_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $role_data = $stmt->get_result()->fetch_assoc();
                    break;
                case 'needy':
                    $stmt = $db->prepare("SELECT * FROM Needy WHERE needy_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $role_data = $stmt->get_result()->fetch_assoc();
                    break;
            }
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Update failed. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Edit Profile</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="profile_edit.php">
        
        <h3>Account Information</h3>
        
        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            <small>Email cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label>User Type</label>
            <input type="text" value="<?php echo ucfirst($user_type); ?>" disabled>
        </div>
        
        <hr>
        
        <h3>Personal Information</h3>
        
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="street">Street Address *</label>
            <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="city">City *</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="state">State *</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="zip_code">Zip Code *</label>
            <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code']); ?>" required>
        </div>
        
        <?php if ($user_type !== 'admin'): ?>
            <div class="form-group">
                <label for="phone_number">Phone Number <?php echo ($user_type !== 'needy') ? '*' : '(optional)'; ?></label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($role_data['phone_number'] ?? ''); ?>" <?php echo ($user_type !== 'needy') ? 'required' : ''; ?>>
            </div>
        <?php endif; ?>
        
        <?php if ($user_type === 'restaurant'): ?>
            <hr>
            <h3>Restaurant Information</h3>
            
            <div class="form-group">
                <label for="restaurant_name">Restaurant Name *</label>
                <input type="text" id="restaurant_name" name="restaurant_name" value="<?php echo htmlspecialchars($role_data['restaurant_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="cuisine_type">Cuisine Type</label>
                <input type="text" id="cuisine_type" name="cuisine_type" value="<?php echo htmlspecialchars($role_data['cuisine_type'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($role_data['description'] ?? ''); ?></textarea>
            </div>
        <?php endif; ?>
        
        <?php if ($user_type === 'customer' || $user_type === 'donner'): ?>
            <hr>
            <h3>Payment Information</h3>
            
            <div class="form-group">
                <label for="credit_card_number">Credit Card Number *</label>
                <input type="text" id="credit_card_number" name="credit_card_number" value="<?php echo htmlspecialchars($role_data['credit_card_number'] ?? ''); ?>" maxlength="19" required>
            </div>
            
            <div class="form-group">
                <label for="card_expiry">Expiration Date (MM/YYYY) *</label>
                <input type="text" id="card_expiry" name="card_expiry" value="<?php echo htmlspecialchars($role_data['card_expiry'] ?? ''); ?>" maxlength="7" required>
            </div>
            
            <div class="form-group">
                <label for="card_cvv">CVV *</label>
                <input type="text" id="card_cvv" name="card_cvv" value="<?php echo htmlspecialchars($role_data['card_cvv'] ?? ''); ?>" maxlength="4" required>
            </div>
            
            <div class="form-group">
                <label for="billing_address">Billing Address *</label>
                <input type="text" id="billing_address" name="billing_address" value="<?php echo htmlspecialchars($role_data['billing_address'] ?? ''); ?>" required>
            </div>
        <?php endif; ?>
        
        <hr>
        
        <h3>Change Password (Optional)</h3>
        <p class="text-muted">Leave blank to keep current password</p>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" minlength="6">
            <small>At least 6 characters</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>
