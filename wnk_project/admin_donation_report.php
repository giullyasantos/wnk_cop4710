<?php
require_once 'includes/config.php';
requireRole('admin');
$page_title = 'Donation Report';

$db = getDB();
$error = '';
$success = '';
$report_data = [];
$donner_name = '';
$total_donated = 0;
$total_donations = 0;

// Get all donners for dropdown
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
$donner_id = isset($_GET['donner_id']) ? intval($_GET['donner_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($donner_id > 0 && $year > 0) {
    // Validate year
    if ($year < 2020 || $year > $current_year + 1) {
        $error = "Please select a valid year.";
    } else {
        try {
            // Get donner name
            $stmt = $db->prepare("SELECT first_name, last_name FROM Users WHERE user_id = ?");
            $stmt->bind_param("i", $donner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $donner_name = $user['first_name'] . ' ' . $user['last_name'];
            }
            $stmt->close();
            
            // Query donations for this donner in the specified year
            $query = "SELECT d.*, p.plate_name, r.restaurant_name
                      FROM Donations d
                      JOIN Plates p ON d.plate_id = p.plate_id
                      JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                      WHERE d.donner_id = ? AND YEAR(d.donated_at) = ?
                      ORDER BY d.donated_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $donner_id, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $report_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate totals
            foreach ($report_data as $row) {
                $total_donated += floatval($row['total_amount']);
                $total_donations++;
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
    <h1>Donation Report</h1>
    <p>Generate a year-end donation report for a donner for tax purposes.</p>
</div>

<div class="card">
    <h2>Report Parameters</h2>
    <form method="GET" action="admin_donation_report.php">
        <div class="form-group">
            <label for="donner_id">Donner:</label>
            <select id="donner_id" name="donner_id" required>
                <option value="">-- Select Donner --</option>
                <?php foreach ($donners as $donner): ?>
                    <option value="<?php echo $donner['user_id']; ?>" <?php echo $donner_id == $donner['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($donner['first_name'] . ' ' . $donner['last_name']); ?>
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
        <?php if ($donner_id > 0 && $year > 0): ?>
            <?php if (count($report_data) > 0): ?>
                <h3><?php echo htmlspecialchars($donner_name); ?> - <?php echo $year; ?> Donation Report (Tax Purposes)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Plate Name</th>
                            <th>Restaurant</th>
                            <th>Quantity Donated</th>
                            <th>Total Donation Amount</th>
                            <th>Donation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['plate_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['restaurant_name']); ?></td>
                                <td><?php echo $row['original_quantity']; ?></td>
                                <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['donated_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="3" style="text-align: right;">Total Donations for <?php echo $year; ?>:</td>
                            <td style="font-size: 1.1em; color: #2c5aa0;">$<?php echo number_format($total_donated, 2); ?></td>
                            <td><?php echo $total_donations; ?> donation(s)</td>
                        </tr>
                    </tfoot>
                </table>
                <div style="margin-top: 16px; padding: 12px; background-color: #f0f8ff; border-left: 4px solid #2c5aa0;">
                    <p style="margin: 0;"><strong>Note:</strong> This report can be used for tax deduction purposes. Total donation amount for <?php echo $year; ?>: <strong>$<?php echo number_format($total_donated, 2); ?></strong></p>
                </div>
            <?php else: ?>
                <p class="text-muted">No donations found for <?php echo htmlspecialchars($donner_name); ?> in <?php echo $year; ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">Select a donner and year above to generate a report.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>

