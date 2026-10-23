<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['owner_name']) ? trim($_POST['owner_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($name === '') {
        $_SESSION['error'] = "Owner name is required.";
        header("Location: owners.php");
        exit();
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['error'] = "Phone number must be exactly 10 digits.";
        header("Location: owners.php");
        exit();
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: owners.php");
        exit();
    }

    $sql = "INSERT INTO PropertyOwners (owner_name, phone, email) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        $email_param = ($email === '') ? null : $email;
        mysqli_stmt_bind_param($stmt, "sss", $name, $phone, $email_param);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Owner added successfully.";
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: owners.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $sql = "SELECT * FROM PropertyOwners WHERE owner_name LIKE ? OR phone = ? ORDER BY owner_id DESC";
    $stmt = mysqli_prepare($con, $sql);
    $search_like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ss", $search_like, $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM PropertyOwners ORDER BY owner_id DESC";
    $result = mysqli_query($con, $sql);
}

include 'includes/header.php';
?>

<h1 class="page-title">Property Owners</h1>

<div class="card-box">
    <h2 class="section-title">Add New Owner</h2>
    <form method="POST" action="owners.php">
        <div class="form-group">
            <label for="owner_name">Owner Name *</label>
            <input type="text" id="owner_name" name="owner_name" class="form-control" required maxlength="100">
        </div>
        <div class="form-group">
            <label for="phone">Phone Number (10 digits) *</label>
            <input type="text" id="phone" name="phone" class="form-control" required pattern="[0-9]{10}" maxlength="10">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100">
        </div>
        <button type="submit" class="btn">Save Owner</button>
    </form>
</div>

<div class="filter-box">
    <form method="GET" action="owners.php" class="form-inline">
        <div class="form-group">
            <label for="search">Search Owner</label>
            <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter name or phone">
        </div>
        <button type="submit" class="btn">Search</button>
        <?php if ($search !== ''): ?>
            <a href="owners.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<h2 class="section-title">Owner List</h2>
<?php if ($result && mysqli_num_rows($result) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo (int)$row['owner_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                        <td class="action-links">
                            <a href="owner_edit.php?id=<?php echo (int)$row['owner_id']; ?>">Edit</a>
                            <a href="delete.php?type=owner&id=<?php echo (int)$row['owner_id']; ?>" class="text-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-msg">No owners found.</div>
<?php endif; ?>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
include 'includes/footer.php';
?>
