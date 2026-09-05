<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_submit'])) {
    $order_id = (int) $_POST['order_id'];
    $client_id = $_SESSION['client_id'];

    $stmt = mysqli_prepare($conn, "SELECT fulfillment_method FROM orders WHERE id = ? AND client_id = ? AND status = 'quoted'");
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $client_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);

    if ($order) {
        $newStatus = $order['fulfillment_method'] === 'delivery' ? 'approved' : 'waiting';
        $update = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ? AND client_id = ?");
        mysqli_stmt_bind_param($update, "sii", $newStatus, $order_id, $client_id);
        mysqli_stmt_execute($update);
    }
}

header("Location: my_orders.php");
exit();