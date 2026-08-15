<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'staff';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_submit'])) {
    $full_name = trim($_POST['full_name']);
    $age = trim($_POST['age']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "INSERT INTO staff (full_name, age, address, contact_number, email, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sisssss", $full_name, $age, $address, $contact_number, $email, $username, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: staff.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_submit'])) {
    $staff_id = trim($_POST['staff_id']);
    $full_name = trim($_POST['full_name']);
    $age = trim($_POST['age']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($password !== '') {
        $stmt = mysqli_prepare($conn, "UPDATE staff SET full_name=?, age=?, address=?, contact_number=?, email=?, username=?, password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sisssssi", $full_name, $age, $address, $contact_number, $email, $username, $password, $staff_id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE staff SET full_name=?, age=?, address=?, contact_number=?, email=?, username=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sissssi", $full_name, $age, $address, $contact_number, $email, $username, $staff_id);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: staff.php");
    exit();
}

if (isset($_GET['delete'])) {
    $staff_id = trim($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM staff WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $staff_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: staff.php");
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM staff");
$totalRow = mysqli_fetch_assoc($countResult);
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

$staffList = [];
$result = mysqli_query($conn, "SELECT * FROM staff ORDER BY id DESC LIMIT $limit OFFSET $offset");
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
<title>Staff Records | JD Printing Services</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../font/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
    background-color: #f0f1f3;
    color: #26292f;
    font-size: 13px;
}

.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

.admin-content {
    flex: 1;
    padding: 24px 28px;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #d6d9de;
}

.page-header-text h1 {
    font-size: 18px;
    font-weight: 600;
    color: #1c2027;
}

.breadcrumb {
    font-size: 12px;
    color: #7a8089;
    margin-top: 3px;
}

.breadcrumb span {
    color: #4c5b76;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    background-color: #33475a;
    color: #ffffff;
    border: 1px solid #33475a;
    border-radius: 3px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.btn-primary:hover {
    background-color: #263748;
}

.data-panel {
    background-color: #ffffff;
    border: 1px solid #d6d9de;
    border-radius: 3px;
}

.data-panel-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #e4e6ea;
    gap: 12px;
    flex-wrap: wrap;
}

.data-panel-toolbar h2 {
    font-size: 13.5px;
    font-weight: 600;
    color: #1c2027;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.record-count {
    font-size: 12px;
    color: #7a8089;
    white-space: nowrap;
}

.search-box {
    position: relative;
    width: 220px;
}

.search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: #9099a3;
}

.search-box input {
    width: 100%;
    padding: 6px 10px 6px 28px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-family: inherit;
    background-color: #fbfbfc;
}

.search-box input:focus {
    outline: none;
    border-color: #3a6ea5;
    background-color: #ffffff;
}

table.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #5c6470;
    padding: 9px 16px;
    background-color: #f5f6f8;
    border-bottom: 1px solid #d6d9de;
    border-top: 1px solid #d6d9de;
    white-space: nowrap;
}

.data-table tbody td {
    padding: 10px 16px;
    border-bottom: 1px solid #edeef1;
    color: #2c313b;
    vertical-align: middle;
}

.data-table tbody tr:hover {
    background-color: #f7f8fa;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.cell-name {
    font-weight: 600;
    color: #1c2027;
}

.cell-muted {
    color: #6b7280;
}

.username-tag {
    font-family: 'Consolas', Menlo, monospace;
    font-size: 12px;
    color: #3a6ea5;
}

.col-actions {
    width: 1%;
    white-space: nowrap;
    text-align: right;
}

.table-action-link {
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    color: #3a6ea5;
    margin-left: 12px;
    cursor: pointer;
    background: none;
    border: none;
    font-family: inherit;
}

.table-action-link:first-child {
    margin-left: 0;
}

.table-action-link:hover {
    text-decoration: underline;
}

.table-action-link.danger {
    color: #a3402a;
}

.empty-state {
    text-align: center;
    padding: 46px 16px;
    color: #9099a3;
    font-size: 13px;
}

.pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid #e4e6ea;
    font-size: 12px;
    color: #7a8089;
    flex-wrap: wrap;
    gap: 10px;
}

