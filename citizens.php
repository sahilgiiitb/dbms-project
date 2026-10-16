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
            $stmt = mysqli_prepare($con, "SELECT citizen_id FROM Citizens WHERE aadhaar_no = ?");
            mysqli_stmt_bind_param($stmt, "s", $aadhaar);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "A citizen with this Aadhaar number already exists.";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($con, "INSERT INTO Citizens (citizen_name, aadhaar_no, mobile) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $name, $aadhaar, $mobile);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: citizens.php?msg=" . urlencode("Citizen registered successfully."));
                    exit;
                } else {
                    $error = "Failed to register citizen.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif ($action == "delete") {
        $id = intval($_POST['citizen_id']);

        $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM Vaccinations WHERE citizen_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            header("Location: citizens.php?error=" . urlencode("Cannot delete citizen: vaccination records exist."));
            exit;
        } else {
            $stmt = mysqli_prepare($con, "DELETE FROM Citizens WHERE citizen_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: citizens.php?msg=" . urlencode("Citizen deleted successfully."));
                exit;
            } else {
                mysqli_stmt_close($stmt);
                header("Location: citizens.php?error=" . urlencode("Failed to delete citizen."));
                exit;
            }
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search != '') {
    $param = "%" . $search . "%";
    $stmt = mysqli_prepare($con, "SELECT * FROM Citizens WHERE citizen_name LIKE ? OR aadhaar_no LIKE ? OR mobile LIKE ? ORDER BY citizen_id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $param, $param, $param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($con, "SELECT * FROM Citizens ORDER BY citizen_id DESC");
}

include("header.php");
?>

<h2>Manage Citizens</h2>

<?php if (!empty($msg)) { ?>
    <div class="success"><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <h3>Add New Citizen</h3>
    <form method="POST" action="citizens.php">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="citizen_name" required>
        </div>
        <div class="form-group">
            <label>Aadhaar Number (12 digits)</label>
            <input type="text" name="aadhaar_no" maxlength="12" pattern="[0-9]{12}" required>
        </div>
        <div class="form-group">
            <label>Mobile Number (10 digits)</label>
            <input type="text" name="mobile" maxlength="10" pattern="[0-9]{10}" required>
        </div>
        <button type="submit" class="btn">Register Citizen</button>
    </form>
</div>

<div class="card">
    <h3>Citizen Directory</h3>
    <form method="GET" action="citizens.php" class="search-bar">
        <input type="text" name="search" placeholder="Search by name, Aadhaar or mobile..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">Search</button>
        <?php if ($search != '') { ?>
            <a href="citizens.php" class="btn btn-secondary">Reset</a>
        <?php } ?>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Aadhaar Number</th>
                <th>Mobile</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['citizen_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['aadhaar_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                        <td>
                            <a href="citizen_edit.php?id=<?php echo $row['citizen_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="citizens.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="citizen_id" value="<?php echo $row['citizen_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No citizens found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include("footer.php");
?>
