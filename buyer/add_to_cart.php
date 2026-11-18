<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

$buyer_id = $_SESSION['user_id'];
$product_id = (int)$_GET['id'];

// Check if already in cart
$check_query = "SELECT * FROM cart WHERE buyer_id = $buyer_id AND product_id = $product_id";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(["success" => true, "message" => "Product already in cart"]);
    exit;
}

// Insert into cart
$insert_query = "INSERT INTO cart (buyer_id, product_id, quantity) VALUES ($buyer_id, $product_id, 1)";
if (mysqli_query($conn, $insert_query)) {
    echo json_encode(["success" => true, "message" => "Product added to cart"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add to cart"]);
}
