<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<h1 class="page-title">Privacy Policy</h1>

<div class="card-box">
    <h2 class="section-title">1. Information Stored</h2>
    <p>This system stores basic property tax administration details: property owner names, contact phone numbers, optional email addresses, property locations, property classifications, assessed tax amounts, and payment transaction logs.</p>

    <h2 class="section-title">2. Purpose of Collection</h2>
    <p>All stored information is utilized exclusively for simulating municipal property tax tracking, computing outstanding tax liabilities, and generating relational DBMS reports.</p>

    <h2 class="section-title">3. Data Access and Security</h2>
    <p>All records are stored locally within a MySQL database on the host machine. Access is restricted to municipal administration personnel and academic reviewers. Prepared statements are implemented to safeguard data input against SQL injection.</p>

    <h2 class="section-title">4. Third-Party Sharing</h2>
    <p>No personal records, contact details, or financial figures are transmitted to external servers, cloud providers, or third-party marketing services.</p>
</div>

<?php
include 'includes/footer.php';
?>
