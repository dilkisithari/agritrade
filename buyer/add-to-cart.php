<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = (int)$_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    $exists = mysqli_query($conn, "SELECT id FROM cart WHERE buyer_id = $buyer_id AND product_id = $product_id LIMIT 1");
    if ($exists && mysqli_num_rows($exists) == 0) {
        mysqli_query($conn, "INSERT INTO cart (buyer_id, user_id, product_id, quantity) VALUES ($buyer_id, $buyer_id, $product_id, 1)");
    }
}
header("Location: shopping_cart.php");
exit();
