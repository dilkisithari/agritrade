<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get parameters
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Check if user exists and is a supplier
$user_query = "SELECT * FROM users WHERE id = $user_id AND role = 'supplier'";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

// Get supplier's products
$products_query = "SELECT * FROM products WHERE supplier_id = $user_id ORDER BY created_at DESC LIMIT $limit";
$products_result = mysqli_query($conn, $products_query);

// Fetch products
$products = [];
if (mysqli_num_rows($products_result) > 0) {
    while ($row = mysqli_fetch_assoc($products_result)) {
        $products[] = $row;
    }
}

// Return response
echo json_encode([
    'success' => true,
    'products' => $products
]);
?>
