<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'dashboard';

$totalOrders = 0;
$pendingOrders = 0;
$totalStaff = 0;

$orderCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders");
if ($orderCountResult) {
    $totalOrders = mysqli_fetch_assoc($orderCountResult)['cnt'];
}

$pendingCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
if ($pendingCountResult) {
    $pendingOrders = mysqli_fetch_assoc($pendingCountResult)['cnt'];
}

$staffCountResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM staff");
if ($staffCountResult) {
    $totalStaff = mysqli_fetch_assoc($staffCountResult)['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
                <h1>Dashboard</h1>
                <p>Overview of orders, staff, and inventory.</p>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
                <div class="stat-value"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-value"><?php echo $pendingOrders; ?></div>
                <div class="stat-label">Pending Quotations</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="stat-value"><?php echo $totalStaff; ?></div>
                <div class="stat-label">Staff Members</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="stat-value">—</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </div>

        <div class="panel">
            <h2>Recent Orders</h2>
            <?php
                $recentOrders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT 5");
            ?>
            <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0) { ?>
                <?php while ($order = mysqli_fetch_assoc($recentOrders)) { ?>
                    <p>#<?php echo $order['id']; ?> — <?php echo htmlspecialchars($order['product_type']); ?> — <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                <?php } ?>
            <?php } else { ?>
                <p>No orders yet.</p>
            <?php } ?>
        </div>
    </div>

</div>

</body>
</html>