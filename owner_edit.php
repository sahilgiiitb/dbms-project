<?php
include 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid owner ID.";
    header("Location: owners.php");
    exit();
}

$sql = "SELECT * FROM PropertyOwners WHERE owner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$owner = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$owner) {
    $_SESSION['error'] = "Record not found.";
    header("Location: owners.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['owner_name']) ? trim($_POST['owner_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($name === '') {
        $_SESSION['error'] = "Owner name is required.";
        header("Location: owner_edit.php?id=" . $id);
        exit();
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['error'] = "Phone number must be exactly 10 digits.";
        header("Location: owner_edit.php?id=" . $id);
        exit();
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: owner_edit.php?id=" . $id);
        exit();
    }

    $sql = "UPDATE PropertyOwners SET owner_name = ?, phone = ?, email = ? WHERE owner_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        $email_param = ($email === '') ? null : $email;
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $email_param, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Owner updated successfully.";
            mysqli_stmt_close($stmt);
            header("Location: owners.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong, please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Something went wrong, please try again.";
    }

    header("Location: owner_edit.php?id=" . $id);
    exit();
}

include 'includes/header.php';
?>

<h1 class="page-title">Edit Owner</h1>

<div class="card-box">
    <form method="POST" action="owner_edit.php?id=<?php echo $id; ?>">
        <div class="form-group">
            <label for="owner_name">Owner Name *</label>
            <input type="text" id="owner_name" name="owner_name" class="form-control" required maxlength="100" value="<?php echo htmlspecialchars($owner['owner_name']); ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone Number (10 digits) *</label>
            <input type="text" id="phone" name="phone" class="form-control" required pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($owner['phone']); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($owner['email'] ?? ''); ?>">
        </div>
        <div class="confirm-actions">
            <button type="submit" class="btn">Update Owner</button>
            <a href="owners.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>
