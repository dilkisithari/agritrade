<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

// Get recent orders
$orders_query = "SELECT o.*, u.username, u.full_name FROM orders o 
                JOIN users u ON o.buyer_id = u.id 
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
