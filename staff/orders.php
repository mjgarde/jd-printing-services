<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'orders';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quote_submit'])) {
    $order_id = trim($_POST['order_id']);
    $quoted_price = trim($_POST['quoted_price']);

    $stmt = mysqli_prepare($conn, "UPDATE orders SET quoted_price = ?, status = 'quoted' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "di", $quoted_price, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: orders.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_submit'])) {
    $order_id = (int) $_POST['order_id'];

    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'approved' WHERE id = ? AND status = 'quoted'");
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: orders.php?view=approved");
    exit();
}

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$view = isset($_GET['view']) && $_GET['view'] === 'approved' ? 'approved' : 'active';

$conditions = [];
if ($category === 'Tarpaulin' || $category === 'Sticker') {
    $conditions[] = "o.product_type = '" . mysqli_real_escape_string($conn, $category) . "'";
}
if ($view === 'approved') {
    $conditions[] = "o.status = 'approved'";
} else {
    $conditions[] = "o.status NOT IN ('approved', 'preparing')";
}
$where = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

$orderBy = "o.id DESC";
if ($sort === 'oldest') $orderBy = "o.id ASC";

$countSql = "SELECT COUNT(*) AS total FROM orders o $where";
$countResult = mysqli_query($conn, $countSql);
$totalRow = mysqli_fetch_assoc($countResult);
$totalRecords = $totalRow['total'];

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$totalPages = max(1, ceil($totalRecords / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

$sql = "SELECT o.*, c.full_name, c.username, c.email, c.phone, c.address
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        $where
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}

$invResult = mysqli_query($conn, "SELECT id, item_name, unit, quantity_on_hand FROM inventory_items ORDER BY item_name");
$inventoryItems = [];
while ($invRow = mysqli_fetch_assoc($invResult)) {
    $inventoryItems[] = $invRow;
}

$deductedTotals = [];
$logResult = mysqli_query($conn, "SELECT l.order_id, l.quantity, i.item_name, i.unit FROM inventory_logs l JOIN inventory_items i ON l.item_id = i.id WHERE l.order_id IS NOT NULL");
while ($logRow = mysqli_fetch_assoc($logResult)) {
    $oid = $logRow['order_id'];
    if (!isset($deductedTotals[$oid])) {
        $deductedTotals[$oid] = [];
    }
    $deductedTotals[$oid][] = $logRow['quantity'] . ' ' . $logRow['unit'] . ' ' . $logRow['item_name'];
}

function buildQuery($params) {
    $current = $_GET;
    foreach ($params as $key => $value) {
        $current[$key] = $value;
    }
    return '?' . http_build_query($current);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders | JD Printing Services</title>
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
    flex-wrap: wrap;
    gap: 12px;
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

.sort-select {
    padding: 7px 12px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-family: inherit;
    background-color: #fbfbfc;
    color: #26292f;
    cursor: pointer;
}

.sort-select:focus {
    outline: none;
    border-color: #3a6ea5;
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

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    background-color: #ffffff;
    color: #4c5560;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.btn-secondary:hover {
    background-color: #f5f6f8;
}

.filter-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.filter-tabs .sort-select {
    margin-left: auto;
}

.filter-tab {
    font-size: 12px;
    font-weight: 600;
    color: #4c5560;
    background-color: #ffffff;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    padding: 7px 14px;
    text-decoration: none;
}

.filter-tab:hover {
    border-color: #3a6ea5;
    color: #3a6ea5;
}

.filter-tab.active {
    background-color: #33475a;
    border-color: #33475a;
    color: #ffffff;
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

.data-table tbody tr {
    cursor: pointer;
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

.product-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 3px;
    background-color: #eef1f6;
    color: #3f4b5c;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-badge.status-pending {
    background-color: #f0f1f3;
    color: #5c6470;
}

.status-badge.status-quoted {
    background-color: #fbf1e0;
    color: #966a1f;
}

.status-badge.status-preparing {
    background-color: #f3e8fb;
    color: #6b3fa0;
}

.status-badge.status-approved {
    background-color: #e5f2ea;
    color: #2f6b45;
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
}

.pagination-links a:hover {
    background-color: #f0f4fa;
    border-color: #3a6ea5;
}

.pagination-links span.active {
    background-color: #33475a;
    border-color: #33475a;
    color: #ffffff;
}

.pagination-links span.disabled {
    color: #b0b8c4;
    border-color: #e4e6ea;
    background-color: #f9f9fa;
}

.order-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(20, 24, 30, 0.45);
    z-index: 300;
    align-items: flex-start;
    justify-content: center;
    padding-top: 40px;
    overflow-y: auto;
}

.order-modal-overlay.active {
    display: flex;
}

.order-modal {
    background-color: #ffffff;
    border-radius: 3px;
    width: 940px;
    max-width: 94%;
    border: 1px solid #c9ccd2;
    margin-bottom: 40px;
}

.order-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background-color: #f5f6f8;
    border-bottom: 1px solid #d6d9de;
}

.order-modal-header h2 {
    font-size: 14.5px;
    font-weight: 600;
    color: #1c2027;
}

.order-modal-close {
    border: none;
    background: none;
    font-size: 14px;
    color: #7a8089;
    cursor: pointer;
}

.order-modal-close:hover {
    color: #a3402a;
}

.order-modal-body {
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 24px;
}

.order-modal-section h3 {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #3a6ea5;
    padding-bottom: 6px;
    margin-bottom: 12px;
    border-bottom: 1px solid #e4e6ea;
}

.order-modal-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 3px;
    border: 1px solid #d6d9de;
    background-color: #fbfbfc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 12px;
}

