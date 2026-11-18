<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get parameters
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 4;

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category is required']);
    exit;
}

// Get related products
$query = "SELECT p.*, u.username FROM products p 
          JOIN users u ON p.supplier_id = u.id 
          WHERE p.category = '$category'";

if ($exclude > 0) {
    $query .= " AND p.id != $exclude";
}

$query .= " ORDER BY RAND() LIMIT $limit";

$result = mysqli_query($conn, $query);

// Fetch products
$products = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Return response
echo json_encode([
    'success' => true,
    'products' => $products
]);
?>
