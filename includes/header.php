<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipal Property Tax System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="site-title">Municipal Property Tax System</div>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="owners.php">Owners</a>
            <a href="properties.php">Properties</a>
            <a href="assessments.php">Assessments</a>
            <a href="payments.php">Payments</a>
            <a href="reports.php">Reports</a>
        </nav>
    </header>
    <main class="container">
<?php
if (isset($_SESSION['msg'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['msg']) . '</div>';
    unset($_SESSION['msg']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>
