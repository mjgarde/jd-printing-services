<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'delivery';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_delivery_status'])) {
    $order_id = trim($_POST['order_id']);
    $delivery_status = trim($_POST['delivery_status']);

    $allowedStatuses = ['preparing', 'in_transit', 'completed'];
    if (in_array($delivery_status, $allowedStatuses)) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET delivery_status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $delivery_status, $order_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: delivery.php");
    exit();
}

$sql = "SELECT o.*, c.full_name, c.username, c.phone, c.address
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        WHERE o.fulfillment_method = 'delivery' AND o.status IN ('approved', 'preparing')
        ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);
$deliveries = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
    }
}

$statusLabels = [
    'preparing' => 'Preparing',
    'in_transit' => 'In Transit',
    'completed' => 'Completed'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery | JD Printing Services</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../font/css/all.min.css">
<link rel="stylesheet" href="staff.css">
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

.staff-wrapper {
    display: flex;
    min-height: 100vh;
}

.staff-content {
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

.cell-sub {
    font-size: 11.5px;
    color: #9099a3;
    margin-top: 1px;
}

.cell-muted {
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-badge.preparing {
    background-color: #fbf1e0;
    color: #966a1f;
}

.status-badge.in_transit {
    background-color: #e6eef8;
    color: #33547d;
}

.status-badge.completed {
    background-color: #e5f2ea;
    color: #2f6b45;
}

.status-select-form {
    display: inline-block;
}

.status-select {
    padding: 5px 8px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12px;
    font-family: inherit;
    color: #26292f;
    background-color: #fbfbfc;
    cursor: pointer;
}

.status-select:focus {
    outline: none;
    border-color: #3a6ea5;
}

.col-actions {
    width: 1%;
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    padding: 46px 16px;
    color: #9099a3;
    font-size: 13px;
}

.empty-state i {
    display: block;
    font-size: 22px;
    margin-bottom: 10px;
    color: #c3c9d3;
}

@media (max-width: 700px) {
    .data-table {
        display: block;
        overflow-x: auto;
    }
}
</style>
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">
        <div class="page-header">
            <div class="page-header-text">
                <h1>Delivery</h1>
                <div class="breadcrumb">Staff Panel <span>/ Delivery</span></div>
            </div>
        </div>

        <div class="data-panel">
            <div class="data-panel-toolbar">
                <h2>Confirmed Delivery Orders</h2>
                <div class="toolbar-right">
                    <span class="record-count"><?php echo count($deliveries); ?> total records</span>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="deliverySearch" placeholder="Search records" onkeyup="filterDeliveryTable()">
                    </div>
                </div>
            </div>

            <table class="data-table" id="deliveryTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="col-actions">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($deliveries) === 0) { ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa-solid fa-truck"></i>
                                    No confirmed delivery orders found.
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($deliveries as $order) {
                        $currentStatus = $order['delivery_status'] ?? 'preparing';
                        $statusLabel = $statusLabels[$currentStatus] ?? 'Preparing';
                    ?>
                        <tr>
                            <td>
                                <div class="cell-name"><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></div>
                                <div class="cell-sub">@<?php echo htmlspecialchars($order['username'] ?? ''); ?></div>
                            </td>
                            <td class="cell-muted"><?php echo htmlspecialchars($order['product_type']); ?></td>
                            <td><?php echo htmlspecialchars($order['width']); ?> x <?php echo htmlspecialchars($order['height']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($order['phone'] ?? '—'); ?></td>
                            <td class="cell-muted"><?php echo htmlspecialchars($order['address'] ?? '—'); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($currentStatus); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                            </td>
                            <td class="col-actions">
                                <form method="POST" action="delivery.php" class="status-select-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="delivery_status" class="status-select" onchange="this.form.submit()">
                                        <option value="preparing" <?php echo $currentStatus === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="in_transit" <?php echo $currentStatus === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                        <option value="completed" <?php echo $currentStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <noscript><button type="submit" name="update_delivery_status">Update</button></noscript>
                                    <input type="hidden" name="update_delivery_status" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function filterDeliveryTable() {
    var query = document.getElementById('deliverySearch').value.toLowerCase();
    var rows = document.querySelectorAll('#deliveryTable tbody tr');
    rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}
</script>

</body>
</html>