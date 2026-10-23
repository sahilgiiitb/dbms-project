<?php
include 'includes/db.php';

$allowed_types = ['Residential', 'Commercial', 'Industrial', 'Agricultural'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = isset($_POST['owner_id']) ? (int)$_POST['owner_id'] : 0;
    $address = isset($_POST['property_address']) ? trim($_POST['property_address']) : '';
    $type = isset($_POST['property_type']) ? trim($_POST['property_type']) : '';

    if ($owner_id <= 0) {
        $_SESSION['error'] = "Please select an owner.";
        header("Location: properties.php");
        exit();
    }

    if ($address === '') {
        $_SESSION['error'] = "Property address is required.";
        header("Location: properties.php");
        exit();
    }

    if (!in_array($type, $allowed_types, true)) {
        $_SESSION['error'] = "Invalid property type selected.";
        header("Location: properties.php");
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
        header("Location: properties.php");
        exit();
    }
    mysqli_stmt_close($chk_stmt);

    $sql = "INSERT INTO Properties (owner_id, property_address, property_type) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $owner_id, $address, $type);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Property added successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: properties.php");
    exit();
}

$selected_type = isset($_GET['type']) ? trim($_GET['type']) : '';

if (in_array($selected_type, $allowed_types, true)) {
    $sql = "SELECT p.property_id, p.property_address, p.property_type, o.owner_name 
            FROM Properties p 
            JOIN PropertyOwners o ON p.owner_id = o.owner_id 
            WHERE p.property_type = ? 
            ORDER BY p.property_id DESC";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $selected_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $selected_type = '';
    $sql = "SELECT p.property_id, p.property_address, p.property_type, o.owner_name 
            FROM Properties p 
            JOIN PropertyOwners o ON p.owner_id = o.owner_id 
            ORDER BY p.property_id DESC";
    $result = mysqli_query($con, $sql);
}

$owners_sql = "SELECT owner_id, owner_name FROM PropertyOwners ORDER BY owner_name ASC";
$owners_result = mysqli_query($con, $owners_sql);

include 'includes/header.php';
?>

<h1 class="page-title">Properties</h1>

<div class="card-box">
    <h2 class="section-title">Add New Property</h2>
    <form method="POST" action="properties.php">
        <div class="form-group">
            <label for="owner_id">Select Owner *</label>
            <select id="owner_id" name="owner_id" class="form-control" required>
                <option value="">-- Choose Owner --</option>
                <?php while ($o = mysqli_fetch_assoc($owners_result)): ?>
                    <option value="<?php echo (int)$o['owner_id']; ?>"><?php echo htmlspecialchars($o['owner_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="property_address">Property Address *</label>
            <input type="text" id="property_address" name="property_address" class="form-control" required maxlength="255">
        </div>
        <div class="form-group">
            <label for="property_type">Property Type *</label>
            <select id="property_type" name="property_type" class="form-control" required>
                <option value="">-- Choose Type --</option>
                <?php foreach ($allowed_types as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Save Property</button>
    </form>
</div>

<div class="filter-box">
    <form method="GET" action="properties.php" class="form-inline">
        <div class="form-group">
            <label for="type">Filter by Property Type</label>
            <select id="type" name="type" class="form-control">
                <option value="">All Types</option>
                <?php foreach ($allowed_types as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php if ($selected_type === $t) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        <?php if ($selected_type !== ''): ?>
            <a href="properties.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<h2 class="section-title">Property List</h2>
<?php if ($result && mysqli_num_rows($result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Owner Name</th>
                    <th>Address</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo (int)$row['property_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_type']); ?></td>
                        <td class="action-links">
                            <a href="property_edit.php?id=<?php echo (int)$row['property_id']; ?>">Edit</a>
                            <a href="delete.php?type=property&id=<?php echo (int)$row['property_id']; ?>" class="text-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No properties found.</div>
<?php endif; ?>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
include 'includes/footer.php';
?>
