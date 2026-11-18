<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_order_id = $_POST['logistics_order_id'];
$new_status = $_POST['new_status'];

mysqli_query($conn, "UPDATE logistics_orders SET status = '$new_status' WHERE id = $logistics_order_id");

header("Location: assigned_orders.php");
exit();
