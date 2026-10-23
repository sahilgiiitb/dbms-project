<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    $year = isset($_POST['assessment_year']) ? trim($_POST['assessment_year']) : '';
    $tax_amount = isset($_POST['tax_amount']) ? trim($_POST['tax_amount']) : '';

    if ($property_id <= 0) {
        $_SESSION['error'] = "Please select a property.";
        header("Location: assessments.php");
        exit();
    }

    if (!preg_match('/^[0-9]{4}$/', $year)) {
        $_SESSION['error'] = "Year must be a valid 4 digit year.";
        header("Location: assessments.php");
        exit();
    }

    if (!is_numeric($tax_amount) || (float)$tax_amount <= 0) {
        $_SESSION['error'] = "Tax amount must be greater than zero.";
        header("Location: assessments.php");
        exit();
    }

    $chk_sql = "SELECT COUNT(*) AS total FROM TaxAssessments WHERE property_id = ? AND assessment_year = ?";
    $chk_stmt = mysqli_prepare($con, $chk_sql);
    $year_int = (int)$year;
    mysqli_stmt_bind_param($chk_stmt, "ii", $property_id, $year_int);
    mysqli_stmt_execute($chk_stmt);
    $chk_res = mysqli_stmt_get_result($chk_stmt);
    $chk_row = mysqli_fetch_assoc($chk_res);
    mysqli_stmt_close($chk_stmt);

    if ($chk_row && (int)$chk_row['total'] > 0) {
        $_SESSION['error'] = "An assessment for this property and year already exists.";
        header("Location: assessments.php");
        exit();
    }

    $sql = "INSERT INTO TaxAssessments (property_id, assessment_year, tax_amount) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        $tax_val = (float)$tax_amount;
        mysqli_stmt_bind_param($stmt, "iid", $property_id, $year_int, $tax_val);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Tax assessment created successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: assessments.php");
    exit();
}

$filter_year = isset($_GET['year']) ? trim($_GET['year']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowed_statuses = ['Paid', 'Partial', 'Unpaid'];

$base_query = "SELECT a.assessment_id, o.owner_name, p.property_address, a.assessment_year,
                      a.tax_amount,
                      IFNULL(x.paid, 0) AS paid,
                      a.tax_amount - IFNULL(x.paid, 0) AS due,
                      CASE
                          WHEN IFNULL(x.paid, 0) = 0 THEN 'Unpaid'
                          WHEN a.tax_amount - IFNULL(x.paid, 0) <= 0 THEN 'Paid'
                          ELSE 'Partial'
                      END AS status
               FROM TaxAssessments a
               JOIN Properties p ON a.property_id = p.property_id
               JOIN PropertyOwners o ON p.owner_id = o.owner_id
               LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
                          FROM Payments GROUP BY assessment_id) x
                      ON a.assessment_id = x.assessment_id ";

