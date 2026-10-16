<?php
include("db.php");
include("header.php");

$q1 = "SELECT ce.center_name, COUNT(v.vaccination_id) AS total
       FROM VaccinationCenters ce
       LEFT JOIN Vaccinations v ON ce.center_id = v.center_id
       GROUP BY ce.center_id, ce.center_name";
$r1 = mysqli_query($con, $q1);

$q2 = "SELECT vc.vaccine_name, COUNT(v.vaccination_id) AS total
       FROM Vaccines vc
       LEFT JOIN Vaccinations v ON vc.vaccine_id = v.vaccine_id
       GROUP BY vc.vaccine_id, vc.vaccine_name";
$r2 = mysqli_query($con, $q2);

$q3 = "SELECT c.citizen_name, vc.vaccine_name, COUNT(*) AS doses_taken
       FROM Vaccinations v
       JOIN Citizens c ON v.citizen_id = c.citizen_id
       JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
       GROUP BY c.citizen_id, vc.vaccine_id, c.citizen_name, vc.vaccine_name, vc.doses_required
       HAVING COUNT(*) >= vc.doses_required";
$r3 = mysqli_query($con, $q3);

$q4 = "SELECT c.citizen_name, vc.vaccine_name, COUNT(*) AS doses_taken, vc.doses_required
       FROM Vaccinations v
       JOIN Citizens c ON v.citizen_id = c.citizen_id
       JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
       GROUP BY c.citizen_id, vc.vaccine_id, c.citizen_name, vc.vaccine_name, vc.doses_required
       HAVING COUNT(*) < vc.doses_required";
$r4 = mysqli_query($con, $q4);

$q5 = "SELECT v.vaccination_id, c.citizen_name, vc.vaccine_name, ce.center_name, v.vaccination_date
       FROM Vaccinations v
       JOIN Citizens c ON v.citizen_id = c.citizen_id
       JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
       JOIN VaccinationCenters ce ON v.center_id = ce.center_id
       ORDER BY v.vaccination_date DESC";
$r5 = mysqli_query($con, $q5);
?>

<h2>Vaccination Reports & Analytics</h2>

<div class="card">
    <h3>1. Center-wise Total Vaccinations</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Center Name</th>
                <th>Total Vaccinations Administered</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($r1) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r1)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['center_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['total']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="2" style="text-align:center;">No data available.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>2. Vaccine-wise Total Doses Administered</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Vaccine Name</th>
                <th>Total Doses Given</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($r2) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r2)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['total']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="2" style="text-align:center;">No data available.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>3. Citizens Who Completed All Required Doses</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Citizen Name</th>
                <th>Vaccine Name</th>
                <th>Doses Completed</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($r3) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r3)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doses_taken']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="3" style="text-align:center;">No fully vaccinated citizens found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>4. Citizens with Pending Doses</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Citizen Name</th>
                <th>Vaccine Name</th>
                <th>Doses Taken</th>
                <th>Doses Required</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($r4) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r4)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doses_taken']); ?></td>
                        <td><?php echo htmlspecialchars($row['doses_required']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4" style="text-align:center;">No citizens with pending doses found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>5. All Vaccination Records Summary</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Citizen Name</th>
                <th>Vaccine Name</th>
                <th>Center</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($r5) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($r5)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vaccination_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['center_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccination_date']); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No records found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include("footer.php");
?>