.pagination-links {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination-links a,
.pagination-links span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border: 1px solid #d6d9de;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    color: #3a6ea5;
    text-decoration: none;
    background-color: #ffffff;
    transition: background 0.15s, border-color 0.15s;
}

.pagination-links a:hover {
    background-color: #f0f4fa;
    border-color: #3a6ea5;
}

.pagination-links span.active {
    background-color: #33475a;
    border-color: #33475a;
    color: #ffffff;
    cursor: default;
}

.pagination-links span.disabled {
    color: #b0b8c4;
    border-color: #e4e6ea;
    cursor: default;
    background-color: #f9f9fa;
}

.pagination-info {
    font-size: 12px;
    color: #7a8089;
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(20, 24, 30, 0.45);
    z-index: 200;
    align-items: flex-start;
    justify-content: center;
    padding-top: 60px;
}

.modal-overlay.active {
    display: flex;
}

.modal-box {
    background-color: #ffffff;
    border-radius: 3px;
    width: 540px;
    max-width: 92%;
    max-height: 84vh;
    overflow-y: auto;
    border: 1px solid #c9ccd2;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background-color: #f5f6f8;
    border-bottom: 1px solid #d6d9de;
}

.modal-header h2 {
    font-size: 14.5px;
    font-weight: 600;
    color: #1c2027;
}

.modal-header p {
    font-size: 11.5px;
    color: #7a8089;
    margin-top: 2px;
}

.modal-body {
    padding: 20px;
}

.form-section-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #3a6ea5;
    padding-bottom: 6px;
    margin-bottom: 12px;
    border-bottom: 1px solid #e4e6ea;
}

.form-section-label.spaced {
    margin-top: 18px;
}

.field-row {
    display: flex;
    gap: 14px;
}

.field-group {
    flex: 1;
    min-width: 0;
    margin-bottom: 13px;
}

.field-group.narrow {
    flex: 0 0 90px;
}

.field-group label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: #3f4b5c;
    margin-bottom: 5px;
}

.field-group input {
    width: 100%;
    padding: 7px 9px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-family: inherit;
    color: #26292f;
    background-color: #fbfbfc;
}

.field-group input:focus {
    outline: none;
    border-color: #3a6ea5;
    background-color: #ffffff;
}

.field-hint {
    font-size: 11px;
    color: #9099a3;
    margin-top: 4px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 14px;
    margin-top: 6px;
    border-top: 1px solid #e4e6ea;
}

.btn-secondary {
    padding: 8px 16px;
    background-color: #ffffff;
    color: #4c5560;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary:hover {
    background-color: #f5f6f8;
}

