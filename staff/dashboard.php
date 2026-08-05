<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">
        <div class="staff-topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Overview of your orders and tasks.</p>
            </div>
        </div>
    </div>

</div>

</body>
</html>