<?php
include("db.php");

$error = "";
$msg = "";

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == "add") {
        $name = trim($_POST['center_name']);
        $district = trim($_POST['district']);
        $state = trim($_POST['state']);

        if (empty($name) || empty($district) || empty($state)) {
            $error = "All fields are required.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO VaccinationCenters (center_name, district, state) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $name, $district, $state);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: centers.php?msg=" . urlencode("Vaccination center added successfully."));
                exit;
            } else {
                $error = "Failed to add center.";
                mysqli_stmt_close($stmt);
            }
        }
    } elseif ($action == "delete") {
        $id = intval($_POST['center_id']);

        $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM Vaccinations WHERE center_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            header("Location: centers.php?error=" . urlencode("Cannot delete center: vaccination records exist."));
            exit;
        } else {
            $stmt = mysqli_prepare($con, "DELETE FROM VaccinationCenters WHERE center_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: centers.php?msg=" . urlencode("Vaccination center deleted successfully."));
                exit;
            } else {
                mysqli_stmt_close($stmt);
                header("Location: centers.php?error=" . urlencode("Failed to delete center."));
                exit;
            }
        }
    }
}

$state_filter = isset($_GET['state']) ? trim($_GET['state']) : '';

$states_result = mysqli_query($con, "SELECT DISTINCT state FROM VaccinationCenters ORDER BY state ASC");

if ($state_filter != '') {
    $stmt = mysqli_prepare($con, "SELECT * FROM VaccinationCenters WHERE state = ? ORDER BY center_id DESC");
    mysqli_stmt_bind_param($stmt, "s", $state_filter);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($con, "SELECT * FROM VaccinationCenters ORDER BY center_id DESC");
}

include("header.php");
?>

<h2>Manage Vaccination Centers</h2>

<?php if (!empty($msg)) { ?>
    <div class="success"><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <h3>Add New Vaccination Center</h3>
    <form method="POST" action="centers.php">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Center Name</label>
            <input type="text" name="center_name" required>
        </div>
        <div class="form-group">
            <label>District</label>
            <input type="text" name="district" required>
        </div>
        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" required>
        </div>
        <button type="submit" class="btn">Add Center</button>
    </form>
</div>

<div class="card">
    <h3>Centers Directory</h3>
    <form method="GET" action="centers.php" class="search-bar">
        <select name="state">
            <option value="">Select State</option>
            <?php while ($s = mysqli_fetch_assoc($states_result)) { ?>
                <option value="<?php echo htmlspecialchars($s['state']); ?>" <?php if ($state_filter == $s['state']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($s['state']); ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="btn">Filter</button>
        <?php if ($state_filter != '') { ?>
            <a href="centers.php" class="btn btn-secondary">Reset</a>
        <?php } ?>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Center Name</th>
                <th>District</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['center_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['center_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['district']); ?></td>
                        <td><?php echo htmlspecialchars($row['state']); ?></td>
                        <td>
                            <a href="center_edit.php?id=<?php echo $row['center_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="centers.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="center_id" value="<?php echo $row['center_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No centers found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include("footer.php");
?>