@media (max-width: 600px) {
    .field-row {
        flex-direction: column;
        gap: 0;
    }

    .field-group.narrow {
        flex: 1;
    }

    .pagination-bar {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

.icon-btn {
    background: none;
    border: none;
    padding: 4px 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #3a6ea5;
    font-family: inherit;
    transition: color 0.15s;
}

.icon-btn:hover {
    color: #1f4a73;
}

.icon-btn.danger {
    color: #a3402a;
}

.icon-btn.danger:hover {
    color: #7a2f1e;
}

.icon-btn svg {
    width: 16px;
    height: 16px;
    vertical-align: middle;
    fill: currentColor;
}

.action-group {
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: flex-end;
}

.action-divider {
    color: #c9ccd2;
    font-size: 14px;
    font-weight: 300;
    padding: 0 2px;
}
</style>
</head>
<body>

<div class="admin-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <div class="page-header-text">
                <h1>Staff Records</h1>
                <div class="breadcrumb">Administration <span>/ Staff</span></div>
            </div>
            <button type="button" class="btn-primary" onclick="openModal('addStaffModal')">
                <i class="fa-solid fa-plus"></i> New Staff Record
            </button>
        </div>

        <div class="data-panel">
            <div class="data-panel-toolbar">
                <h2>Staff Directory</h2>
                <div class="toolbar-right">
                    <span class="record-count"><?php echo $totalRecords; ?> total records</span>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="staffSearch" placeholder="Search records" onkeyup="filterStaffTable()">
                    </div>
                </div>
            </div>

            <table class="data-table" id="staffTable">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staffList) === 0) { ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">No staff records found.</div>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($staffList as $staff) { ?>
                        <tr>
                            <td class="cell-name"><?php echo htmlspecialchars($staff['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['age']); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($staff['address']); ?></td>
                            <td><?php echo htmlspecialchars($staff['contact_number']); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td><span class="username-tag"><?php echo htmlspecialchars($staff['username']); ?></span></td>
                            <td class="col-actions">
                                <div class="action-group">
                                    <button type="button" class="icon-btn" title="Edit"
                                        onclick="openEditModal(
                                            '<?php echo $staff['id']; ?>',
                                            '<?php echo htmlspecialchars($staff['full_name'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($staff['age'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($staff['address'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($staff['contact_number'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($staff['email'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($staff['username'], ENT_QUOTES); ?>'
                                        )">
                                        <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </button>
                                    <span class="action-divider">|</span>
                                    <a href="staff.php?delete=<?php echo $staff['id']; ?>&page=<?php echo $page; ?>" class="icon-btn danger" title="Delete" onclick="return confirm('Remove this staff record?');">
                                        <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="pagination-bar">
                <span class="pagination-info">
                    Showing <?php echo count($staffList) > 0 ? $offset + 1 : 0; ?> 
                    to <?php echo min($offset + $limit, $totalRecords); ?> 
                    of <?php echo $totalRecords; ?> records
                </span>
                <div class="pagination-links">
                    <?php if ($page > 1) { ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo;</a>
                    <?php } else { ?>
                        <span class="disabled">&laquo;</span>
                    <?php } ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="?page=1">1</a>';
                        if ($startPage > 2) echo '<span class="disabled">...</span>';
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="?page=' . $i . '">' . $i . '</a>';
                        }
                    }

                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span class="disabled">...</span>';
                        echo '<a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages) { ?>
                        <a href="?page=<?php echo $page + 1; ?>">&raquo;</a>
                    <?php } else { ?>
                        <span class="disabled">&raquo;</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal-overlay" id="addStaffModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>New Staff Record</h2>
                <p>Fill in the details to create a staff account.</p>
            </div>
        </div>

        <form method="POST" action="staff.php">
            <div class="modal-body">
                <span class="form-section-label">Personal Information</span>
                <div class="field-row">
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="field-group narrow">
                        <label>Age</label>
                        <input type="number" name="age" min="1" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" required>
                    </div>
                    <div class="field-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                <div class="field-group">
                    <label>Address</label>
                    <input type="text" name="address" required>
                </div>

                <span class="form-section-label spaced">Account Credentials</span>
                <div class="field-row">
                    <div class="field-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="field-group">
                        <label>Password</label>
                        <input type="text" name="password" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addStaffModal')">Cancel</button>
                    <button type="submit" name="add_submit" class="btn-primary">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editStaffModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>Edit Staff Record</h2>
                <p>Update the staff account details.</p>
            </div>
        </div>

        <form method="POST" action="staff.php">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <div class="modal-body">
                <span class="form-section-label">Personal Information</span>
                <div class="field-row">
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="field-group narrow">
                        <label>Age</label>
                        <input type="number" name="age" id="edit_age" min="1" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" id="edit_contact_number" required>
                    </div>
                    <div class="field-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                </div>
                <div class="field-group">
                    <label>Address</label>
                    <input type="text" name="address" id="edit_address" required>
                </div>

                <span class="form-section-label spaced">Account Credentials</span>
                <div class="field-row">
                    <div class="field-group">
                        <label>Username</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                    <div class="field-group">
                        <label>Password</label>
                        <input type="text" name="password" id="edit_password">
                        <div class="field-hint">Leave blank to keep the current password.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editStaffModal')">Cancel</button>
                    <button type="submit" name="edit_submit" class="btn-primary">Save Changes</button>
                </div>
            </div>
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

function filterStaffTable() {
    var query = document.getElementById('staffSearch').value.toLowerCase();
    var rows = document.querySelectorAll('#staffTable tbody tr');
    rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}
</script>

</body>
</html>