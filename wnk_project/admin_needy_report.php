<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Needy Report';
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
            <select id="needy_id" name="needy_id">
                <option value="">-- Select Needy User --</option>
                <!-- Needy user options will be populated here -->
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
                    <th>Quantity</th>
                    <th>Claim Date</th>
                    <th>Donner</th>
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

