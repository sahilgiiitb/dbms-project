<?php
include("db.php");

$error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: citizens.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['citizen_id']);
    $name = trim($_POST['citizen_name']);
    $aadhaar = trim($_POST['aadhaar_no']);
    $mobile = trim($_POST['mobile']);

    if (empty($name) || empty($aadhaar) || empty($mobile)) {
        $error = "All fields are required.";
    } elseif (strlen($aadhaar) != 12 || !ctype_digit($aadhaar)) {
        $error = "Aadhaar number must be exactly 12 digits.";
    } elseif (strlen($mobile) != 10 || !ctype_digit($mobile)) {
        $error = "Mobile number must be exactly 10 digits.";
    } else {
        $stmt = mysqli_prepare($con, "SELECT citizen_id FROM Citizens WHERE aadhaar_no = ? AND citizen_id != ?");
        mysqli_stmt_bind_param($stmt, "si", $aadhaar, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Another citizen with this Aadhaar number already exists.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($con, "UPDATE Citizens SET citizen_name = ?, aadhaar_no = ?, mobile = ? WHERE citizen_id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $name, $aadhaar, $mobile, $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: citizens.php?msg=" . urlencode("Citizen updated successfully."));
                exit;
            } else {
                $error = "Failed to update citizen.";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM Citizens WHERE citizen_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$citizen = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$citizen) {
    header("Location: citizens.php?error=" . urlencode("Citizen not found."));
    exit;
}

include("header.php");
?>

<h2>Edit Citizen</h2>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <form method="POST" action="citizen_edit.php?id=<?php echo $id; ?>">
        <input type="hidden" name="citizen_id" value="<?php echo $citizen['citizen_id']; ?>">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="citizen_name" value="<?php echo htmlspecialchars($citizen['citizen_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Aadhaar Number (12 digits)</label>
            <input type="text" name="aadhaar_no" maxlength="12" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($citizen['aadhaar_no']); ?>" required>
        </div>
        <div class="form-group">
            <label>Mobile Number (10 digits)</label>
            <input type="text" name="mobile" maxlength="10" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($citizen['mobile']); ?>" required>
        </div>
        <button type="submit" class="btn">Update Citizen</button>
        <a href="citizens.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include("footer.php");
?>
