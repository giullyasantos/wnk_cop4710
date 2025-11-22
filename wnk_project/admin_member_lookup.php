<?php
require_once 'includes/config.php';
// requireRole('admin'); // Commented out for skeleton
$page_title = 'Member Lookup';
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
                    <input type="radio" name="search_type" value="email" checked>
                    Email
                </label>
                <label>
                    <input type="radio" name="search_type" value="name">
                    Name
                </label>
                <label>
                    <input type="radio" name="search_type" value="user_id">
                    User ID
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="search_term">Search Term:</label>
            <input type="text" id="search_term" name="search_term" placeholder="Enter search term">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Search Results</h2>
    <div style="display: block; padding: 16px;">
        <p>Search results will appear here.</p>
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
                <tr>
                    <td colspan="6" style="text-align: center; padding: 24px;">No results to display</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>

