<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get user ID
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Check if cart table exists
$check_table_query = "SHOW TABLES LIKE 'cart'";
$table_result = mysqli_query($conn, $check_table_query);

if (mysqli_num_rows($table_result) == 0) {
    echo json_encode(['success' => true, 'count' => 0]);
    exit;
}

// Get cart count
$query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Return response
echo json_encode([
    'success' => true,
    'count' => intval($row['count'])
]);
?>
