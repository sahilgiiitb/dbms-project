<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = isset($_POST['assessment_id']) ? (int)$_POST['assessment_id'] : 0;
    $date = isset($_POST['payment_date']) ? trim($_POST['payment_date']) : '';
    $amount = isset($_POST['amount_paid']) ? trim($_POST['amount_paid']) : '';

    if ($assessment_id <= 0) {
        $_SESSION['error'] = "Please select an assessment.";
        header("Location: payments.php");
        exit();
    }

    if ($date === '') {
        $_SESSION['error'] = "Payment date is required.";
        header("Location: payments.php");
        exit();
    }

    $today = date('Y-m-d');
    if ($date > $today) {
        $_SESSION['error'] = "Payment date cannot be in the future.";
        header("Location: payments.php");
        exit();
    }

    if (!is_numeric($amount) || (float)$amount <= 0) {
        $_SESSION['error'] = "Payment amount must be greater than zero.";
        header("Location: payments.php");
        exit();
    }

    $chk_sql = "SELECT a.tax_amount - IFNULL(SUM(pay.amount_paid), 0) AS due
                FROM TaxAssessments a
                LEFT JOIN Payments pay ON a.assessment_id = pay.assessment_id
                WHERE a.assessment_id = ?
                GROUP BY a.assessment_id, a.tax_amount";
    $chk_stmt = mysqli_prepare($con, $chk_sql);
    mysqli_stmt_bind_param($chk_stmt, "i", $assessment_id);
    mysqli_stmt_execute($chk_stmt);
    $chk_res = mysqli_stmt_get_result($chk_stmt);
    $chk_row = mysqli_fetch_assoc($chk_res);
    mysqli_stmt_close($chk_stmt);

    if (!$chk_row) {
        $_SESSION['error'] = "Selected assessment does not exist.";
        header("Location: payments.php");
        exit();
    }

    $due = (float)$chk_row['due'];
    $pay_amount = (float)$amount;

    if ($due <= 0) {
        $_SESSION['error'] = "This assessment has already been fully paid.";
        header("Location: payments.php");
        exit();
    }

    if ($pay_amount > $due) {
        $_SESSION['error'] = "Payment amount cannot be more than the pending amount (Rs. " . number_format($due, 2) . ").";
        header("Location: payments.php");
        exit();
    }

    $sql = "INSERT INTO Payments (assessment_id, payment_date, amount_paid) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isd", $assessment_id, $date, $pay_amount);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Payment recorded successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: payments.php");
    exit();
}

$assessments_sql = "SELECT a.assessment_id, a.assessment_year, 
                           a.tax_amount - IFNULL(x.paid, 0) AS due,
                           o.owner_name, p.property_address
                    FROM TaxAssessments a
                    JOIN Properties p ON a.property_id = p.property_id
                    JOIN PropertyOwners o ON p.owner_id = o.owner_id
                    LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
                               FROM Payments GROUP BY assessment_id) x
                           ON a.assessment_id = x.assessment_id
                    HAVING due > 0
                    ORDER BY a.assessment_year DESC, o.owner_name ASC";
$assessments_result = mysqli_query($con, $assessments_sql);

$payments_sql = "SELECT pay.payment_id, pay.payment_date, pay.amount_paid,
                        a.assessment_id, a.assessment_year,
                        p.property_address,
                        o.owner_name
                 FROM Payments pay
                 JOIN TaxAssessments a ON pay.assessment_id = a.assessment_id
                 JOIN Properties p ON a.property_id = p.property_id
                 JOIN PropertyOwners o ON p.owner_id = o.owner_id
                 ORDER BY pay.payment_date DESC, pay.payment_id DESC";
$payments_result = mysqli_query($con, $payments_sql);

include 'includes/header.php';
?>

<h1 class="page-title">Tax Payments</h1>

<div class="card-box">
    <h2 class="section-title">Record New Payment</h2>
    <form method="POST" action="payments.php">
        <div class="form-group">
            <label for="assessment_id">Select Assessment (Pending Only) *</label>
            <select id="assessment_id" name="assessment_id" class="form-control" required>
                <option value="">-- Choose Assessment --</option>
                <?php while ($as = mysqli_fetch_assoc($assessments_result)): ?>
                    <option value="<?php echo (int)$as['assessment_id']; ?>">
                        #<?php echo (int)$as['assessment_id']; ?>: <?php echo htmlspecialchars($as['owner_name']); ?> - <?php echo htmlspecialchars($as['property_address']); ?> (<?php echo (int)$as['assessment_year']; ?>) - Due: Rs. <?php echo number_format((float)$as['due'], 2); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="payment_date">Payment Date *</label>
            <input type="date" id="payment_date" name="payment_date" class="form-control" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label for="amount_paid">Amount Paid (Rs.) *</label>
            <input type="number" step="0.01" id="amount_paid" name="amount_paid" class="form-control" required min="1">
        </div>
        <button type="submit" class="btn">Record Payment</button>
    </form>
</div>

<h2 class="section-title">All Recorded Payments</h2>
<?php if ($payments_result && mysqli_num_rows($payments_result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Assessment ID</th>
                    <th>Owner</th>
                    <th>Property</th>
                    <th>Year</th>
                    <th>Date</th>
                    <th class="text-right">Amount Paid (Rs.)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($payments_result)): ?>
                    <tr>
                        <td><?php echo (int)$row['payment_id']; ?></td>
                        <td>#<?php echo (int)$row['assessment_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_address']); ?></td>
                        <td><?php echo (int)$row['assessment_year']; ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['amount_paid'], 2); ?></td>
                        <td class="action-links">
                            <a href="delete.php?type=payment&id=<?php echo (int)$row['payment_id']; ?>" class="text-danger">Delete</a>
                        </td>
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
