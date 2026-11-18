<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get featured products
$query = "SELECT p.*, u.username FROM products p 
          JOIN users u ON p.supplier_id = u.id 
          WHERE p.featured = 1 
          ORDER BY p.created_at DESC 
          LIMIT 4";
$result = mysqli_query($conn, $query);

$products = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

echo json_encode($products);
?>
