<?php
include 'includes/db.php';
include 'includes/header.php';

$res_owners = mysqli_query($con, "SELECT COUNT(*) AS total FROM PropertyOwners");
$row_owners = mysqli_fetch_assoc($res_owners);
$total_owners = (int)$row_owners['total'];

$res_props = mysqli_query($con, "SELECT COUNT(*) AS total FROM Properties");
$row_props = mysqli_fetch_assoc($res_props);
$total_props = (int)$row_props['total'];

$res_assess = mysqli_query($con, "SELECT COUNT(*) AS total, IFNULL(SUM(tax_amount), 0) AS total_tax FROM TaxAssessments");
$row_assess = mysqli_fetch_assoc($res_assess);
$total_assessments = (int)$row_assess['total'];
$total_tax_assessed = (float)$row_assess['total_tax'];

$res_pay = mysqli_query($con, "SELECT COUNT(*) AS total, IFNULL(SUM(amount_paid), 0) AS total_paid FROM Payments");
$row_pay = mysqli_fetch_assoc($res_pay);
$total_payments = (int)$row_pay['total'];
$total_tax_collected = (float)$row_pay['total_paid'];

$total_tax_pending = $total_tax_assessed - $total_tax_collected;
if ($total_tax_pending < 0) {
    $total_tax_pending = 0;
}

$recent_sql = "SELECT pay.payment_id, pay.payment_date, pay.amount_paid,
                      a.assessment_id, a.assessment_year,
                      p.property_address,
                      o.owner_name
               FROM Payments pay
               JOIN TaxAssessments a ON pay.assessment_id = a.assessment_id
               JOIN Properties p ON a.property_id = p.property_id
               JOIN PropertyOwners o ON p.owner_id = o.owner_id
               ORDER BY pay.payment_date DESC, pay.payment_id DESC
               LIMIT 5";
$recent_result = mysqli_query($con, $recent_sql);
?>

<h1 class="page-title">System Dashboard</h1>

<h2 class="section-title">System Summary Totals</h2>
<div class="table-responsive" style="max-width: 600px;">
    <table class="table">
        <thead>
            <tr>
                <th>Summary Metric</th>
                <th class="text-right">Total Count / Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Registered Owners</td>
                <td class="text-right"><?php echo $total_owners; ?></td>
            </tr>
            <tr>
                <td>Total Registered Properties</td>
                <td class="text-right"><?php echo $total_props; ?></td>
            </tr>
            <tr>
                <td>Total Yearly Assessments</td>
                <td class="text-right"><?php echo $total_assessments; ?></td>
            </tr>
            <tr>
                <td>Total Payments Recorded</td>
                <td class="text-right"><?php echo $total_payments; ?></td>
            </tr>
            <tr>
                <td>Total Tax Assessed</td>
                <td class="text-right">Rs. <?php echo number_format($total_tax_assessed, 2); ?></td>
            </tr>
            <tr>
                <td>Total Tax Collected</td>
                <td class="text-right">Rs. <?php echo number_format($total_tax_collected, 2); ?></td>
            </tr>
            <tr>
                <td>Total Pending Due</td>
                <td class="text-right"><strong>Rs. <?php echo number_format($total_tax_pending, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

<h2 class="section-title">Recent Payments (Last 5 Records)</h2>
<?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Assessment ID</th>
                    <th>Owner</th>
                    <th>Property Address</th>
                    <th>Year</th>
                    <th>Date</th>
                    <th class="text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                    <tr>
                        <td><?php echo (int)$row['payment_id']; ?></td>
                        <td>#<?php echo (int)$row['assessment_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_address']); ?></td>
                        <td><?php echo (int)$row['assessment_year']; ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['amount_paid'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No payments recorded yet.</div>
<?php endif; ?>

<?php
include 'includes/footer.php';
?>
