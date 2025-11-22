<?php
require_once 'includes/config.php';

$page_title = 'Register';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    // Get and sanitize common fields
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize($_POST['user_type']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $street = sanitize($_POST['street']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    
    // Validation
    if (empty($email) || empty($password) || empty($user_type) || empty($first_name) || 
        empty($last_name) || empty($street) || empty($city) || empty($state) || empty($zip_code)) {
        $error = "All required fields must be filled.";
    } elseif (!validateEmail($email)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password
            $password_hash = hashPassword($password);
            
            // Begin transaction
            $db->begin_transaction();
            
            try {
                // Insert into Users table
                $stmt = $db->prepare("INSERT INTO Users (email, password_hash, user_type, first_name, last_name, street, city, state, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $email, $password_hash, $user_type, $first_name, $last_name, $street, $city, $state, $zip_code);
                $stmt->execute();
                $user_id = $db->insert_id;
                
                // Insert into role-specific table
                switch($user_type) {
                    case 'restaurant':
                        $phone_number = sanitize($_POST['phone_number']);
                        $restaurant_name = sanitize($_POST['restaurant_name']);
                        $description = sanitize($_POST['description']);
                        $cuisine_type = sanitize($_POST['cuisine_type']);
                        
                        $stmt = $db->prepare("INSERT INTO Restaurants (restaurant_id, phone_number, restaurant_name, description, cuisine_type) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("issss", $user_id, $phone_number, $restaurant_name, $description, $cuisine_type);
                        $stmt->execute();
                        break;
                        
                    case 'customer':
                        $phone_number = sanitize($_POST['phone_number']);
                        $credit_card = sanitize($_POST['credit_card_number']);
                        $expiry = sanitize($_POST['card_expiry']);
                        $cvv = sanitize($_POST['card_cvv']);
                        $billing_address = sanitize($_POST['billing_address']);
                        
                        $stmt = $db->prepare("INSERT INTO Customers (customer_id, phone_number, credit_card_number, card_expiry, card_cvv, billing_address) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssss", $user_id, $phone_number, $credit_card, $expiry, $cvv, $billing_address);
                        $stmt->execute();
                        break;
                        
                    case 'donner':
                        $phone_number = sanitize($_POST['phone_number']);
                        $credit_card = sanitize($_POST['credit_card_number']);
                        $expiry = sanitize($_POST['card_expiry']);
                        $cvv = sanitize($_POST['card_cvv']);
                        $billing_address = sanitize($_POST['billing_address']);
                        
                        $stmt = $db->prepare("INSERT INTO Donners (donner_id, phone_number, credit_card_number, card_expiry, card_cvv, billing_address) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssss", $user_id, $phone_number, $credit_card, $expiry, $cvv, $billing_address);
                        $stmt->execute();
                        break;
                        
                    case 'needy':
                        $phone_number = !empty($_POST['phone_number']) ? sanitize($_POST['phone_number']) : null;
                        
                        $stmt = $db->prepare("INSERT INTO Needy (needy_id, phone_number, verification_status) VALUES (?, ?, 'pending')");
                        $stmt->bind_param("is", $user_id, $phone_number);
                        $stmt->execute();
                        break;
                        
                    case 'admin':
                        // Admin doesn't need additional table (no Administrators table in new design)
                        break;
                }
                
                // Commit transaction
                $db->commit();
                
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                
            } catch (Exception $e) {
                $db->rollback();
                $error = "Registration failed. Please try again.";
            }
        }
        
        $stmt->close();
    }
    
    $db->close();
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Register for WNK</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="register.php" id="registerForm">
        
        <!-- User Type Selection -->
        <div class="form-group">
            <label>I am a: *</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="user_type" value="restaurant" required>
                    Restaurant
                </label>
                <label>
                    <input type="radio" name="user_type" value="customer" required>
                    Customer
                </label>
                <label>
                    <input type="radio" name="user_type" value="donner" required>
                    Donor
                </label>
                <label>
                    <input type="radio" name="user_type" value="needy" required>
                    Needy
                </label>
                <label>
                    <input type="radio" name="user_type" value="admin" required>
                    Administrator
                </label>
            </div>
        </div>
        
        <hr>
        
        <!-- Common Fields -->
        <h3>Personal Information</h3>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="6">
            <small>At least 6 characters</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label for="street">Street Address *</label>
            <input type="text" id="street" name="street" required>
        </div>
        
        <div class="form-group">
            <label for="city">City *</label>
            <input type="text" id="city" name="city" required>
        </div>
        
        <div class="form-group">
            <label for="state">State *</label>
            <input type="text" id="state" name="state" required>
        </div>
        
        <div class="form-group">
            <label for="zip_code">Zip Code *</label>
            <input type="text" id="zip_code" name="zip_code" required>
        </div>
        
        <!-- Phone Number (for most types) -->
        <div class="form-group" id="phone_group" style="display: none;">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number">
            <small>Required for restaurants, customers, and donors. Optional for needy.</small>
        </div>
        
        <!-- Restaurant-specific Fields -->
        <div id="restaurant_group" style="display: none;">
            <h3>Restaurant Information</h3>
            
            <div class="form-group">
                <label for="restaurant_name">Restaurant Name *</label>
                <input type="text" id="restaurant_name" name="restaurant_name">
            </div>
            
            <div class="form-group">
                <label for="cuisine_type">Cuisine Type</label>
                <input type="text" id="cuisine_type" name="cuisine_type" placeholder="e.g., Italian, Japanese, Mexican">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
        </div>
        
        <!-- Credit Card Fields (for customers and donors) -->
        <div id="credit_card_group" style="display: none;">
            <h3>Payment Information</h3>
            
            <div class="form-group">
                <label for="credit_card_number">Credit Card Number *</label>
                <input type="text" id="credit_card_number" name="credit_card_number" maxlength="19">
            </div>
            
            <div class="form-group">
                <label for="card_expiry">Expiration Date (MM/YYYY) *</label>
                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YYYY" maxlength="7">
            </div>
            
            <div class="form-group">
                <label for="card_cvv">CVV *</label>
                <input type="text" id="card_cvv" name="card_cvv" maxlength="4">
            </div>
            
            <div class="form-group">
                <label for="billing_address">Billing Address *</label>
                <input type="text" id="billing_address" name="billing_address">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Register</button>
        
        <p class="text-center mt-2">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
