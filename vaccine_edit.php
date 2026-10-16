<?php
include("db.php");

$error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: vaccines.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['vaccine_id']);
    $name = trim($_POST['vaccine_name']);
    $manufacturer = trim($_POST['manufacturer']);
    $doses = intval($_POST['doses_required']);

    if (empty($name) || empty($manufacturer) || $doses <= 0) {
        $error = "All fields are required and doses must be greater than zero.";
    } else {
        $stmt = mysqli_prepare($con, "UPDATE Vaccines SET vaccine_name = ?, manufacturer = ?, doses_required = ? WHERE vaccine_id = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $name, $manufacturer, $doses, $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: vaccines.php?msg=" . urlencode("Vaccine updated successfully."));
            exit;
        } else {
            $error = "Failed to update vaccine.";
            mysqli_stmt_close($stmt);
        }
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM Vaccines WHERE vaccine_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vaccine = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$vaccine) {
    header("Location: vaccines.php?error=" . urlencode("Vaccine not found."));
    exit;
}

include("header.php");
?>

<h2>Edit Vaccine</h2>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <form method="POST" action="vaccine_edit.php?id=<?php echo $id; ?>">
        <input type="hidden" name="vaccine_id" value="<?php echo $vaccine['vaccine_id']; ?>">
        <div class="form-group">
            <label>Vaccine Name</label>
            <input type="text" name="vaccine_name" value="<?php echo htmlspecialchars($vaccine['vaccine_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Manufacturer</label>
            <input type="text" name="manufacturer" value="<?php echo htmlspecialchars($vaccine['manufacturer']); ?>" required>
        </div>
        <div class="form-group">
            <label>Doses Required</label>
            <input type="number" name="doses_required" min="1" value="<?php echo htmlspecialchars($vaccine['doses_required']); ?>" required>
        </div>
        <button type="submit" class="btn">Update Vaccine</button>
        <a href="vaccines.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include("footer.php");
?>
