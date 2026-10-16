<?php
include("db.php");
include("header.php");

$res = mysqli_query($con, "SELECT COUNT(*) AS total FROM Citizens");
$row = mysqli_fetch_assoc($res);
$citizens_count = $row['total'];

$res = mysqli_query($con, "SELECT COUNT(*) AS total FROM VaccinationCenters");
$row = mysqli_fetch_assoc($res);
$centers_count = $row['total'];

$res = mysqli_query($con, "SELECT COUNT(*) AS total FROM Vaccines");
$row = mysqli_fetch_assoc($res);
$vaccines_count = $row['total'];

$res = mysqli_query($con, "SELECT COUNT(*) AS total FROM Vaccinations");
$row = mysqli_fetch_assoc($res);
$vaccinations_count = $row['total'];
?>

<h2>Dashboard Overview</h2>

<div class="card">
    <h3>System Summary</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Entity</th>
                <th>Total Records</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Citizens Registered</td>
                <td><?php echo $citizens_count; ?></td>
                <td><a href="citizens.php">View Citizens</a></td>
            </tr>
            <tr>
                <td>Vaccination Centers</td>
                <td><?php echo $centers_count; ?></td>
                <td><a href="centers.php">View Centers</a></td>
            </tr>
            <tr>
                <td>Vaccine Types</td>
                <td><?php echo $vaccines_count; ?></td>
                <td><a href="vaccines.php">View Vaccines</a></td>
            </tr>
            <tr>
                <td>Vaccinations Administered</td>
                <td><?php echo $vaccinations_count; ?></td>
                <td><a href="vaccinations.php">View Vaccinations</a></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Quick Actions</h3>
    <p style="margin-bottom: 12px;">Select an operation to proceed:</p>
    <a href="citizens.php" class="btn">Register Citizen</a>
    <a href="vaccinations.php" class="btn">Record Vaccination</a>
    <a href="reports.php" class="btn btn-secondary">View Reports</a>
</div>

<?php
include("footer.php");
?>
