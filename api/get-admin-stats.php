<?php
header('Content-Type: application/json');
include '../config/database.php';

// Check if user is admin (you might want to add more robust authentication)
// For now, we'll skip this check for simplicity

// Get counts for dashboard
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_count = mysqli_fetch_assoc($users_result)['count'];

$suppliers_query = "SELECT COUNT(*) as count FROM users WHERE role = 'supplier'";
$suppliers_result = mysqli_query($conn, $suppliers_query);
$suppliers_count = mysqli_fetch_assoc($suppliers_result)['count'];

$buyers_query = "SELECT COUNT(*) as count FROM users WHERE role = 'buyer'";
$buyers_result = mysqli_query($conn, $buyers_query);
$buyers_count = mysqli_fetch_assoc($buyers_result)['count'];

$logistics_query = "SELECT COUNT(*) as count FROM users WHERE role = 'logistics'";
$logistics_result = mysqli_query($conn, $logistics_query);
$logistics_count = mysqli_fetch_assoc($logistics_result)['count'];

$products_query = "SELECT COUNT(*) as count FROM products";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['count'];

$orders_query = "SELECT COUNT(*) as count FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Return response
echo json_encode([
    'success' => true,
    'stats' => [
        'users_count' => $users_count,
        'suppliers_count' => $suppliers_count,
        'buyers_count' => $buyers_count,
        'logistics_count' => $logistics_count,
        'products_count' => $products_count,
        'orders_count' => $orders_count
    ]
]);
?>
