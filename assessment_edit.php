<?php
include 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid assessment ID.";
    header("Location: assessments.php");
    exit();
}

$sql = "SELECT a.assessment_id, a.assessment_year, a.tax_amount, 
               p.property_address, p.property_type, o.owner_name,
               IFNULL(SUM(pay.amount_paid), 0) AS paid
        FROM TaxAssessments a
        JOIN Properties p ON a.property_id = p.property_id
        JOIN PropertyOwners o ON p.owner_id = o.owner_id
        LEFT JOIN Payments pay ON a.assessment_id = pay.assessment_id
        WHERE a.assessment_id = ?
        GROUP BY a.assessment_id, a.assessment_year, a.tax_amount, p.property_address, p.property_type, o.owner_name";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assessment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assessment) {
    $_SESSION['error'] = "Record not found.";
    header("Location: assessments.php");
    exit();
}

if ((float)$assessment['paid'] > 0) {
    $_SESSION['error'] = "Cannot edit an assessment that has payments recorded.";
    header("Location: assessments.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tax_amount = isset($_POST['tax_amount']) ? trim($_POST['tax_amount']) : '';

    if (!is_numeric($tax_amount) || (float)$tax_amount <= 0) {
        $_SESSION['error'] = "Tax amount must be greater than zero.";
        header("Location: assessment_edit.php?id=" . $id);
        exit();
    }

    $sql = "UPDATE TaxAssessments SET tax_amount = ? WHERE assessment_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        $tax_val = (float)$tax_amount;
        mysqli_stmt_bind_param($stmt, "di", $tax_val, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Assessment tax amount updated successfully.";
            mysqli_stmt_close($stmt);
            header("Location: assessments.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: assessment_edit.php?id=" . $id);
    exit();
}

include 'includes/header.php';
?>

<h1 class="page-title">Edit Assessment</h1>

<div class="card-box">
    <div style="margin-bottom: 16px;">
        <p><strong>Owner:</strong> <?php echo htmlspecialchars($assessment['owner_name']); ?></p>
        <p><strong>Property:</strong> <?php echo htmlspecialchars($assessment['property_address']); ?> (<?php echo htmlspecialchars($assessment['property_type']); ?>)</p>
        <p><strong>Assessment Year:</strong> <?php echo (int)$assessment['assessment_year']; ?></p>
    </div>

    <form method="POST" action="assessment_edit.php?id=<?php echo $id; ?>">
        <div class="form-group">
            <label for="tax_amount">Tax Amount (Rs.) *</label>
            <input type="number" step="0.01" id="tax_amount" name="tax_amount" class="form-control" required min="1" value="<?php echo htmlspecialchars($assessment['tax_amount']); ?>">
        </div>
        <div class="confirm-actions">
            <button type="submit" class="btn">Update Tax Amount</button>
            <a href="assessments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>
