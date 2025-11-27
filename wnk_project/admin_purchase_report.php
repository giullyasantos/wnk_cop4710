<?php
require_once 'includes/config.php';
requireRole('admin');
$page_title = 'Purchase Report';

$db = getDB();
$error = '';
$success = '';
$report_data = [];
$user_name = '';
$total_amount = 0;
$total_purchases = 0;

// Get user type from form or default to customer
$selected_user_type = isset($_GET['user_type']) ? sanitize($_GET['user_type']) : 'customer';
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

// Get customers for dropdown
$customers_query = "SELECT u.user_id, u.first_name, u.last_name 
                    FROM Users u 
                    JOIN Customers c ON u.user_id = c.customer_id 
                    WHERE u.user_type = 'customer' AND u.is_active = TRUE
                    ORDER BY u.last_name, u.first_name";
$customers_result = $db->query($customers_query);
$customers = $customers_result->fetch_all(MYSQLI_ASSOC);

// Get donners for dropdown
$donners_query = "SELECT u.user_id, u.first_name, u.last_name 
                  FROM Users u 
                  JOIN Donners d ON u.user_id = d.donner_id 
                  WHERE u.user_type = 'donner' AND u.is_active = TRUE
                  ORDER BY u.last_name, u.first_name";
$donners_result = $db->query($donners_query);
$donners = $donners_result->fetch_all(MYSQLI_ASSOC);

// Generate year options (last 10 years)
$current_year = date('Y');
$years = [];
for ($i = 0; $i < 10; $i++) {
    $years[] = $current_year - $i;
}

// Process form submission
if ($user_id > 0 && $year > 0) {
    // Validate year
    if ($year < 2020 || $year > $current_year + 1) {
        $error = "Please select a valid year.";
    } else {
        try {
            // Get user name
            $stmt = $db->prepare("SELECT first_name, last_name FROM Users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_name = $user['first_name'] . ' ' . $user['last_name'];
            }
            $stmt->close();
            
            // Query purchases based on user type
            if ($selected_user_type === 'customer') {
                // Query customer reservations
                $query = "SELECT cr.*, p.plate_name, p.price, r.restaurant_name,
                                 (cr.quantity * p.price) as total_amount,
                                 COALESCE(cr.confirmed_at, cr.reserved_at) as purchase_date
                          FROM Customer_Reservations cr
                          JOIN Plates p ON cr.plate_id = p.plate_id
                          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                          WHERE cr.customer_id = ? 
                            AND YEAR(COALESCE(cr.confirmed_at, cr.reserved_at)) = ?
                            AND cr.status != 'cancelled'
                          ORDER BY purchase_date DESC";
            } else {
                // Query donner donations
                $query = "SELECT d.*, p.plate_name, p.price, r.restaurant_name,
                                 d.total_amount,
                                 d.donated_at as purchase_date
                          FROM Donations d
                          JOIN Plates p ON d.plate_id = p.plate_id
                          JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                          WHERE d.donner_id = ? 
                            AND YEAR(d.donated_at) = ?
                          ORDER BY d.donated_at DESC";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $user_id, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $report_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate totals
            foreach ($report_data as $row) {
                $total_amount += floatval($row['total_amount']);
                $total_purchases++;
            }
            
            if (count($report_data) > 0) {
                $success = "Report generated successfully.";
            }
            
        } catch (Exception $e) {
            $error = "An error occurred while generating the report. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Purchase Report</h1>
    <p>Generate an annual purchase report for a customer or donner.</p>
</div>

<div class="card">
    <h2>Report Parameters</h2>
    <form method="GET" action="admin_purchase_report.php">
        <div class="form-group">
            <label>User Type:</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="user_type" value="customer" <?php echo $selected_user_type === 'customer' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    Customer
                </label>
                <label>
                    <input type="radio" name="user_type" value="donner" <?php echo $selected_user_type === 'donner' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    Donner
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="user_id">User:</label>
            <select id="user_id" name="user_id" required>
                <option value="">-- Select User --</option>
                <?php if ($selected_user_type === 'customer'): ?>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['user_id']; ?>" <?php echo $user_id == $customer['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($donners as $donner): ?>
                        <option value="<?php echo $donner['user_id']; ?>" <?php echo $user_id == $donner['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($donner['first_name'] . ' ' . $donner['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="year">Year:</label>
            <select id="year" name="year" required>
                <option value="">-- Select Year --</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Report Results</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="display: block; padding: 16px;">
        <?php if ($user_id > 0 && $year > 0): ?>
            <?php if (count($report_data) > 0): ?>
                <h3><?php echo htmlspecialchars($user_name); ?> - <?php echo $year; ?> Purchase Report</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Plate Name</th>
                            <th>Restaurant</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Purchase Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['plate_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['restaurant_name']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $selected_user_type === 'customer' ? $row['quantity'] : $row['original_quantity']; ?></td>
                                <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['purchase_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="4" style="text-align: right;">Totals:</td>
                            <td>$<?php echo number_format($total_amount, 2); ?></td>
                            <td><?php echo $total_purchases; ?> purchase(s)</td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted">No purchases found for <?php echo htmlspecialchars($user_name); ?> in <?php echo $year; ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">Select a user and year above to generate a report.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>

