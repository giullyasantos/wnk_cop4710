<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Donation Report';
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
            <select id="donner_id" name="donner_id">
                <option value="">-- Select Donner --</option>
                <!-- Donner options will be populated here -->
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
                    <th>Quantity Donated</th>
                    <th>Total Donation Amount</th>
                    <th>Donation Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 24px;">No report data to display</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>

