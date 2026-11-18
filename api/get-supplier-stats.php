<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get user ID
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

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

// Get supplier's products count
$products_query = "SELECT COUNT(*) as count FROM products WHERE supplier_id = $user_id";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['count'];

// Get supplier's orders count
$orders_query = "SELECT COUNT(DISTINCT o.id) as count FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN products p ON oi.product_id = p.id 
                WHERE p.supplier_id = $user_id";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Get pending orders count
$pending_orders_query = "SELECT COUNT(DISTINCT o.id) as count FROM orders o 
                        JOIN order_items oi ON o.id = oi.order_id 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE p.supplier_id = $user_id AND o.status = 'pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders_count = mysqli_fetch_assoc($pending_orders_result)['count'];

// Return response
echo json_encode([
    'success' => true,
    'stats' => [
        'products_count' => $products_count,
        'orders_count' => $orders_count,
        'pending_orders_count' => $pending_orders_count
    ]
]);
?>
