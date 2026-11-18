<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);
$buyer_id = $_SESSION['user_id'];

// Get order details
$order_query = "SELECT * FROM orders WHERE id = $order_id AND buyer_id = $buyer_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Check if order status is supplier_approved
if ($order['status'] != 'supplier_approved') {
    header("Location: orders.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle payment slip upload
    if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['payment_slip']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($_FILES['payment_slip']['size'] > $max_size) {
            $errors[] = "Image size should be less than 5MB";
        } else {
            $upload_dir = "../uploads/payments/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . $_FILES['payment_slip']['name'];
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['payment_slip']['tmp_name'], $target_file)) {
                $payment_slip = "uploads/payments/" . $file_name;
                
                // Update order status and payment slip
                $update_query = "UPDATE orders SET status = 'payment_uploaded', payment_slip = '$payment_slip' WHERE id = $order_id";
                
                if (mysqli_query($conn, $update_query)) {
                    // Add notification for supplier
                    $get_supplier_query = "SELECT DISTINCT p.supplier_id FROM order_items oi 
                                         JOIN products p ON oi.product_id = p.id 
                                         WHERE oi.order_id = $order_id";
                    $supplier_result = mysqli_query($conn, $get_supplier_query);
                    
                    while ($supplier = mysqli_fetch_assoc($supplier_result)) {
                        $supplier_id = $supplier['supplier_id'];
                        $notification_message = "Payment uploaded for Order #$order_id. Please review and approve.";
                        
                        $notification_query = "INSERT INTO notifications (user_id, message) 
                                             VALUES ($supplier_id, '$notification_message')";
                        mysqli_query($conn, $notification_query);
                    }
                    
                    $success = true;
                } else {
                    $errors[] = "Error updating order: " . mysqli_error($conn);
                }
            } else {
                $errors[] = "Failed to upload payment slip";
            }
        }
    } else {
        $errors[] = "Please select a payment slip to upload";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Payment - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Buyer Dashboard</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="browse-products.php"><i class="fas fa-search"></i> Browse Products</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li><a href="shopping_cart.php"><i class="fas fa-shopping-basket"></i> Shopping Cart</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="buyer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Upload Payment for Order #<?php echo $order_id; ?></h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Payment slip uploaded successfully! The supplier will review and approve your payment.
                    <br>
                    <a href="orders.php" class="btn btn-primary" style="margin-top: 10px;">Back to Orders</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="form-container" style="max-width: 600px; margin: 0 auto;">
                    <div class="order-summary" style="margin-bottom: 20px;">
                        <h3>Order Summary</h3>
                        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    
                    <div class="payment-instructions" style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3>Payment Instructions</h3>
                        <p>Please make a payment to the following account:</p>
                        <p><strong>Bank Name:</strong> AgriTrade Bank</p>
                        <p><strong>Account Name:</strong> AgriTrade Inc.</p>
                        <p><strong>Account Number:</strong> 1234-5678-9012-3456</p>
                        <p><strong>Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p><strong>Reference:</strong> Order #<?php echo $order_id; ?></p>
                    </div>
                    
                    <form action="upload-payment.php?id=<?php echo $order_id; ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="payment_slip">Upload Payment Slip</label>
                            <input type="file" id="payment_slip" name="payment_slip" accept="image/*" required>
                            <div class="image-preview" style="margin-top: 10px;"></div>
                            <small>Please upload a clear image of your payment receipt or screenshot.</small>
                        </div>
                        
                        <div class="form-buttons" style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">Upload Payment</button>
                            <a href="orders.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
