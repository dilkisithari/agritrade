<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is supplier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: supplier-orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);
$supplier_id = $_SESSION['user_id'];

// Check if supplier has products in this order
$check_query = "SELECT COUNT(*) as count FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $order_id AND p.supplier_id = $supplier_id";
$check_result = mysqli_query($conn, $check_query);
$check_count = mysqli_fetch_assoc($check_result)['count'];

if ($check_count == 0) {
    header("Location: supplier-orders.php");
    exit();
}

// Get order details
$order_query = "SELECT o.*, u.username, u.full_name FROM orders o 
               JOIN users u ON o.buyer_id = u.id 
               WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

// Check if order status is payment_uploaded
if ($order['status'] != 'payment_uploaded') {
    header("Location: supplier-orders.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        // Update order status
        $update_query = "UPDATE orders SET status = 'payment_approved' WHERE id = $order_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Add notification for buyer
            $buyer_id = $order['buyer_id'];
            $notification_message = "Your payment for Order #$order_id has been approved. Your order will be processed for delivery.";
            
            $notification_query = "INSERT INTO notifications (user_id, message) 
                                 VALUES ($buyer_id, '$notification_message')";
            mysqli_query($conn, $notification_query);
            
            // Add notification for logistics
            $logistics_query = "SELECT id FROM users WHERE role = 'logistics'";
            $logistics_result = mysqli_query($conn, $logistics_query);
            
            while ($logistics = mysqli_fetch_assoc($logistics_result)) {
                $logistics_id = $logistics['id'];
                $notification_message = "New order #$order_id is available for delivery assignment.";
                
                $notification_query = "INSERT INTO notifications (user_id, message) 
                                     VALUES ($logistics_id, '$notification_message')";
                mysqli_query($conn, $notification_query);
            }
            
            $success = true;
        } else {
            $errors[] = "Error updating order: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['reject'])) {
        // Update order status back to supplier_approved
        $update_query = "UPDATE orders SET status = 'supplier_approved' WHERE id = $order_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Add notification for buyer
            $buyer_id = $order['buyer_id'];
            $notification_message = "Your payment for Order #$order_id has been rejected. Please upload a valid payment slip.";
            
            $notification_query = "INSERT INTO notifications (user_id, message) 
                                 VALUES ($buyer_id, '$notification_message')";
            mysqli_query($conn, $notification_query);
            
            $success = true;
            header("Location: supplier-orders.php");
            exit();
        } else {
            $errors[] = "Error updating order: " . mysqli_error($conn);
        }
    }
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $order_id AND p.supplier_id = $supplier_id";
$items_result = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Payment - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Supplier Dashboard</h3>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> My Products</a></li>
                <li><a href="add-product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                <li><a href="supplier-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="supplier-payment.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="supplier_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Approve Payment for Order #<?php echo $order_id; ?></h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Payment approved successfully! The order is now available for logistics assignment.
                    <br>
                    <a href="supplier-orders.php" class="btn btn-primary" style="margin-top: 10px;">Back to Orders</a>
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
                
                <div class="order-details" style="background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Order #<?php echo $order_id; ?></h3>
                        <span class="status status-pending">Payment Uploaded</span>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 20px;">
                        <div style="flex: 1; min-width: 200px;">
                            <h4>Order Date</h4>
                            <p><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px;">
                            <h4>Buyer</h4>
                            <p><?php echo $order['full_name']; ?> (<?php echo $order['username']; ?>)</p>
                        </div>
                        
                        <div style="flex: 1; min-width: 200px;">
                            <h4>Total Amount</h4>
                            <p>$<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                    </div>
                    
                    <h4>Payment Slip</h4>
                    <div style="margin: 20px 0; text-align: center;">
                        <?php if (!empty($order['payment_slip'])): ?>
                            <img src="../<?php echo $order['payment_slip']; ?>" alt="Payment Slip" style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: var(--shadow);">
                        <?php else: ?>
                            <p>No payment slip uploaded.</p>
                        <?php endif; ?>
                    </div>
                    
                    <h4>Order Items</h4>
                    <div class="table-container" style="margin-top: 10px; margin-bottom: 20px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                while ($item = mysqli_fetch_assoc($items_result)): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td style="display: flex; align-items: center; gap: 15px;">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php endif; ?>
                                            <div>
                                                <h4><?php echo $item['name']; ?></h4>
                                            </div>
                                        </td>
                                        <td>$<?php echo $item['price']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                    <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <form action="approve-payment.php?id=<?php echo $order_id; ?>" method="post" style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="submit" name="reject" class="btn btn-secondary" onclick="return confirm('Are you sure you want to reject this payment?')">Reject Payment</button>
                        <button type="submit" name="approve" class="btn btn-primary">Approve Payment</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
