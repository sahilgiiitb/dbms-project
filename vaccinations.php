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

$today = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == "add") {
        $citizen_id = isset($_POST['citizen_id']) ? intval($_POST['citizen_id']) : 0;
        $vaccine_id = isset($_POST['vaccine_id']) ? intval($_POST['vaccine_id']) : 0;
        $center_id = isset($_POST['center_id']) ? intval($_POST['center_id']) : 0;
        $date = isset($_POST['vaccination_date']) ? trim($_POST['vaccination_date']) : '';

        if ($citizen_id <= 0 || $vaccine_id <= 0 || $center_id <= 0 || empty($date)) {
            $error = "All fields are required.";
        } elseif ($date > $today) {
            $error = "Vaccination date cannot be in the future.";
        } else {
            $stmt = mysqli_prepare($con, "SELECT doses_required FROM Vaccines WHERE vaccine_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $vaccine_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $doses_required);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM Vaccinations WHERE citizen_id = ? AND vaccine_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $citizen_id, $vaccine_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $taken_count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($taken_count >= $doses_required) {
                $error = "Citizen has already received all required doses (" . $doses_required . ") for this vaccine.";
            } else {
                $stmt = mysqli_prepare($con, "INSERT INTO Vaccinations (citizen_id, vaccine_id, center_id, vaccination_date) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiis", $citizen_id, $vaccine_id, $center_id, $date);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: vaccinations.php?msg=" . urlencode("Vaccination recorded successfully."));
                    exit;
                } else {
                    $error = "Failed to record vaccination.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif ($action == "delete") {
        $id = intval($_POST['vaccination_id']);
        $stmt = mysqli_prepare($con, "DELETE FROM Vaccinations WHERE vaccination_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: vaccinations.php?msg=" . urlencode("Vaccination record deleted successfully."));
            exit;
        } else {
            mysqli_stmt_close($stmt);
            header("Location: vaccinations.php?error=" . urlencode("Failed to delete vaccination record."));
            exit;
        }
    }
}

$citizens_list = mysqli_query($con, "SELECT citizen_id, citizen_name, aadhaar_no FROM Citizens ORDER BY citizen_name ASC");
$vaccines_list = mysqli_query($con, "SELECT vaccine_id, vaccine_name, doses_required FROM Vaccines ORDER BY vaccine_name ASC");
$centers_list = mysqli_query($con, "SELECT center_id, center_name FROM VaccinationCenters ORDER BY center_name ASC");

$query = "SELECT v.vaccination_id, c.citizen_name, c.aadhaar_no, vc.vaccine_name, ce.center_name, v.vaccination_date
          FROM Vaccinations v
          JOIN Citizens c ON v.citizen_id = c.citizen_id
          JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
          JOIN VaccinationCenters ce ON v.center_id = ce.center_id
          ORDER BY v.vaccination_date DESC, v.vaccination_id DESC";
$result = mysqli_query($con, $query);

include("header.php");
?>

<h2>Vaccination Records</h2>

<?php if (!empty($msg)) { ?>
    <div class="success"><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <h3>Record New Vaccination</h3>
    <form method="POST" action="vaccinations.php">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label>Select Citizen</label>
            <select name="citizen_id" required>
                <option value="">Select Citizen</option>
                <?php while ($c = mysqli_fetch_assoc($citizens_list)) { ?>
                    <option value="<?php echo $c['citizen_id']; ?>">
                        <?php echo htmlspecialchars($c['citizen_name'] . ' (Aadhaar: ' . $c['aadhaar_no'] . ')'); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Select Vaccine</label>
            <select name="vaccine_id" required>
                <option value="">Select Vaccine</option>
                <?php while ($v = mysqli_fetch_assoc($vaccines_list)) { ?>
                    <option value="<?php echo $v['vaccine_id']; ?>">
                        <?php echo htmlspecialchars($v['vaccine_name'] . ' (' . $v['doses_required'] . ' doses required)'); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Select Center</label>
            <select name="center_id" required>
                <option value="">Select Center</option>
                <?php while ($ce = mysqli_fetch_assoc($centers_list)) { ?>
                    <option value="<?php echo $ce['center_id']; ?>">
                        <?php echo htmlspecialchars($ce['center_name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Vaccination Date</label>
            <input type="date" name="vaccination_date" max="<?php echo $today; ?>" value="<?php echo $today; ?>" required>
        </div>

        <button type="submit" class="btn">Save Record</button>
    </form>
</div>

<div class="card">
    <h3>All Vaccination Logs</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Citizen Name</th>
                <th>Aadhaar Number</th>
                <th>Vaccine Name</th>
                <th>Center</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vaccination_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['aadhaar_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['center_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccination_date']); ?></td>
                        <td>
                            <form method="POST" action="vaccinations.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="vaccination_id" value="<?php echo $row['vaccination_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No vaccination records found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include("footer.php");
?>
