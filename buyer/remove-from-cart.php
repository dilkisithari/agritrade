<?php
session_start();
include '../config/database.php';

// Ensure user is logged in and is buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $cart_id = intval($_GET['id']);
    $buyer_id = intval($_SESSION['user_id']);

    $query = "DELETE FROM cart WHERE id = $cart_id AND buyer_id = $buyer_id";
    if (mysqli_query($conn, $query)) {
        header("Location: shopping_cart.php");
        exit();
    } else {
        echo "Error removing item: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
