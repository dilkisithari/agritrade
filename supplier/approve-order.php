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

// Check if order status is pending
if ($order['status'] != 'pending') {
    header("Location: supplier-orders.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        // Update order status
        $update_query = "UPDATE orders SET status = 'supplier_approved' WHERE id = $order_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Add notification for buyer
            $buyer_id = $order['buyer_id'];
            $notification_message = "Your Order #$order_id has been approved by the supplier. Please upload your payment.";
            
            $notification_query = "INSERT INTO notifications (user_id, message) 
                                 VALUES ($buyer_id, '$notification_message')";
            mysqli_query($conn, $notification_query);
            
            $success = true;
        } else {
            $errors[] = "Error updating order: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['reject'])) {
        // Update order status (you might want to add a 'rejected' status)
        $update_query = "UPDATE orders SET status = 'rejected' WHERE id = $order_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Add notification for buyer
            $buyer_id = $order['buyer_id'];
            $notification_message = "Your Order #$order_id has been rejected by the supplier.";
            
            $notification_query = "INSERT INTO notifications (user_id, message) 
                                 VALUES ($buyer_id, '$notification_message')";
            mysqli_query($conn, $notification_query);
            
            // Return products to inventory
            $return_query = "UPDATE products p 
                           JOIN order_items oi ON p.id = oi.product_id 
                           SET p.quantity = p.quantity + oi.quantity 
                           WHERE oi.order_id = $order_id";
            mysqli_query($conn, $return_query);
            
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
    <title>Approve Order - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Supplier Dashboard</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> My Products</a></li>
                <li><a href="add-product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                <li><a href="supplier-orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="supplier-payment.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="supplier_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Approve Order #<?php echo $order_id; ?></h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Order approved successfully! The buyer will be notified to upload payment.
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
                        <span class="status status-pending">Pending</span>
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
                    
                    <form action="approve-order.php?id=<?php echo $order_id; ?>" method="post" style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="submit" name="reject" class="btn btn-secondary" onclick="return confirm('Are you sure you want to reject this order?')">Reject Order</button>
                        <button type="submit" name="approve" class="btn btn-primary">Approve Order</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
