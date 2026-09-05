<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO inventory_items (item_name, unit, quantity_on_hand, reorder_level) VALUES (?, ?, ?, ?)");
    $item_name = trim($_POST['item_name']);
    $unit = trim($_POST['unit']);
    $quantity_on_hand = (float) $_POST['quantity_on_hand'];
    $reorder_level = (float) $_POST['reorder_level'];
    mysqli_stmt_bind_param($stmt, "ssdd", $item_name, $unit, $quantity_on_hand, $reorder_level);
    mysqli_stmt_execute($stmt);
    header("Location: inventory.php?added=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock'])) {
    $item_id = (int) $_POST['item_id'];
    $quantity = (float) $_POST['quantity'];

    if ($quantity > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE inventory_items SET quantity_on_hand = quantity_on_hand + ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "di", $quantity, $item_id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: inventory.php?restocked=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deduct_stock'])) {
    $item_id = (int) $_POST['item_id'];
    $quantity = (float) $_POST['quantity'];
    $order_id = !empty($_POST['order_id']) ? (int) $_POST['order_id'] : null;

    if ($quantity > 0) {
        mysqli_begin_transaction($conn);

        $update = mysqli_prepare($conn, "UPDATE inventory_items SET quantity_on_hand = GREATEST(quantity_on_hand - ?, 0) WHERE id = ?");
        mysqli_stmt_bind_param($update, "di", $quantity, $item_id);
        mysqli_stmt_execute($update);

        $log = mysqli_prepare($conn, "INSERT INTO inventory_logs (item_id, order_id, staff_id, quantity) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($log, "iiid", $item_id, $order_id, $staff_id, $quantity);
        mysqli_stmt_execute($log);

        if ($order_id) {
            $statusCheck = mysqli_prepare($conn, "SELECT status FROM orders WHERE id = ?");
            mysqli_stmt_bind_param($statusCheck, "i", $order_id);
            mysqli_stmt_execute($statusCheck);
            $statusResult = mysqli_stmt_get_result($statusCheck);
            $currentStatus = mysqli_fetch_assoc($statusResult)['status'] ?? null;

            if ($currentStatus === 'approved') {
                $statusUpdate = mysqli_prepare($conn, "UPDATE orders SET status = 'preparing' WHERE id = ? AND status = 'approved'");
                mysqli_stmt_bind_param($statusUpdate, "i", $order_id);
                mysqli_stmt_execute($statusUpdate);
            } elseif ($currentStatus === 'waiting') {
                $statusUpdate = mysqli_prepare($conn, "UPDATE orders SET status = 'confirmed' WHERE id = ? AND status = 'waiting'");
                mysqli_stmt_bind_param($statusUpdate, "i", $order_id);
                mysqli_stmt_execute($statusUpdate);
            }
        }

        mysqli_commit($conn);
    }

    if ($order_id) {
        header("Location: orders.php?deducted=1");
    } else {
        header("Location: inventory.php?restocked=1");
    }
    exit();
}

header("Location: inventory.php");
exit();