<?php
include 'includes/db.php';

$allowed_types = ['Residential', 'Commercial', 'Industrial', 'Agricultural'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid property ID.";
    header("Location: properties.php");
    exit();
}

$sql = "SELECT * FROM Properties WHERE property_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$property = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$property) {
    $_SESSION['error'] = "Record not found.";
    header("Location: properties.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = isset($_POST['owner_id']) ? (int)$_POST['owner_id'] : 0;
    $address = isset($_POST['property_address']) ? trim($_POST['property_address']) : '';
    $type = isset($_POST['property_type']) ? trim($_POST['property_type']) : '';

    if ($owner_id <= 0) {
        $_SESSION['error'] = "Please select an owner.";
        header("Location: property_edit.php?id=" . $id);
        exit();
    }

    if ($address === '') {
        $_SESSION['error'] = "Property address is required.";
        header("Location: property_edit.php?id=" . $id);
        exit();
    }

    if (!in_array($type, $allowed_types, true)) {
        $_SESSION['error'] = "Invalid property type selected.";
        header("Location: property_edit.php?id=" . $id);
        exit();
    }

    $chk_sql = "SELECT owner_id FROM PropertyOwners WHERE owner_id = ?";
    $chk_stmt = mysqli_prepare($con, $chk_sql);
    mysqli_stmt_bind_param($chk_stmt, "i", $owner_id);
    mysqli_stmt_execute($chk_stmt);
    $chk_res = mysqli_stmt_get_result($chk_stmt);
    if (!mysqli_fetch_assoc($chk_res)) {
        $_SESSION['error'] = "Selected owner does not exist.";
        mysqli_stmt_close($chk_stmt);
        header("Location: property_edit.php?id=" . $id);
        exit();
    }
    mysqli_stmt_close($chk_stmt);

    $sql = "UPDATE Properties SET owner_id = ?, property_address = ?, property_type = ? WHERE property_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issi", $owner_id, $address, $type, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Property updated successfully.";
            mysqli_stmt_close($stmt);
            header("Location: properties.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: property_edit.php?id=" . $id);
    exit();
}

$owners_sql = "SELECT owner_id, owner_name FROM PropertyOwners ORDER BY owner_name ASC";
$owners_result = mysqli_query($con, $owners_sql);

include 'includes/header.php';
?>

<h1 class="page-title">Edit Property</h1>

<div class="card-box">
    <form method="POST" action="property_edit.php?id=<?php echo $id; ?>">
        <div class="form-group">
            <label for="owner_id">Select Owner *</label>
            <select id="owner_id" name="owner_id" class="form-control" required>
                <?php while ($o = mysqli_fetch_assoc($owners_result)): ?>
                    <option value="<?php echo (int)$o['owner_id']; ?>" <?php if ((int)$property['owner_id'] === (int)$o['owner_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($o['owner_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="property_address">Property Address *</label>
            <input type="text" id="property_address" name="property_address" class="form-control" required maxlength="255" value="<?php echo htmlspecialchars($property['property_address']); ?>">
        </div>
        <div class="form-group">
            <label for="property_type">Property Type *</label>
            <select id="property_type" name="property_type" class="form-control" required>
                <?php foreach ($allowed_types as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php if ($property['property_type'] === $t) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="confirm-actions">
            <button type="submit" class="btn">Update Property</button>
            <a href="properties.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>
