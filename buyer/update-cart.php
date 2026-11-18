<?php
session_start();
include '../config/database.php';

// Ensure user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $new_quantity = intval($_POST['quantity']);
    $buyer_id = intval($_SESSION['user_id']);

    if ($new_quantity < 1) {
        $new_quantity = 1;
    }

    $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = $cart_id AND buyer_id = $buyer_id";
    if (mysqli_query($conn, $update_query)) {
        header("Location: shopping_cart.php");
        exit();
    } else {
        echo "Failed to update cart: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
