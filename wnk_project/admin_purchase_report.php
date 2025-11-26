<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Purchase Report';
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
                    <input type="radio" name="user_type" value="customer" checked>
                    Customer
                </label>
                <label>
                    <input type="radio" name="user_type" value="donner">
                    Donner
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="user_id">User:</label>
            <select id="user_id" name="user_id">
                <option value="">-- Select User --</option>
                <!-- User options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="year">Year:</label>
            <select id="year" name="year">
                <option value="">-- Select Year --</option>
                <!-- Year options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Report Results</h2>
    <div style="display: block; padding: 16px;">
        <p>Report data will appear here.</p>
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
                <tr>
                    <td colspan="6" style="text-align: center; padding: 24px;">No report data to display</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>

