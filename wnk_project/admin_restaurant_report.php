<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Restaurant Activity Report';
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
            <select id="restaurant_id" name="restaurant_id">
                <option value="">-- Select Restaurant --</option>
                <!-- Restaurant options will be populated here -->
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
                    <th>Price</th>
                    <th>Quantity Listed</th>
                    <th>Quantity Sold</th>
                    <th>Total Revenue</th>
                    <th>Date Listed</th>
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