if ($filter_year !== '' && in_array($filter_status, $allowed_statuses, true)) {
    $sql = $base_query . " WHERE a.assessment_year = ? HAVING status = ? ORDER BY a.assessment_year DESC, a.assessment_id DESC";
    $stmt = mysqli_prepare($con, $sql);
    $f_year = (int)$filter_year;
    mysqli_stmt_bind_param($stmt, "is", $f_year, $filter_status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} elseif ($filter_year !== '') {
    $sql = $base_query . " WHERE a.assessment_year = ? ORDER BY a.assessment_year DESC, a.assessment_id DESC";
    $stmt = mysqli_prepare($con, $sql);
    $f_year = (int)$filter_year;
    mysqli_stmt_bind_param($stmt, "i", $f_year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} elseif (in_array($filter_status, $allowed_statuses, true)) {
    $sql = $base_query . " HAVING status = ? ORDER BY a.assessment_year DESC, a.assessment_id DESC";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $filter_status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = $base_query . " ORDER BY a.assessment_year DESC, a.assessment_id DESC";
    $result = mysqli_query($con, $sql);
}

$props_sql = "SELECT p.property_id, p.property_address, p.property_type, o.owner_name 
              FROM Properties p 
              JOIN PropertyOwners o ON p.owner_id = o.owner_id 
              ORDER BY o.owner_name ASC, p.property_address ASC";
$props_result = mysqli_query($con, $props_sql);

$years_sql = "SELECT DISTINCT assessment_year FROM TaxAssessments ORDER BY assessment_year DESC";
$years_result = mysqli_query($con, $years_sql);

include 'includes/header.php';
?>

<h1 class="page-title">Tax Assessments</h1>

<div class="card-box">
    <h2 class="section-title">Create New Assessment</h2>
    <form method="POST" action="assessments.php">
        <div class="form-group">
            <label for="property_id">Select Property *</label>
            <select id="property_id" name="property_id" class="form-control" required>
                <option value="">-- Choose Property --</option>
                <?php while ($pr = mysqli_fetch_assoc($props_result)): ?>
                    <option value="<?php echo (int)$pr['property_id']; ?>">
                        <?php echo htmlspecialchars($pr['owner_name'] . ' - ' . $pr['property_address'] . ' (' . $pr['property_type'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="assessment_year">Assessment Year (YYYY) *</label>
            <input type="number" id="assessment_year" name="assessment_year" class="form-control" required min="2000" max="2099" value="<?php echo date('Y'); ?>">
        </div>
        <div class="form-group">
            <label for="tax_amount">Tax Amount (Rs.) *</label>
            <input type="number" step="0.01" id="tax_amount" name="tax_amount" class="form-control" required min="1">
        </div>
        <button type="submit" class="btn">Save Assessment</button>
    </form>
</div>

<div class="filter-box">
    <form method="GET" action="assessments.php" class="form-inline">
        <div class="form-group">
            <label for="year">Filter by Year</label>
            <select id="year" name="year" class="form-control">
                <option value="">All Years</option>
                <?php while ($y = mysqli_fetch_assoc($years_result)): ?>
                    <option value="<?php echo (int)$y['assessment_year']; ?>" <?php if ($filter_year === (string)$y['assessment_year']) echo 'selected'; ?>>
                        <?php echo (int)$y['assessment_year']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Filter by Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                <?php foreach ($allowed_statuses as $st): ?>
                    <option value="<?php echo htmlspecialchars($st); ?>" <?php if ($filter_status === $st) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($st); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        <?php if ($filter_year !== '' || $filter_status !== ''): ?>
            <a href="assessments.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<h2 class="section-title">Assessment List</h2>
<?php if ($result && mysqli_num_rows($result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Owner</th>
                    <th>Property Address</th>
                    <th>Year</th>
                    <th class="text-right">Tax (Rs.)</th>
                    <th class="text-right">Paid (Rs.)</th>
                    <th class="text-right">Due (Rs.)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo (int)$row['assessment_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_address']); ?></td>
                        <td><?php echo (int)$row['assessment_year']; ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['tax_amount'], 2); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['paid'], 2); ?></td>
                        <td class="text-right"><?php echo number_format((float)$row['due'], 2); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Paid'): ?>
                                <span class="badge badge-paid">Paid</span>
                            <?php elseif ($row['status'] === 'Partial'): ?>
                                <span class="badge badge-partial">Partial</span>
                            <?php else: ?>
                                <span class="badge badge-unpaid">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-links">
                            <?php if ((float)$row['paid'] == 0): ?>
                                <a href="assessment_edit.php?id=<?php echo (int)$row['assessment_id']; ?>">Edit</a>
                            <?php endif; ?>
                            <a href="delete.php?type=assessment&id=<?php echo (int)$row['assessment_id']; ?>" class="text-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No assessments found.</div>
<?php endif; ?>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
include 'includes/footer.php';
?>
