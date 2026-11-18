<?php
header('Content-Type: application/json');
include '../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$price_id = intval($data['price_id']);

if ($price_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid price ID']);
    exit;
}

// Check if price exists
$price_query = "SELECT * FROM market_prices WHERE id = $price_id";
$price_result = mysqli_query($conn, $price_query);

if (mysqli_num_rows($price_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Market price not found']);
    exit;
}

// Delete market price
$delete_query = "DELETE FROM market_prices WHERE id = $price_id";

if (mysqli_query($conn, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Market price deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete market price: ' . mysqli_error($conn)]);
}
?>
