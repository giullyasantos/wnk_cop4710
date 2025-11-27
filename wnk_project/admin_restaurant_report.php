<?php
require_once 'includes/config.php';
requireRole('admin');
$page_title = 'Restaurant Activity Report';

$db = getDB();
$error = '';
$success = '';
$report_data = [];
$restaurant_name = '';
$total_revenue = 0;
$total_plates = 0;

// Get all restaurants for dropdown
$restaurants_query = "SELECT u.user_id, r.restaurant_name 
                      FROM Users u 
                      JOIN Restaurants r ON u.user_id = r.restaurant_id 
                      WHERE u.user_type = 'restaurant' AND u.is_active = TRUE
                      ORDER BY r.restaurant_name";
$restaurants_result = $db->query($restaurants_query);
$restaurants = $restaurants_result->fetch_all(MYSQLI_ASSOC);

// Generate year options (last 10 years)
$current_year = date('Y');
$years = [];
for ($i = 0; $i < 10; $i++) {
    $years[] = $current_year - $i;
}

// Process form submission
$restaurant_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($restaurant_id > 0 && $year > 0) {
    // Validate year
    if ($year < 2020 || $year > $current_year + 1) {
        $error = "Please select a valid year.";
    } else {
        try {
            // Get restaurant name
            $stmt = $db->prepare("SELECT restaurant_name FROM Restaurants WHERE restaurant_id = ?");
            $stmt->bind_param("i", $restaurant_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $restaurant = $result->fetch_assoc();
                $restaurant_name = $restaurant['restaurant_name'];
            }
            $stmt->close();
            
            // Query plates for this restaurant in the specified year
            $query = "SELECT p.*, 
                             (p.original_quantity - p.quantity_available) as quantity_sold,
                             ((p.original_quantity - p.quantity_available) * p.price) as revenue
                      FROM Plates p
                      WHERE p.restaurant_id = ? AND YEAR(p.created_at) = ?
                      ORDER BY p.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $restaurant_id, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $report_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate totals
            foreach ($report_data as $row) {
                $total_revenue += floatval($row['revenue']);
                $total_plates++;
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
    <h1>Restaurant Activity Report</h1>
    <p>Generate an annual activity report for a restaurant.</p>
</div>

<div class="card">
    <h2>Report Parameters</h2>
    <form method="GET" action="admin_restaurant_report.php">
        <div class="form-group">
            <label for="restaurant_id">Restaurant:</label>
            <select id="restaurant_id" name="restaurant_id" required>
                <option value="">-- Select Restaurant --</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?php echo $restaurant['user_id']; ?>" <?php echo $restaurant_id == $restaurant['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>
                    </option>
                <?php endforeach; ?>
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
        <?php if ($restaurant_id > 0 && $year > 0): ?>
            <?php if (count($report_data) > 0): ?>
                <h3><?php echo htmlspecialchars($restaurant_name); ?> - <?php echo $year; ?> Activity Report</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Plate Name</th>
                            <th>Price</th>
                            <th>Quantity Listed</th>
                            <th>Quantity Sold</th>
                            <th>Total Revenue</th>
                            <th>Date Listed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['plate_name']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['original_quantity']; ?></td>
                                <td><?php echo $row['quantity_sold']; ?></td>
                                <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="3" style="text-align: right;">Totals:</td>
                            <td><?php echo array_sum(array_column($report_data, 'quantity_sold')); ?></td>
                            <td>$<?php echo number_format($total_revenue, 2); ?></td>
                            <td><?php echo $total_plates; ?> plate(s)</td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted">No activity found for <?php echo htmlspecialchars($restaurant_name); ?> in <?php echo $year; ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">Select a restaurant and year above to generate a report.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>