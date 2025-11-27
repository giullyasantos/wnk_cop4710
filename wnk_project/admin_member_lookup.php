<?php
require_once 'includes/config.php';
requireRole('admin');
$page_title = 'Member Lookup';

$db = getDB();
$error = '';
$results = [];
$search_type = isset($_GET['search_type']) ? sanitize($_GET['search_type']) : 'email';
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';

// Process search if search term is provided
if (!empty($search_term)) {
    try {
        // Build query based on search type
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.user_type, 
                         u.street, u.city, u.state, u.zip_code,
                         COALESCE(r.phone_number, c.phone_number, d.phone_number, n.phone_number) as phone_number,
                         r.restaurant_name
                  FROM Users u
                  LEFT JOIN Restaurants r ON u.user_id = r.restaurant_id
                  LEFT JOIN Customers c ON u.user_id = c.customer_id
                  LEFT JOIN Donners d ON u.user_id = d.donner_id
                  LEFT JOIN Needy n ON u.user_id = n.needy_id
                  WHERE ";
        
        $stmt = null;
        
        switch ($search_type) {
            case 'email':
                $query .= "u.email LIKE ?";
                $search_param = '%' . $search_term . '%';
                $stmt = $db->prepare($query);
                $stmt->bind_param("s", $search_param);
                break;
                
            case 'name':
                $query .= "(u.first_name LIKE ? OR u.last_name LIKE ?)";
                $search_param = '%' . $search_term . '%';
                $stmt = $db->prepare($query);
                $stmt->bind_param("ss", $search_param, $search_param);
                break;
                
            case 'user_id':
                if (is_numeric($search_term)) {
                    $query .= "u.user_id = ?";
                    $user_id = intval($search_term);
                    $stmt = $db->prepare($query);
                    $stmt->bind_param("i", $user_id);
                } else {
                    $error = "User ID must be a number.";
                }
                break;
                
            default:
                $error = "Invalid search type.";
        }
        
        if ($stmt && !$error) {
            $stmt->execute();
            $result = $stmt->get_result();
            $results = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
    } catch (Exception $e) {
        $error = "An error occurred while searching. Please try again.";
    }
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Member Lookup</h1>
    <p>Search for member information by email, name, or user ID.</p>
</div>

<div class="card">
    <h2>Search Criteria</h2>
    <form method="GET" action="admin_member_lookup.php">
        <div class="form-group">
            <label>Search By:</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="search_type" value="email" <?php echo $search_type === 'email' ? 'checked' : ''; ?>>
                    Email
                </label>
                <label>
                    <input type="radio" name="search_type" value="name" <?php echo $search_type === 'name' ? 'checked' : ''; ?>>
                    Name
                </label>
                <label>
                    <input type="radio" name="search_type" value="user_id" <?php echo $search_type === 'user_id' ? 'checked' : ''; ?>>
                    User ID
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="search_term">Search Term:</label>
            <input type="text" id="search_term" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Enter search term">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Search Results</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display: block; padding: 16px;">
        <?php if (!empty($search_term) && empty($error)): ?>
            <?php if (count($results) > 0): ?>
                <p><strong><?php echo count($results); ?></strong> result(s) found.</p>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Phone</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                    <?php if ($row['user_type'] === 'restaurant' && $row['restaurant_name']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['restaurant_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($row['user_type'])); ?></td>
                                <td><?php echo $row['phone_number'] ? htmlspecialchars($row['phone_number']) : 'N/A'; ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($row['street'] . ', ' . $row['city'] . ', ' . $row['state'] . ' ' . $row['zip_code']); 
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No results found for your search criteria.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">Enter a search term above to find members.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>

