<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f5f7;
            color: #222;
        }

        .staff-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .staff-sidebar {
            width: 240px;
            background-color: #1c1f26;
            color: #ffffff;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 22px 20px;
            border-bottom: 1px solid #2a2e37;
        }

        .brand-text {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #ffffff;
        }

        .brand-sub {
            display: block;
            font-size: 12px;
            color: #ffffff;
            margin-top: 2px;
            opacity: 0.6;
        }

        .sidebar-menu {
            list-style: none;
            padding: 10px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
        }

        .sidebar-menu li a i {
            width: 16px;
            text-align: center;
            font-size: 14px;
            color: #ffffff;
        }

        .sidebar-menu li a:hover {
            background-color: #262a33;
            color: #ffffff;
        }

        .sidebar-menu li.active a {
            background-color: #2f89ff;
            color: #ffffff;
        }

        .menu-label {
            padding: 14px 20px 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            opacity: 0.5;
        }

        .menu-divider {
            border-top: 1px solid #2a2e37;
            margin: 10px 0;
        }

        .logout-link {
            color: #ff6b6b !important;
        }

        .logout-link i {
            color: #ff6b6b !important;
        }

        .staff-content {
            flex: 1;
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">

    </div>

</div>

</body>
</html>