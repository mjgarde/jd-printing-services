<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'orders';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quote_submit'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $quoted_price = mysqli_real_escape_string($conn, $_POST['quoted_price']);

    mysqli_query($conn, "UPDATE orders SET quoted_price = '$quoted_price', status = 'quoted' WHERE id = '$order_id'");
    header("Location: orders.php");
    exit();
}

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where = "";
if ($category === 'Tarpaulin' || $category === 'Sticker') {
    $where = "WHERE o.product_type = '" . mysqli_real_escape_string($conn, $category) . "'";
}

$orderBy = "o.id DESC";
if ($sort === 'oldest') $orderBy = "o.id ASC";
if ($sort === 'status') $orderBy = "o.status ASC, o.id DESC";
if ($sort === 'type') $orderBy = "o.product_type ASC, o.id DESC";

$sql = "SELECT o.*, c.full_name, c.username, c.email, c.phone, c.address
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        $where
        ORDER BY $orderBy";

$result = mysqli_query($conn, $sql);
$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders — Staff Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
    <style>
        .orders-topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 22px;
        }

        .orders-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .filter-tab {
            font-size: 12.5px;
            font-weight: 600;
            color: #5b5f66;
            background: #ffffff;
            border: 1px solid #e4e6ea;
            border-radius: 30px;
            padding: 8px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .filter-tab.active {
            background: #14161b;
            border-color: #14161b;
            color: #ffffff;
        }

        .sort-select {
            padding: 9px 14px;
            border: 1px solid #e4e6ea;
            border-radius: 8px;
            font-size: 12.5px;
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #333333;
        }

        .orders-table-wrap {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e9eaee;
            overflow: hidden;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #9a9ea6;
            padding: 12px 16px;
            border-bottom: 1px solid #e9eaee;
            background: #fafbfc;
        }

        .orders-table td {
            padding: 13px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f2f5;
            vertical-align: middle;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr {
            transition: background-color 0.12s ease;
        }

        .orders-table tbody tr:hover {
            background-color: #fafbfc;
        }

        .order-row {
            cursor: pointer;
        }

        .order-customer strong {
            display: block;
            font-size: 13.5px;
        }

        .order-customer span {
            font-size: 12px;
            color: #9a9ea6;
        }

        .order-product-tag {
            display: inline-block;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3px 9px;
            border-radius: 20px;
            background: #f1f2f5;
            color: #52565e;
        }

        .order-date {
            font-size: 12.5px;
            color: #7d818a;
        }

        .status-pill {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        .status-pill.status-pending {
            background: #f1f2f5;
            color: #6b6f76;
        }

        .status-pill.status-quoted {
            background: #fff0ea;
            color: #ff4a1c;
        }

        .status-pill.status-approved {
            background: #14161b;
            color: #ffffff;
        }

        .orders-empty {
            text-align: center;
            padding: 50px 20px;
            color: #9a9ea6;
        }

        .order-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 300;
            align-items: stretch;
            justify-content: stretch;
            padding: 0;
        }

        .order-modal-overlay.active {
            display: flex;
        }

        .order-modal {
            background: #ffffff;
            border-radius: 0;
            width: 100%;
            max-width: 100%;
            height: 100%;
            max-height: 100%;
            overflow-y: auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .order-modal-body {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 28px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 28px;
        }

        .order-modal-close {
            position: absolute;
            top: 18px;
            right: 20px;
            border: none;
            background: none;
            font-size: 15px;
            color: #9a9ea6;
            cursor: pointer;
            z-index: 2;
        }

        .order-modal-close:hover {
            color: #14161b;
        }

        .order-modal-section h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #9a9ea6;
            margin-bottom: 12px;
        }

        .order-modal-image {
            width: 220px;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            border: 1px solid #e9eaee;
            background: #fafbfc;
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
            font-size: 26px;
            color: #c5c8ce;
        }

        .image-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border: 1px solid #e4e6ea;
            border-radius: 7px;
            background: #ffffff;
            color: #52565e;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .image-download-btn:hover {
            border-color: #14161b;
            color: #14161b;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #f1f2f5;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row span:first-child {
            color: #9a9ea6;
        }

        .info-row span:last-child {
            font-weight: 600;
            text-align: right;
        }

        .order-modal-notes {
            margin-top: 16px;
            padding: 12px 14px;
            background: #fafbfc;
            border: 1px solid #f1f2f5;
            border-radius: 8px;
            font-size: 12.5px;
            color: #52565e;
        }

        .order-modal-quote {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f2f5;
        }

        .order-modal-quote-existing {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #fafbfc;
            border-radius: 8px;
        }

        .order-modal-quote-existing span {
            font-size: 12px;
            color: #9a9ea6;
            display: block;
            margin-bottom: 4px;
        }

        .order-modal-quote-existing strong {
            font-size: 20px;
        }

        .modal-quote-form {
            display: flex;
            gap: 10px;
        }

        .modal-quote-form input {
            flex: 1;
            padding: 11px 13px;
            border: 1px solid #dcdfe4;
            border-radius: 8px;
            font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .modal-quote-form input:focus {
            outline: none;
            border-color: #14161b;
        }

        .modal-quote-form button {
            padding: 11px 20px;
            background: #14161b;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-quote-form button:hover {
            background: #ff4a1c;
        }

        @media (max-width: 640px) {
            .order-modal-body {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">
        <div class="orders-topbar">
            <div>
                <h1>Orders</h1>
                <p>Review customer orders and provide quotations.</p>
            </div>

            <select class="sort-select" onchange="window.location.href = updateParam('sort', this.value)">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="status" <?php echo $sort === 'status' ? 'selected' : ''; ?>>Sort by Status</option>
                <option value="type" <?php echo $sort === 'type' ? 'selected' : ''; ?>>Sort by Product Type</option>
            </select>
        </div>

        <div class="orders-filters">
            <a href="?category=all&sort=<?php echo $sort; ?>" class="filter-tab <?php echo $category === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?category=Tarpaulin&sort=<?php echo $sort; ?>" class="filter-tab <?php echo $category === 'Tarpaulin' ? 'active' : ''; ?>">Tarpaulin</a>
            <a href="?category=Sticker&sort=<?php echo $sort; ?>" class="filter-tab <?php echo $category === 'Sticker' ? 'active' : ''; ?>">Sticker</a>
        </div>

        <div class="orders-table-wrap">
            <table class="orders-table">
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
                            <td colspan="4" class="orders-empty">No orders found.</td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($orders as $order) { ?>
                        <?php
                            $specs = htmlspecialchars($order['width']) . ' x ' . htmlspecialchars($order['height']);
                            if (!empty($order['shape'])) {
                                $specs .= ' - ' . htmlspecialchars(ucfirst($order['shape']));
                            }
                            $specs .= ' - Qty ' . htmlspecialchars($order['quantity']);
                            $imagePath = !empty($order['design_file']) ? '../assets/uploads/designs/' . htmlspecialchars($order['design_file'], ENT_QUOTES) : '';
                        ?>
                        <tr class="order-row" onclick='openOrderModal(<?php echo json_encode([
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
                                    "quoted_price" => $order["quoted_price"],
                                    "date_ordered" => date("M d, Y g:i A", strtotime($order["date_ordered"])),
                                    "image" => $imagePath
                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                            <td class="order-customer">
                                <strong><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></strong>
                                <span>@<?php echo htmlspecialchars($order['username'] ?? ''); ?></span>
                            </td>
                            <td><span class="order-product-tag"><?php echo htmlspecialchars($order['product_type']); ?></span></td>
                            <td><span class="order-date"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['date_ordered']))); ?></span></td>
                            <td><span class="status-pill status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="order-modal-overlay" id="orderModal">
    <div class="order-modal">
        <button type="button" class="order-modal-close" onclick="closeOrderModal()"><i class="fa-solid fa-xmark"></i></button>

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
                <div class="info-row"><span>Date Ordered</span><span id="modalDate"></span></div>
                <div class="info-row"><span>Status</span><span id="modalStatus"></span></div>

                <div class="order-modal-notes" id="modalNotesWrap">
                    <span id="modalNotes"></span>
                </div>

                <div class="order-modal-quote">
                    <h3>Quotation</h3>
                    <div id="modalQuoteArea"></div>
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
    } else if (order.quoted_price) {
        quoteArea.innerHTML =
            '<div class="order-modal-quote-existing">' +
                '<div><span>Quoted Price</span><strong>\u20b1' + parseFloat(order.quoted_price).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</strong></div>' +
            '</div>';
    } else {
        quoteArea.innerHTML = '<div class="order-modal-quote-existing"><span>No quotation yet.</span></div>';
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

function updateParam(key, value) {
    var url = new URL(window.location.href);
    url.searchParams.set(key, value);
    return url.toString();
}
</script>

</body>
</html>