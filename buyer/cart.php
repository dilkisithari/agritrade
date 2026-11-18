<?php
header('Content-Type: application/json');
session_start();
include '../config/database.php';

// Check if user is logged in and is buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    echo json_encode([
        'success' => false,
        'message' => 'Please login as a buyer to add items to cart'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST request (add to cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to POST data if JSON is not provided
    if (!$input) {
        $input = $_POST;
    }
    
    $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
    
    // Validate input
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID'
        ]);
        exit();
    }
    
    if ($quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Quantity must be greater than 0'
        ]);
        exit();
    }
    
    // Check if product exists and has enough stock
    $product_query = "SELECT * FROM products WHERE id = ? AND quantity >= ?";
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $quantity);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($product_result) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not available or insufficient stock'
        ]);
        exit();
    }
    
    $product = mysqli_fetch_assoc($product_result);
    
    // Check if item already exists in cart
    $cart_check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $cart_check_query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($cart_result) > 0) {
        // Update existing cart item
        $existing_item = mysqli_fetch_assoc($cart_result);
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if new quantity doesn't exceed stock
        if ($new_quantity > $product['quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock available. Maximum available: ' . $product['quantity']
            ]);
            exit();
        }
        
        $update_query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "iii", $new_quantity, $user_id, $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update cart'
            ]);
        }
    } else {
        // Add new item to cart
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $product_id, $quantity);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add product to cart'
            ]);
        }
    }
    
    mysqli_stmt_close($stmt);
    exit();
}

// Handle GET request with product ID (for backward compatibility)
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Get product details
    $product_query = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit();
    }
    
    $product = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
    
    mysqli_stmt_close($stmt);
    exit();
}

// If no valid request
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
?>