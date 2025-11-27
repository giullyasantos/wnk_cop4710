<?php
require_once 'includes/config.php';
requireRole('admin');
$page_title = 'Needy Report';

$db = getDB();
$error = '';
$success = '';
$report_data = [];
$needy_name = '';
$total_plates = 0;
$total_claims = 0;

// Get all needy users for dropdown
$needy_query = "SELECT u.user_id, u.first_name, u.last_name 
                FROM Users u 
                JOIN Needy n ON u.user_id = n.needy_id 
                WHERE u.user_type = 'needy' AND u.is_active = TRUE
                ORDER BY u.last_name, u.first_name";
$needy_result = $db->query($needy_query);
$needy_users = $needy_result->fetch_all(MYSQLI_ASSOC);

// Generate year options (last 10 years)
$current_year = date('Y');
$years = [];
for ($i = 0; $i < 10; $i++) {
    $years[] = $current_year - $i;
}

// Process form submission
$needy_id = isset($_GET['needy_id']) ? intval($_GET['needy_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($needy_id > 0 && $year > 0) {
    // Validate year
    if ($year < 2020 || $year > $current_year + 1) {
        $error = "Please select a valid year.";
    } else {
        try {
            // Get needy user name
            $stmt = $db->prepare("SELECT first_name, last_name FROM Users WHERE user_id = ?");
            $stmt->bind_param("i", $needy_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $needy_name = $user['first_name'] . ' ' . $user['last_name'];
            }
            $stmt->close();
            
            // Query needy claims with donner information
            $query = "SELECT nc.*, p.plate_name, r.restaurant_name, 
                             u.first_name as donner_first_name, u.last_name as donner_last_name,
                             COALESCE(nc.confirmed_at, nc.claimed_at) as claim_date
                      FROM Needy_Claim nc
                      JOIN Donations d ON nc.donation_id = d.donation_id
                      JOIN Plates p ON d.plate_id = p.plate_id
                      JOIN Restaurants r ON p.restaurant_id = r.restaurant_id
                      JOIN Users u ON d.donner_id = u.user_id
                      WHERE nc.needy_id = ? 
                        AND YEAR(COALESCE(nc.confirmed_at, nc.claimed_at)) = ?
                        AND nc.status != 'cancelled'
                      ORDER BY claim_date DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $needy_id, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $report_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate totals
            foreach ($report_data as $row) {
                $total_plates += intval($row['quantity']);
                $total_claims++;
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
    <h1>Needy Report</h1>
    <p>Generate an annual report of free plates received by a needy member.</p>
</div>

<div class="card">
    <h2>Report Parameters</h2>
    <form method="GET" action="admin_needy_report.php">
        <div class="form-group">
            <label for="needy_id">Needy User:</label>
            <select id="needy_id" name="needy_id" required>
                <option value="">-- Select Needy User --</option>
                <?php foreach ($needy_users as $needy): ?>
                    <option value="<?php echo $needy['user_id']; ?>" <?php echo $needy_id == $needy['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($needy['first_name'] . ' ' . $needy['last_name']); ?>
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
        <?php if ($needy_id > 0 && $year > 0): ?>
            <?php if (count($report_data) > 0): ?>
                <h3><?php echo htmlspecialchars($needy_name); ?> - <?php echo $year; ?> Free Plates Report</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Plate Name</th>
                            <th>Restaurant</th>
                            <th>Quantity</th>
                            <th>Claim Date</th>
                            <th>Donner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['plate_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['restaurant_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['claim_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['donner_first_name'] . ' ' . $row['donner_last_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td colspan="2" style="text-align: right;">Totals:</td>
                            <td><?php echo $total_plates; ?> plate(s)</td>
                            <td><?php echo $total_claims; ?> claim(s)</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted">No free plates found for <?php echo htmlspecialchars($needy_name); ?> in <?php echo $year; ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">Select a needy user and year above to generate a report.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$db->close();
include 'includes/footer.php'; 
?>