.order-modal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-modal-image i {
    font-size: 24px;
    color: #c9ccd2;
}

.image-download-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 12px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    background-color: #ffffff;
    color: #4c5560;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}

.image-download-btn:hover {
    background-color: #f5f6f8;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #edeef1;
    font-size: 12.5px;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row span:first-child {
    color: #7a8089;
}

.info-row span:last-child {
    font-weight: 600;
    text-align: right;
    color: #26292f;
}

.order-modal-notes {
    margin-top: 14px;
    padding: 10px 12px;
    background-color: #fbfbfc;
    border: 1px solid #edeef1;
    border-radius: 3px;
    font-size: 12px;
    color: #4c5560;
}

.order-modal-quote {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #edeef1;
}

.order-modal-quote-existing {
    padding: 12px 14px;
    background-color: #fbfbfc;
    border: 1px solid #edeef1;
    border-radius: 3px;
}

.order-modal-quote-existing span {
    font-size: 11.5px;
    color: #7a8089;
    display: block;
    margin-bottom: 4px;
}

.order-modal-quote-existing strong {
    font-size: 18px;
    color: #1c2027;
}

.modal-quote-form {
    display: flex;
    gap: 8px;
}

.modal-quote-form input {
    flex: 1;
    padding: 8px 10px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 13px;
    font-family: inherit;
}

.modal-quote-form input:focus {
    outline: none;
    border-color: #3a6ea5;
}

.modal-quote-form button {
    padding: 8px 16px;
    background-color: #33475a;
    color: #ffffff;
    border: 1px solid #33475a;
    border-radius: 3px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.modal-quote-form button:hover {
    background-color: #263748;
}

.order-modal-inventory {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #edeef1;
}

.inventory-deduct-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.inventory-deduct-row {
    display: flex;
    gap: 8px;
}

.inventory-deduct-row select,
.inventory-deduct-row input {
    flex: 1;
    padding: 8px 10px;
    border: 1px solid #c9ccd2;
    border-radius: 3px;
    font-size: 12.5px;
    font-family: inherit;
    background-color: #ffffff;
}

.inventory-deduct-row select:focus,
.inventory-deduct-row input:focus {
    outline: none;
    border-color: #3a6ea5;
}

.inventory-deduct-form button {
    padding: 8px 16px;
    background-color: #a3402a;
    color: #ffffff;
    border: 1px solid #a3402a;
    border-radius: 3px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.inventory-deduct-form button:hover {
    background-color: #85331f;
}

.inventory-deduct-hint {
    font-size: 11px;
    color: #9099a3;
}

.inventory-deducted-log {
    margin-top: 10px;
    font-size: 11.5px;
    color: #4c5560;
}

.inventory-deducted-log ul {
    list-style: none;
    margin-top: 4px;
}

.inventory-deducted-log li {
    padding: 4px 0;
    border-bottom: 1px solid #f0f1f3;
}

.inventory-deducted-log li:last-child {
    border-bottom: none;
}

@media (max-width: 700px) {
    .order-modal-body {
        grid-template-columns: 1fr;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }

    .pagination-bar {
        flex-direction: column;
        align-items: center;
        text-align: center;
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
                <h1><?php echo $view === 'approved' ? 'Approved Orders' : 'Orders'; ?></h1>
                <div class="breadcrumb">Staff Panel <span>/ Orders <?php echo $view === 'approved' ? '/ Approved' : ''; ?></span></div>
            </div>
        </div>

        <div class="filter-tabs">
            <a href="<?php echo htmlspecialchars(buildQuery(['category' => 'all', 'view' => 'active', 'page' => 1])); ?>" class="filter-tab <?php echo ($category === 'all' && $view === 'active') ? 'active' : ''; ?>">All</a>
            <a href="<?php echo htmlspecialchars(buildQuery(['category' => 'Tarpaulin', 'view' => 'active', 'page' => 1])); ?>" class="filter-tab <?php echo ($category === 'Tarpaulin' && $view === 'active') ? 'active' : ''; ?>">Tarpaulin</a>
            <a href="<?php echo htmlspecialchars(buildQuery(['category' => 'Sticker', 'view' => 'active', 'page' => 1])); ?>" class="filter-tab <?php echo ($category === 'Sticker' && $view === 'active') ? 'active' : ''; ?>">Sticker</a>
            <a href="<?php echo htmlspecialchars(buildQuery(['view' => 'approved', 'page' => 1])); ?>" class="filter-tab <?php echo $view === 'approved' ? 'active' : ''; ?>">Approved</a>
            <select class="sort-select" onchange="window.location.href = this.value">
                <option value="<?php echo htmlspecialchars(buildQuery(['sort' => 'newest', 'page' => 1])); ?>" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="<?php echo htmlspecialchars(buildQuery(['sort' => 'oldest', 'page' => 1])); ?>" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
            </select>
        </div>        <div class="data-panel">
            <div class="data-panel-toolbar">
                <h2>Order List</h2>
                <div class="toolbar-right">
                    <span class="record-count"><?php echo $totalRecords; ?> total records</span>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="orderSearch" placeholder="Search records" onkeyup="filterOrderTable()">
                    </div>
                </div>
            </div>

            <table class="data-table" id="orderTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) === 0) { ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">No orders found.</div>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($orders as $order) { ?>
                        <?php
                            $imagePath = !empty($order['design_file']) ? '../assets/uploads/designs/' . htmlspecialchars($order['design_file'], ENT_QUOTES) : '';
                        ?>
                        <tr onclick='openOrderModal(<?php echo json_encode([
                                    "id" => $order["id"],
                                    "product_type" => $order["product_type"],
                                    "full_name" => $order["full_name"] ?? "Unknown",
                                    "username" => $order["username"] ?? "",
                                    "email" => $order["email"] ?? "",
                                    "phone" => $order["phone"] ?? "",
                                    "address" => $order["address"] ?? "",
                                    "width" => $order["width"],
                                    "height" => $order["height"],
                                    "shape" => $order["shape"],
                                    "quantity" => $order["quantity"],
                                    "notes" => $order["notes"],
                                    "status" => $order["status"],
                                    "fulfillment_method" => $order["fulfillment_method"],
                                    "quoted_price" => $order["quoted_price"],
                                    "date_ordered" => date("M d, Y g:i A", strtotime($order["date_ordered"])),
                                    "image" => $imagePath,
                                    "deducted" => $deductedTotals[$order["id"]] ?? []
                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                            <td>
                                <div class="cell-name"><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></div>
                                <div class="cell-sub">@<?php echo htmlspecialchars($order['username'] ?? ''); ?></div>
                            </td>
                            <td><span class="product-tag"><?php echo htmlspecialchars($order['product_type']); ?></span></td>
                            <td class="cell-muted"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['date_ordered']))); ?></td>
                            <td><span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="pagination-bar">
                <span class="pagination-info">
                    Showing <?php echo count($orders) > 0 ? $offset + 1 : 0; ?>
                    to <?php echo min($offset + $limit, $totalRecords); ?>
                    of <?php echo $totalRecords; ?> records
                </span>
                <div class="pagination-links">
                    <?php if ($page > 1) { ?>
                        <a href="<?php echo htmlspecialchars(buildQuery(['page' => $page - 1])); ?>">&laquo;</a>
                    <?php } else { ?>
                        <span class="disabled">&laquo;</span>
                    <?php } ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="' . htmlspecialchars(buildQuery(['page' => 1])) . '">1</a>';
                        if ($startPage > 2) echo '<span class="disabled">...</span>';
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="' . htmlspecialchars(buildQuery(['page' => $i])) . '">' . $i . '</a>';
                        }
                    }

                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span class="disabled">...</span>';
                        echo '<a href="' . htmlspecialchars(buildQuery(['page' => $totalPages])) . '">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages) { ?>
                        <a href="<?php echo htmlspecialchars(buildQuery(['page' => $page + 1])); ?>">&raquo;</a>
                    <?php } else { ?>
                        <span class="disabled">&raquo;</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="order-modal-overlay" id="orderModal">
    <div class="order-modal">
        <div class="order-modal-header">
            <h2>Order Details</h2>
            <button type="button" class="order-modal-close" onclick="closeOrderModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="order-modal-body">
            <div class="order-modal-section">
                <h3>Design</h3>
                <div class="order-modal-image" id="modalImageWrap">
                    <i class="fa-solid fa-image"></i>
                </div>
                <div id="modalImageDownloadWrap"></div>
            </div>

            <div class="order-modal-section">
                <h3>Order Details</h3>
                <div class="info-row"><span>Size</span><span id="modalSize"></span></div>
                <div class="info-row" id="modalShapeRow"><span>Shape</span><span id="modalShape"></span></div>
                <div class="info-row"><span>Quantity</span><span id="modalQuantity"></span></div>
                <div class="info-row"><span>Fulfillment</span><span id="modalFulfillment"></span></div>
                <div class="info-row"><span>Date Ordered</span><span id="modalDate"></span></div>
                <div class="info-row"><span>Status</span><span id="modalStatus"></span></div>

                <div class="order-modal-notes" id="modalNotesWrap">
                    <span id="modalNotes"></span>
                </div>

                <div class="order-modal-quote">
                    <h3>Quotation</h3>
                    <div id="modalQuoteArea"></div>
                </div>

                <div class="order-modal-inventory">
                    <h3>Deduct Inventory</h3>
                    <div id="modalInventoryArea"></div>
                </div>
            </div>

            <div class="order-modal-section">
                <h3>Customer</h3>
                <div class="info-row"><span>Full Name</span><span id="modalCustomerName"></span></div>
                <div class="info-row"><span>Username</span><span id="modalCustomerUsername"></span></div>
                <div class="info-row"><span>Email</span><span id="modalCustomerEmail"></span></div>
                <div class="info-row"><span>Phone</span><span id="modalCustomerPhone"></span></div>
                <div class="info-row"><span>Address</span><span id="modalCustomerAddress"></span></div>
            </div>
        </div>
    </div>
</div>

<script>
var INVENTORY_ITEMS = <?php echo json_encode($inventoryItems, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function openOrderModal(order) {
    var imageWrap = document.getElementById('modalImageWrap');
    var downloadWrap = document.getElementById('modalImageDownloadWrap');
    if (order.image) {
        imageWrap.innerHTML = '<img src="' + order.image + '" alt="Design preview">';
        downloadWrap.innerHTML = '<a href="' + order.image + '" download class="image-download-btn"><i class="fa-solid fa-download"></i> Download Design</a>';
    } else {
        imageWrap.innerHTML = '<i class="fa-solid fa-image"></i>';
        downloadWrap.innerHTML = '';
    }

    document.getElementById('modalSize').textContent = order.width + ' x ' + order.height;
    document.getElementById('modalQuantity').textContent = order.quantity;
    document.getElementById('modalFulfillment').textContent = order.fulfillment_method ? (order.fulfillment_method.charAt(0).toUpperCase() + order.fulfillment_method.slice(1)) : '\u2014';
    document.getElementById('modalDate').textContent = order.date_ordered;
    document.getElementById('modalStatus').textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);

    var shapeRow = document.getElementById('modalShapeRow');
    if (order.shape) {
        shapeRow.style.display = 'flex';
        document.getElementById('modalShape').textContent = order.shape.charAt(0).toUpperCase() + order.shape.slice(1);
    } else {
        shapeRow.style.display = 'none';
    }

    var notesWrap = document.getElementById('modalNotesWrap');
    if (order.notes) {
        notesWrap.style.display = 'block';
        document.getElementById('modalNotes').textContent = order.notes;
    } else {
        notesWrap.style.display = 'none';
    }

    document.getElementById('modalCustomerName').textContent = order.full_name;
    document.getElementById('modalCustomerUsername').textContent = '@' + order.username;
    document.getElementById('modalCustomerEmail').textContent = order.email || '\u2014';
    document.getElementById('modalCustomerPhone').textContent = order.phone || '\u2014';
    document.getElementById('modalCustomerAddress').textContent = order.address || '\u2014';

    var quoteArea = document.getElementById('modalQuoteArea');
    if (order.status === 'pending') {
        quoteArea.innerHTML =
            '<form method="POST" action="orders.php" class="modal-quote-form">' +
                '<input type="hidden" name="order_id" value="' + order.id + '">' +
                '<input type="number" step="0.01" min="0" name="quoted_price" placeholder="Enter price" required>' +
                '<button type="submit" name="quote_submit">Send Quote</button>' +
            '</form>';
    } else if (order.status === 'quoted' && order.quoted_price) {
        quoteArea.innerHTML =
            '<div class="order-modal-quote-existing">' +
                '<span>Quoted Price</span><strong>\u20b1' + parseFloat(order.quoted_price).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</strong>' +
            '</div>' +
            '<form method="POST" action="orders.php" style="margin-top: 8px;">' +
                '<input type="hidden" name="order_id" value="' + order.id + '">' +
                '<button type="submit" name="approve_submit" style="width: 100%; padding: 8px 16px; background-color: #2f6b45; color: #ffffff; border: 1px solid #2f6b45; border-radius: 3px; font-size: 12.5px; font-weight: 600; cursor: pointer;">Approve Order</button>' +
            '</form>';
    } else if (order.quoted_price) {
        quoteArea.innerHTML =
            '<div class="order-modal-quote-existing">' +
                '<span>Quoted Price</span><strong>\u20b1' + parseFloat(order.quoted_price).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</strong>' +
            '</div>';
    } else {
        quoteArea.innerHTML = '<div class="order-modal-quote-existing"><span>No quotation yet.</span></div>';
    }

    var inventoryArea = document.getElementById('modalInventoryArea');

    if (order.status === 'approved') {
        var options = '';
        var preselectId = null;
        INVENTORY_ITEMS.forEach(function (item) {
            var matches = (order.product_type === 'Tarpaulin' && item.item_name === 'Tarpaulin') ||
                          (order.product_type === 'Sticker' && item.item_name === 'Sticker Paper');
            if (matches && preselectId === null) {
                preselectId = item.id;
            }
            options += '<option value="' + item.id + '"' + (matches ? ' selected' : '') + '>' +
                            item.item_name + ' (' + parseFloat(item.quantity_on_hand).toLocaleString() + ' ' + item.unit + ' on hand)' +
                       '</option>';
        });

        inventoryArea.innerHTML =
            '<form method="POST" action="inventory_action.php" class="inventory-deduct-form">' +
                '<input type="hidden" name="order_id" value="' + order.id + '">' +
                '<div class="inventory-deduct-row">' +
                    '<select name="item_id" required>' + options + '</select>' +
                    '<input type="number" step="0.01" min="0.01" name="quantity" placeholder="Qty" required>' +
                '</div>' +
                '<div class="inventory-deduct-hint">Select the material used and enter the amount to subtract from stock.</div>' +
                '<button type="submit" name="deduct_stock"><i class="fa-solid fa-minus"></i> Deduct from Inventory</button>' +
            '</form>';
    } else if (order.status === 'preparing') {
        inventoryArea.innerHTML = '<div class="order-modal-quote-existing"><span>Stock already deducted for this order.</span></div>';
    } else {
        inventoryArea.innerHTML = '<div class="order-modal-quote-existing"><span>Approve the order first before deducting stock.</span></div>';
    }

    if (order.deducted && order.deducted.length > 0) {
        var deductedHtml = '<div class="inventory-deducted-log"><span>Already deducted for this order:</span><ul>';
        order.deducted.forEach(function (entry) {
            deductedHtml += '<li>' + entry + '</li>';
        });
        deductedHtml += '</ul></div>';
        inventoryArea.innerHTML += deductedHtml;
    }

    document.getElementById('orderModal').classList.add('active');
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

window.addEventListener('click', function (e) {
    if (e.target.id === 'orderModal') {
        closeOrderModal();
    }
});

function filterOrderTable() {
    var query = document.getElementById('orderSearch').value.toLowerCase();
    var rows = document.querySelectorAll('#orderTable tbody tr');
    rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}
</script>

</body>
</html>