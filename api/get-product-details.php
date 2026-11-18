<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Get product details
$query = "SELECT p.*, u.username, u.full_name FROM products p 
          JOIN users u ON p.supplier_id = u.id 
          WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = mysqli_fetch_assoc($result);

// Return response
echo json_encode([
    'success' => true,
    'product' => $product
]);
?>
