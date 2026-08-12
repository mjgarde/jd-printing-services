<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['client_id'];

$result = mysqli_query($conn, "SELECT * FROM orders WHERE client_id = '$client_id' AND fulfillment_method = 'delivery' ORDER BY id DESC");
$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Deliveries — JD Printing Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../font/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="colorbar"><span></span><span></span><span></span><span></span></div>

<div class="navbar">
    <div class="logo">
        <a href="../index.php"><img src="../logo.png" alt="JD Printing Logo"></a>
    </div>
</div>

<div class="section" id="my-deliveries">
    <div class="section-head">
        <span class="eyebrow">Your Account</span>
        <h2>My Deliveries</h2>
        <p>Track orders you chose to have delivered.</p>
    </div>

    <div class="orders-list">
        <?php if (count($deliveries) === 0) { ?>
            <div class="orders-empty">
                <i class="fa-solid fa-truck"></i>
                <p>You don't have any delivery orders yet.</p>
                <a href="../index.php#order" class="btn btn-dark">Place an Order</a>
            </div>
        <?php } else { ?>
            <?php foreach ($deliveries as $order) { ?>
                <?php
                    $status = $order['status'];
                    $statusLabel = ucfirst($status);
                    $statusClass = 'status-' . $status;
                ?>
                <div class="order-item">
                    <div class="order-item-media">
                        <?php if (!empty($order['design_file'])) { ?>
                            <img src="../../assets/uploads/designs/<?php echo htmlspecialchars($order['design_file']); ?>" alt="Design preview">
                        <?php } else { ?>
                            <i class="fa-solid fa-image"></i>
                        <?php } ?>
                    </div>
                    <div class="order-item-body">
                        <div class="order-item-top">
                            <h3><?php echo htmlspecialchars($order['product_type']); ?></h3>
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                        </div>
                        <p class="order-item-spec">
                            <?php echo htmlspecialchars($order['width']); ?>ft &times; <?php echo htmlspecialchars($order['height']); ?>ft
                            <?php if (!empty($order['shape'])) { ?>
                                &middot; Shape: <?php echo htmlspecialchars(ucfirst($order['shape'])); ?>
                            <?php } ?>
                            &middot; Qty: <?php echo htmlspecialchars($order['quantity']); ?>
                        </p>

                        <?php if ($status === 'approved') { ?>
                            <p class="order-item-waiting"><i class="fa-solid fa-truck"></i> Confirmed for delivery. Our team will coordinate the delivery schedule with you.</p>
                        <?php } elseif ($status === 'quoted') { ?>
                            <p class="order-item-waiting"><i class="fa-solid fa-hourglass-half"></i> Awaiting your confirmation before delivery can be scheduled.</p>
                        <?php } else { ?>
                            <p class="order-item-waiting"><i class="fa-solid fa-clock"></i> Waiting for quotation from our team.</p>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<footer>
    &copy; 2026 JD PRINTING SERVICES — ALL RIGHTS RESERVED
</footer>

</body>
</html>