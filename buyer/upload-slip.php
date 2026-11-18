<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);

    if (isset($_FILES['slip']) && $_FILES['slip']['error'] === 0) {
        $slip_tmp = $_FILES['slip']['tmp_name'];
        $slip_name = basename($_FILES['slip']['name']);
        $slip_ext = strtolower(pathinfo($slip_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($slip_ext, $allowed_exts)) {
            $new_filename = 'slip_' . time() . '_' . $order_id . '.' . $slip_ext;
            $upload_path = '../uploads/slips/' . $new_filename;

            if (move_uploaded_file($slip_tmp, $upload_path)) {
                // ✅ Save slip info to DB, but do not change status
                $query = "UPDATE orders SET payment_slip = '$new_filename' WHERE id = $order_id AND buyer_id = $buyer_id";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = "Slip uploaded successfully.";
                } else {
                    $_SESSION['error'] = "Database update failed.";
                }
            } else {
                $_SESSION['error'] = "Failed to upload slip.";
            }
        } else {
            $_SESSION['error'] = "Invalid file format. Only JPG, PNG, and PDF are allowed.";
        }
    } else {
        $_SESSION['error'] = "No file uploaded or file error.";
    }

    header("Location: orders.php");
    exit();
}
?>
