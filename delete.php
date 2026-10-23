<?php
include 'includes/db.php';

$allowed_types = ['owner', 'property', 'assessment', 'payment'];

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if (!in_array($type, $allowed_types, true) || $id <= 0) {
    $_SESSION['error'] = "Invalid delete request.";
    header("Location: index.php");
    exit();
}

$redirect_urls = [
    'owner' => 'owners.php',
    'property' => 'properties.php',
    'assessment' => 'assessments.php',
    'payment' => 'payments.php'
];

$target_page = $redirect_urls[$type];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'owner') {
        $chk_sql = "SELECT COUNT(*) AS total FROM Properties WHERE owner_id = ?";
        $chk_stmt = mysqli_prepare($con, $chk_sql);
        mysqli_stmt_bind_param($chk_stmt, "i", $id);
        mysqli_stmt_execute($chk_stmt);
        $chk_res = mysqli_stmt_get_result($chk_stmt);
        $chk_row = mysqli_fetch_assoc($chk_res);
        mysqli_stmt_close($chk_stmt);

        if ($chk_row && (int)$chk_row['total'] > 0) {
            $_SESSION['error'] = "This owner has properties and cannot be deleted.";
            header("Location: " . $target_page);
            exit();
        }

        $del_sql = "DELETE FROM PropertyOwners WHERE owner_id = ?";
        $del_stmt = mysqli_prepare($con, $del_sql);
        mysqli_stmt_bind_param($del_stmt, "i", $id);
        if (mysqli_stmt_execute($del_stmt)) {
            $_SESSION['msg'] = "Owner deleted successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($del_stmt);

    } elseif ($type === 'property') {
        $chk_sql = "SELECT COUNT(*) AS total FROM TaxAssessments WHERE property_id = ?";
        $chk_stmt = mysqli_prepare($con, $chk_sql);
        mysqli_stmt_bind_param($chk_stmt, "i", $id);
        mysqli_stmt_execute($chk_stmt);
        $chk_res = mysqli_stmt_get_result($chk_stmt);
        $chk_row = mysqli_fetch_assoc($chk_res);
        mysqli_stmt_close($chk_stmt);

        if ($chk_row && (int)$chk_row['total'] > 0) {
            $_SESSION['error'] = "This property has tax assessments and cannot be deleted.";
            header("Location: " . $target_page);
            exit();
        }

        $del_sql = "DELETE FROM Properties WHERE property_id = ?";
        $del_stmt = mysqli_prepare($con, $del_sql);
        mysqli_stmt_bind_param($del_stmt, "i", $id);
        if (mysqli_stmt_execute($del_stmt)) {
            $_SESSION['msg'] = "Property deleted successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($del_stmt);

    } elseif ($type === 'assessment') {
        $chk_sql = "SELECT COUNT(*) AS total FROM Payments WHERE assessment_id = ?";
        $chk_stmt = mysqli_prepare($con, $chk_sql);
        mysqli_stmt_bind_param($chk_stmt, "i", $id);
        mysqli_stmt_execute($chk_stmt);
        $chk_res = mysqli_stmt_get_result($chk_stmt);
        $chk_row = mysqli_fetch_assoc($chk_res);
        mysqli_stmt_close($chk_stmt);

        if ($chk_row && (int)$chk_row['total'] > 0) {
            $_SESSION['error'] = "This assessment has payments and cannot be deleted.";
            header("Location: " . $target_page);
            exit();
        }

        $del_sql = "DELETE FROM TaxAssessments WHERE assessment_id = ?";
        $del_stmt = mysqli_prepare($con, $del_sql);
        mysqli_stmt_bind_param($del_stmt, "i", $id);
        if (mysqli_stmt_execute($del_stmt)) {
            $_SESSION['msg'] = "Assessment deleted successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($del_stmt);

    } elseif ($type === 'payment') {
        $del_sql = "DELETE FROM Payments WHERE payment_id = ?";
        $del_stmt = mysqli_prepare($con, $del_sql);
        mysqli_stmt_bind_param($del_stmt, "i", $id);
        if (mysqli_stmt_execute($del_stmt)) {
            $_SESSION['msg'] = "Payment deleted successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($del_stmt);
    }

    header("Location: " . $target_page);
    exit();
}

$item_description = "";
if ($type === 'owner') {
    $q = "SELECT owner_name, phone FROM PropertyOwners WHERE owner_id = ?";
    $st = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    $info = mysqli_fetch_assoc($r);
    mysqli_stmt_close($st);
    if ($info) {
        $item_description = "Owner: " . $info['owner_name'] . " (Phone: " . $info['phone'] . ")";
    }
} elseif ($type === 'property') {
    $q = "SELECT p.property_address, p.property_type, o.owner_name 
          FROM Properties p 
          JOIN PropertyOwners o ON p.owner_id = o.owner_id 
          WHERE p.property_id = ?";
    $st = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    $info = mysqli_fetch_assoc($r);
    mysqli_stmt_close($st);
    if ($info) {
        $item_description = "Property: " . $info['property_address'] . " (" . $info['property_type'] . "), Owner: " . $info['owner_name'];
    }
} elseif ($type === 'assessment') {
    $q = "SELECT a.assessment_year, a.tax_amount, p.property_address, o.owner_name 
          FROM TaxAssessments a 
          JOIN Properties p ON a.property_id = p.property_id 
          JOIN PropertyOwners o ON p.owner_id = o.owner_id 
          WHERE a.assessment_id = ?";
    $st = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    $info = mysqli_fetch_assoc($r);
    mysqli_stmt_close($st);
    if ($info) {
        $item_description = "Assessment #" . $id . ": Year " . $info['assessment_year'] . ", Tax Rs. " . number_format((float)$info['tax_amount'], 2) . " for " . $info['property_address'] . " (" . $info['owner_name'] . ")";
    }
} elseif ($type === 'payment') {
    $q = "SELECT payment_id, payment_date, amount_paid FROM Payments WHERE payment_id = ?";
    $st = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    $info = mysqli_fetch_assoc($r);
    mysqli_stmt_close($st);
    if ($info) {
        $item_description = "Payment #" . $info['payment_id'] . ": Rs. " . number_format((float)$info['amount_paid'], 2) . " on " . $info['payment_date'];
    }
}

if ($item_description === '') {
    $_SESSION['error'] = "Record not found.";
    header("Location: " . $target_page);
    exit();
}

include 'includes/header.php';
?>

<div class="confirm-box">
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete the following record?</p>
    <p><strong><?php echo htmlspecialchars($item_description); ?></strong></p>
    <p style="color: #666666; font-size: 13px;">This action cannot be undone. Records linked to other entries cannot be deleted.</p>
    
    <form method="POST" action="delete.php">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="confirm-actions">
            <button type="submit" class="btn btn-danger">Yes, Delete Record</button>
            <a href="<?php echo htmlspecialchars($target_page); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>
