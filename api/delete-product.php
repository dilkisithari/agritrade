<?php
header('Content-Type: application/json');
include '../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$user_id = intval($data['user_id']);
$product_id = intval($data['product_id']);

if ($user_id <= 0 || $product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if user exists and is a supplier
$user_query = "SELECT * FROM users WHERE id = $user_id AND role = 'supplier'";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

// Check if product exists and belongs to the supplier
$product_query = "SELECT * FROM products WHERE id = $product_id AND supplier_id = $user_id";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found or does not belong to you']);
    exit;
}

// Check if product is in any order
$order_check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = $product_id";
$order_check_result = mysqli_query($conn, $order_check_query);
$order_count = mysqli_fetch_assoc($order_check_result)['count'];

if ($order_count > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete product as it is part of one or more orders']);
    exit;
}

// Delete product
$delete_query = "DELETE FROM products WHERE id = $product_id";

if (mysqli_query($conn, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete product: ' . mysqli_error($conn)]);
}
?>
