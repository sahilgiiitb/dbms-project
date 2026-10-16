<?php
include("db.php");

$error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: centers.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['center_id']);
    $name = trim($_POST['center_name']);
    $district = trim($_POST['district']);
    $state = trim($_POST['state']);

    if (empty($name) || empty($district) || empty($state)) {
        $error = "All fields are required.";
    } else {
        $stmt = mysqli_prepare($con, "UPDATE VaccinationCenters SET center_name = ?, district = ?, state = ? WHERE center_id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $district, $state, $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: centers.php?msg=" . urlencode("Vaccination center updated successfully."));
            exit;
        } else {
            $error = "Failed to update center.";
            mysqli_stmt_close($stmt);
        }
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM VaccinationCenters WHERE center_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$center = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$center) {
    header("Location: centers.php?error=" . urlencode("Center not found."));
    exit;
}

include("header.php");
?>

<h2>Edit Vaccination Center</h2>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <form method="POST" action="center_edit.php?id=<?php echo $id; ?>">
        <input type="hidden" name="center_id" value="<?php echo $center['center_id']; ?>">
        <div class="form-group">
            <label>Center Name</label>
            <input type="text" name="center_name" value="<?php echo htmlspecialchars($center['center_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>District</label>
            <input type="text" name="district" value="<?php echo htmlspecialchars($center['district']); ?>" required>
        </div>
        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($center['state']); ?>" required>
        </div>
        <button type="submit" class="btn">Update Center</button>
        <a href="centers.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include("footer.php");
?>
