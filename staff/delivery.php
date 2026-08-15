<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$activePage = 'delivery';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_delivery_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $delivery_status = mysqli_real_escape_string($conn, $_POST['delivery_status']);

    $allowedStatuses = ['preparing', 'in_transit', 'completed'];
    if (in_array($delivery_status, $allowedStatuses)) {
        mysqli_query($conn, "UPDATE orders SET delivery_status = '$delivery_status' WHERE id = '$order_id'");
    }
    header("Location: delivery.php");
    exit();
}

$sql = "SELECT o.*, c.full_name, c.username, c.phone, c.address
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        WHERE o.fulfillment_method = 'delivery' AND o.status = 'approved'
        ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);
$deliveries = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery — Staff Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../font/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
    <style>
        .delivery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 18px;
        }

        .delivery-card {
            background: #ffffff;
            border: 1px solid #e9eaee;
            border-radius: 10px;
            padding: 20px;
        }

        .delivery-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 14px;
        }

        .delivery-card-top h3 {
            font-size: 15px;
            font-weight: 700;
        }

        .delivery-card-top span {
            font-size: 12px;
            color: #9a9ea6;
            display: block;
            margin-top: 2px;
        }

        .delivery-product-tag {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3px 9px;
            border-radius: 20px;
            background: #f1f2f5;
            color: #52565e;
        }

        .delivery-info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f1f2f5;
            font-size: 13px;
        }

        .delivery-info-row:last-of-type {
            border-bottom: none;
        }

        .delivery-info-row span:first-child {
            color: #9a9ea6;
        }

        .delivery-info-row span:last-child {
            font-weight: 600;
            text-align: right;
        }

        .delivery-status-track {
            display: flex;
            gap: 6px;
            margin-top: 16px;
        }

        .delivery-status-track form {
            flex: 1;
        }

        .delivery-status-btn {
            width: 100%;
            padding: 9px 6px;
            border: 1px solid #e4e6ea;
            background: #ffffff;
            color: #52565e;
            border-radius: 7px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
        }

        .delivery-status-btn:hover {
            border-color: #14161b;
        }

        .delivery-status-btn.current {
            background: #14161b;
            border-color: #14161b;
            color: #ffffff;
        }

        .deliveries-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9a9ea6;
        }

        .deliveries-empty i {
            font-size: 28px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>
<body>

<div class="staff-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="staff-content">
        <div class="staff-topbar">
            <div>
                <h1>Delivery</h1>
                <p>Track confirmed orders that customers chose to have delivered.</p>
            </div>
        </div>

        <?php if (count($deliveries) === 0) { ?>
            <div class="deliveries-empty">
                <i class="fa-solid fa-truck"></i>
                <p>No confirmed delivery orders at the moment.</p>
            </div>
        <?php } else { ?>
            <div class="delivery-grid">
                <?php foreach ($deliveries as $order) { ?>
                    <div class="delivery-card">
                        <div class="delivery-card-top">
                            <div>
                                <h3><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></h3>
                                <span>@<?php echo htmlspecialchars($order['username'] ?? ''); ?></span>
                            </div>
                            <span class="delivery-product-tag"><?php echo htmlspecialchars($order['product_type']); ?></span>
                        </div>

                        <div class="delivery-info-row"><span>Phone</span><span><?php echo htmlspecialchars($order['phone'] ?? '—'); ?></span></div>
                        <div class="delivery-info-row"><span>Address</span><span><?php echo htmlspecialchars($order['address'] ?? '—'); ?></span></div>
                        <div class="delivery-info-row"><span>Size</span><span><?php echo htmlspecialchars($order['width']); ?> x <?php echo htmlspecialchars($order['height']); ?></span></div>
                        <div class="delivery-info-row"><span>Quantity</span><span><?php echo htmlspecialchars($order['quantity']); ?></span></div>

                        <div class="delivery-status-track">
                            <form method="POST" action="delivery.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="delivery_status" value="preparing">
                                <button type="submit" name="update_delivery_status" class="delivery-status-btn <?php echo $order['delivery_status'] === 'preparing' ? 'current' : ''; ?>">Preparing</button>
                            </form>
                            <form method="POST" action="delivery.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="delivery_status" value="in_transit">
                                <button type="submit" name="update_delivery_status" class="delivery-status-btn <?php echo $order['delivery_status'] === 'in_transit' ? 'current' : ''; ?>">In Transit</button>
                            </form>
                            <form method="POST" action="delivery.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="delivery_status" value="completed">
                                <button type="submit" name="update_delivery_status" class="delivery-status-btn <?php echo $order['delivery_status'] === 'completed' ? 'current' : ''; ?>">Complete</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>
</html>