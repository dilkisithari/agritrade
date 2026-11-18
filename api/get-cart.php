<?php
header('Content-Type: application/json');
session_start();
include __DIR__ . '/config/database.php';

// Only buyers can view cart
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Please login as a buyer to view the cart', 'cart' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items joined with product info and supplier username
$query = "
    SELECT c.id AS cart_id, c.quantity AS cart_quantity, c.created_at AS added_at,
           p.id AS product_id, p.name, p.price, p.description, p.category, p.image_url, p.quantity AS stock_quantity,
           u.username AS supplier_username
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN users u ON p.supplier_id = u.id
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC, c.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cart = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Prefix image URL if exists
    $row['image_url'] = !empty($row['image_url']) ? '../' . $row['image_url'] : '../assets/images/product-placeholder.jpg';
    $cart[] = $row;
}

mysqli_stmt_close($stmt);
$conn->close();

echo json_encode([
    'success' => true,
    'cart' => $cart,
    'count' => count($cart)
]);
