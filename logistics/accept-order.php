<?php
session_start();
include '../config/database.php';

// ✅ Check if logistics is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_id = $_SESSION['user_id'];

if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];

    // ✅ Assign the order to this logistics user
    $update_query = "
        UPDATE orders 
        SET logistics_id = $logistics_id, status = 'assigned', delivery_status = 'Pending' 
        WHERE id = $order_id AND status = 'confirmed' AND logistics_id IS NULL
    ";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Order accepted successfully.";
    } else {
        $_SESSION['message'] = "Failed to accept the order.";
    }
}

header("Location: available_orders.php");
exit();
