<?php
require_once 'includes/config.php';

$page_title = 'Login';
$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirect = '';
    switch($_SESSION['user_type']) {
        case 'restaurant':
            $redirect = 'restaurant_dashboard.php';
            break;
        case 'customer':
            $redirect = 'customer_browse.php';
            break;
        case 'donner':
            $redirect = 'donner_browse.php';
            break;
        case 'needy':
            $redirect = 'needy_browse.php';
            break;
        case 'admin':
            $redirect = 'admin_dashboard.php';
            break;
        default:
            $redirect = 'index.php';
    }
    header("Location: $redirect");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Get user from database
        $stmt = $db->prepare("SELECT user_id, password_hash, user_type, first_name, last_name, is_active FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if (!$user['is_active']) {
                $error = "Your account has been deactivated. Please contact support.";
            }
            // Verify password
            elseif (verifyPassword($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $email;
                
                // Redirect based on user type
                switch($user['user_type']) {
                    case 'restaurant':
                        header("Location: restaurant_dashboard.php");
                        break;
                    case 'customer':
                        header("Location: index.php");
                        break;
                    case 'donner':
                        header("Location: index.php");
                        break;
                    case 'needy':
                        header("Location: index.php");
                        break;
                    case 'admin':
                        header("Location: index.php");
                        break;
                    default:
                        header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        
        $stmt->close();
    }
    
    $db->close();
}

include 'includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>Login to WNK</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Login</button>
        
        <p class="text-center mt-2">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
