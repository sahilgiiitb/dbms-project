<?php
include 'includes/db.php';
include 'includes/header.php';

$defaulters_sql = "SELECT o.owner_name, o.phone, p.property_address, a.assessment_year,
                          a.tax_amount - IFNULL(SUM(pay.amount_paid), 0) AS due
                   FROM TaxAssessments a
                   JOIN Properties p ON a.property_id = p.property_id
                   JOIN PropertyOwners o ON p.owner_id = o.owner_id
                   LEFT JOIN Payments pay ON a.assessment_id = pay.assessment_id
                   GROUP BY a.assessment_id, o.owner_name, o.phone, p.property_address,
                            a.assessment_year, a.tax_amount
                   HAVING due > 0
                   ORDER BY due DESC";
$defaulters_result = mysqli_query($con, $defaulters_sql);

$yearwise_sql = "SELECT a.assessment_year,
                        SUM(a.tax_amount) AS total_assessed,
                        IFNULL(SUM(x.paid), 0) AS total_collected,
                        SUM(a.tax_amount) - IFNULL(SUM(x.paid), 0) AS total_pending
                 FROM TaxAssessments a
                 LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
                            FROM Payments GROUP BY assessment_id) x
                         ON a.assessment_id = x.assessment_id
                 GROUP BY a.assessment_year
                 ORDER BY a.assessment_year DESC";
$yearwise_result = mysqli_query($con, $yearwise_sql);

$typewise_sql = "SELECT p.property_type,
                        COUNT(a.assessment_id) AS assessments,
                        SUM(a.tax_amount) AS total_assessed,
                        IFNULL(SUM(x.paid), 0) AS total_collected
                 FROM Properties p
                 JOIN TaxAssessments a ON p.property_id = a.property_id
                 LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
                            FROM Payments GROUP BY assessment_id) x
                         ON a.assessment_id = x.assessment_id
                 GROUP BY p.property_type
                 ORDER BY p.property_type ASC";
$typewise_result = mysqli_query($con, $typewise_sql);

$ownerwise_sql = "SELECT o.owner_name,
                         SUM(a.tax_amount) - IFNULL(SUM(x.paid), 0) AS pending
                  FROM PropertyOwners o
                  JOIN Properties p ON o.owner_id = p.owner_id
                  JOIN TaxAssessments a ON p.property_id = a.property_id
                  LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
                             FROM Payments GROUP BY assessment_id) x
                          ON a.assessment_id = x.assessment_id
                  GROUP BY o.owner_id, o.owner_name
                  HAVING pending > 0
                  ORDER BY pending DESC";
$ownerwise_result = mysqli_query($con, $ownerwise_sql);
?>

<h1 class="page-title">Tax System Reports</h1>

<h2 class="section-title">1. Tax Defaulters Report (Assessments with Pending Dues)</h2>
<?php if ($defaulters_result && mysqli_num_rows($defaulters_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Owner Name</th>
                    <th>Phone</th>
                    <th>Property Address</th>
                    <th>Year</th>
                    <th class="text-right">Pending Due (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($defaulters_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_address']); ?></td>
                        <td><?php echo (int)$row['assessment_year']; ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['due'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No tax defaulters found.</div>
<?php endif; ?>

<h2 class="section-title">2. Year-Wise Tax Collection Summary</h2>
<?php if ($yearwise_result && mysqli_num_rows($yearwise_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Assessment Year</th>
                    <th class="text-right">Total Assessed (Rs.)</th>
                    <th class="text-right">Total Collected (Rs.)</th>
                    <th class="text-right">Total Pending (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($yearwise_result)): ?>
                    <tr>
                        <td><?php echo (int)$row['assessment_year']; ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['total_assessed'], 2); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['total_collected'], 2); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['total_pending'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No assessment data available.</div>
<?php endif; ?>

<h2 class="section-title">3. Property Type-Wise Tax Summary</h2>
<?php if ($typewise_result && mysqli_num_rows($typewise_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Property Type</th>
                    <th class="text-center">Assessments</th>
                    <th class="text-right">Total Assessed (Rs.)</th>
                    <th class="text-right">Total Collected (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($typewise_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['property_type']); ?></td>
                        <td class="text-center"><?php echo (int)$row['assessments']; ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['total_assessed'], 2); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['total_collected'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No property type data available.</div>
<?php endif; ?>

<h2 class="section-title">4. Owner-Wise Pending Dues</h2>
<?php if ($ownerwise_result && mysqli_num_rows($ownerwise_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Owner Name</th>
                    <th class="text-right">Pending Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($ownerwise_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['pending'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No pending amounts for any owners.</div>
<?php endif; ?>

<?php
include 'includes/footer.php';
?>
