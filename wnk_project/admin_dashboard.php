<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Admin Dashboard';
include 'includes/header.php';
?>

<div class="card">
    <h1>Administrator Dashboard</h1>
    <p>Welcome to the Waste Not Kitchen administration panel.</p>
</div>

<div class="card">
    <h2>Member Management</h2>
    <div style="display: block; margin-top: 16px;">
        <a href="admin_member_lookup.php" class="btn btn-primary">Look Up Member Information</a>
    </div>
</div>

<div class="card">
    <h2>Report Generation</h2>
    <div style="display: block; margin-top: 16px;">
        <div style="display: block; margin-bottom: 12px;">
            <a href="admin_restaurant_report.php" class="btn btn-secondary">Restaurant Activity Report</a>
        </div>
        <div style="display: block; margin-bottom: 12px;">
            <a href="admin_purchase_report.php" class="btn btn-secondary">Purchase Report</a>
        </div>
        <div style="display: block; margin-bottom: 12px;">
            <a href="admin_needy_report.php" class="btn btn-secondary">Needy Report</a>
        </div>
        <div style="display: block; margin-bottom: 12px;">
            <a href="admin_donation_report.php" class="btn btn-secondary">Donation Report</a>
        </div>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>

