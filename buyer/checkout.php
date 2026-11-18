<?php 
header('Content-Type: application/json');
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['buyer_id']) || !is_numeric($data['buyer_id']) ||
    !isset($data['payment_method']) || empty($data['payment_method']) ||
    !isset($data['payment_reference']) || empty($data['payment_reference'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$buyer_id = intval($data['buyer_id']);
$payment_method = mysqli_real_escape_string($conn, $data['payment_method']);
$payment_reference = mysqli_real_escape_string($conn, $data['payment_reference']);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'buyer'");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found or not a buyer']);
    exit;
}
$stmt->close();

$cart_query = "SELECT c.product_id, c.quantity, p.price, p.quantity as available_quantity, p.supplier_id 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.buyer_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;
$stock_error = false;

while ($item = $result->fetch_assoc()) {
    if ($item['quantity'] > $item['available_quantity']) {
        $stock_error = true;
        break;
    }
    $subtotal = $item['price'] * $item['quantity'];
    $total_price += $subtotal;
    $cart_items[] = $item;
}
$stmt->close();

if ($stock_error || empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'One or more items are out of stock or cart is empty']);
    exit;
}

$conn->begin_transaction();

try {
    $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, total_price, status) VALUES (?, ?, 'pending')");
    $order_stmt->bind_param("id", $buyer_id, $total_price);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    $payment_stmt = $conn->prepare("INSERT INTO payments (`order_id`, `buyer_id`, `method`, `reference`) VALUES (?, ?, ?, ?)");
    $payment_stmt->bind_param("iiss", $order_id, $buyer_id, $payment_method, $payment_reference);
    $payment_stmt->execute();
    $payment_stmt->close();

    foreach ($cart_items as $item) {
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $item_stmt->execute();
        $item_stmt->close();

        $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stmt->execute();
        $update_stmt->close();

        $message = "New order #$order_id received for your product(s).";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (buyer_id, message) VALUES (?, ?)");
        $notif_stmt->bind_param("is", $item['supplier_id'], $message);
        $notif_stmt->execute();
        $notif_stmt->close();
    }

    $clear_stmt = $conn->prepare("DELETE FROM cart WHERE buyer_id = ?");
    $clear_stmt->bind_param("i", $buyer_id);
    $clear_stmt->execute();
    $clear_stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order and payment processed successfully',
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
