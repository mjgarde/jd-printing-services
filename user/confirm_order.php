<?php
session_start();
require_once '../../config/db.php';

function showAlert($message, $redirect = 'my_orders.php') {
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='" . $redirect . "';</script>";
    exit();
}

if (!isset($_SESSION['client_id'])) {
    showAlert('Please login first.', '../index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_submit'])) {
    $client_id = $_SESSION['client_id'];
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);

    $check = mysqli_query($conn, "SELECT id, status FROM orders WHERE id = '$order_id' AND client_id = '$client_id'");

    if (mysqli_num_rows($check) === 0) {
        showAlert('Order not found.');
    }

    $order = mysqli_fetch_assoc($check);

    if ($order['status'] !== 'quoted') {
        showAlert('This order cannot be confirmed right now.');
    }

    $sql = "UPDATE orders SET status = 'approved' WHERE id = '$order_id' AND client_id = '$client_id'";

    if (mysqli_query($conn, $sql)) {
        showAlert('Order confirmed! Production will begin shortly.');
    } else {
        showAlert('Database error. Please try again.');
    }
} else {
    header("Location: my_orders.php");
    exit();
}
?>