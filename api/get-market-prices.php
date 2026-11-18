<?php
header('Content-Type: application/json');
include '../config/database.php';

// Get parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

// Get market prices
$prices_query = "SELECT mp.*, u.username FROM market_prices mp 
                JOIN users u ON mp.updated_by = u.id 
                ORDER BY mp.updated_at DESC 
                LIMIT $limit";
$prices_result = mysqli_query($conn, $prices_query);

// Fetch prices
$prices = [];
if (mysqli_num_rows($prices_result) > 0) {
    while ($row = mysqli_fetch_assoc($prices_result)) {
        $prices[] = $row;
    }
}

// Return response
echo json_encode([
    'success' => true,
    'prices' => $prices
]);
?>
