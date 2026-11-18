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

// Get supplier's orders
$orders_query = "SELECT DISTINCT o.*, u.username, u.full_name FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN products p ON oi.product_id = p.id 
                JOIN users u ON o.buyer_id = u.id 
                WHERE p.supplier_id = $user_id 
                ORDER BY o.created_at DESC 
                LIMIT $limit";
$orders_result = mysqli_query($conn, $orders_query);

// Fetch orders
$orders = [];
if (mysqli_num_rows($orders_result) > 0) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}

// Return response
echo json_encode([
    'success' => true,
    'orders' => $orders
]);
?>
