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
        $name = trim($_POST['vaccine_name']);
        $manufacturer = trim($_POST['manufacturer']);
        $doses = intval($_POST['doses_required']);

        if (empty($name) || empty($manufacturer) || $doses <= 0) {
            $error = "All fields are required and doses must be greater than zero.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO Vaccines (vaccine_name, manufacturer, doses_required) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $name, $manufacturer, $doses);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: vaccines.php?msg=" . urlencode("Vaccine added successfully."));
                exit;
            } else {
                $error = "Failed to add vaccine.";
                mysqli_stmt_close($stmt);
            }
        }
    } elseif ($action == "delete") {
        $id = intval($_POST['vaccine_id']);

        $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM Vaccinations WHERE vaccine_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            header("Location: vaccines.php?error=" . urlencode("Cannot delete vaccine: vaccination records exist."));
            exit;
        } else {
            $stmt = mysqli_prepare($con, "DELETE FROM Vaccines WHERE vaccine_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: vaccines.php?msg=" . urlencode("Vaccine deleted successfully."));
                exit;
            } else {
                mysqli_stmt_close($stmt);
                header("Location: vaccines.php?error=" . urlencode("Failed to delete vaccine."));
                exit;
            }
        }
    }
}

$result = mysqli_query($con, "SELECT * FROM Vaccines ORDER BY vaccine_id DESC");

include("header.php");
?>

<h2>Manage Vaccines</h2>

<?php if (!empty($msg)) { ?>
    <div class="success"><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="card">
    <h3>Add New Vaccine</h3>
    <form method="POST" action="vaccines.php">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Vaccine Name</label>
            <input type="text" name="vaccine_name" required>
        </div>
        <div class="form-group">
            <label>Manufacturer</label>
            <input type="text" name="manufacturer" required>
        </div>
        <div class="form-group">
            <label>Doses Required</label>
            <input type="number" name="doses_required" min="1" value="1" required>
        </div>
        <button type="submit" class="btn">Add Vaccine</button>
    </form>
</div>

<div class="card">
    <h3>Vaccine Inventory</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Vaccine Name</th>
                <th>Manufacturer</th>
                <th>Doses Required</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vaccine_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer']); ?></td>
                        <td><?php echo htmlspecialchars($row['doses_required']); ?></td>
                        <td>
                            <a href="vaccine_edit.php?id=<?php echo $row['vaccine_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="vaccines.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="vaccine_id" value="<?php echo $row['vaccine_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No vaccines found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include("footer.php");
?>
