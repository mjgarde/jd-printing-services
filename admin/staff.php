<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'staff';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_submit'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "INSERT INTO staff (full_name, age, address, contact_number, email, username, password)
            VALUES ('$full_name', '$age', '$address', '$contact_number', '$email', '$username', '$password')";
    mysqli_query($conn, $sql);
    header("Location: staff.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_submit'])) {
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if ($password !== '') {
        $password = mysqli_real_escape_string($conn, $password);
        $sql = "UPDATE staff SET full_name='$full_name', age='$age', address='$address', contact_number='$contact_number', email='$email', username='$username', password='$password' WHERE id='$staff_id'";
    } else {
        $sql = "UPDATE staff SET full_name='$full_name', age='$age', address='$address', contact_number='$contact_number', email='$email', username='$username' WHERE id='$staff_id'";
    }

    mysqli_query($conn, $sql);
    header("Location: staff.php");
    exit();
}

if (isset($_GET['delete'])) {
    $staff_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM staff WHERE id='$staff_id'");
    header("Location: staff.php");
    exit();
}

$staffList = [];
$result = mysqli_query($conn, "SELECT * FROM staff ORDER BY id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staffList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-topbar">
            <div>
                <h1>Staff</h1>
                <p>Manage staff accounts.</p>
            </div>
            <button type="button" class="admin-btn" onclick="openModal('addStaffModal')">
                <i class="fa-solid fa-plus"></i> Add Staff
            </button>
        </div>

        <div class="panel">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staffList) === 0) { ?>
                        <tr>
                            <td colspan="7" class="admin-table-empty">No staff members yet.</td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($staffList as $staff) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['age']); ?></td>
                            <td><?php echo htmlspecialchars($staff['address']); ?></td>
                            <td><?php echo htmlspecialchars($staff['contact_number']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><?php echo htmlspecialchars($staff['username']); ?></td>
                            <td class="admin-table-actions">
                                <button type="button" class="icon-btn"
                                    onclick="openEditModal(
                                        '<?php echo $staff['id']; ?>',
                                        '<?php echo htmlspecialchars($staff['full_name'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($staff['age'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($staff['address'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($staff['contact_number'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($staff['email'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($staff['username'], ENT_QUOTES); ?>'
                                    )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="staff.php?delete=<?php echo $staff['id']; ?>" class="icon-btn icon-btn-danger" onclick="return confirm('Remove this staff member?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal-overlay" id="addStaffModal">
    <div class="modal-box">
        <button type="button" class="modal-close" onclick="closeModal('addStaffModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Add Staff</h2>
        <p class="modal-sub">Create a new staff account.</p>

        <form method="POST" action="staff.php" class="admin-form">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" min="1" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" required>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" required>
            </div>
            <button type="submit" name="add_submit" class="admin-btn admin-btn-full">Add Staff</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editStaffModal">
    <div class="modal-box">
        <button type="button" class="modal-close" onclick="closeModal('editStaffModal')"><i class="fa-solid fa-xmark"></i></button>
        <h2>Edit Staff</h2>
        <p class="modal-sub">Update staff account details.</p>

        <form method="POST" action="staff.php" class="admin-form">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="edit_full_name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" id="edit_age" min="1" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="edit_contact_number" required>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" id="edit_address" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" id="edit_password" placeholder="Leave blank to keep current password">
            </div>
            <button type="submit" name="edit_submit" class="admin-btn admin-btn-full">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function openEditModal(id, fullName, age, address, contact, email, username) {
    document.getElementById('edit_staff_id').value = id;
    document.getElementById('edit_full_name').value = fullName;
    document.getElementById('edit_age').value = age;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_contact_number').value = contact;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_password').value = '';
    openModal('editStaffModal');
}

window.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});
</script>

</body>
</html>