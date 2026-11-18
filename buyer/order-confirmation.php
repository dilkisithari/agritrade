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

// Get order items
$items_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - AgriTrade</title>
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
                <h2>Order Confirmation</h2>
            </div>
            
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Your order has been placed successfully!
            </div>
            
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
                            <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
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
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="order-status" style="margin-bottom: 20px;">
                    <h4>Order Status</h4>
                    <div class="status-timeline" style="display: flex; justify-content: space-between; margin-top: 15px; position: relative;">
                        <div class="status-step active" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                            <div class="status-icon" style="width: 30px; height: 30px; background-color: var(--primary-color); border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-check" style="color: #fff; font-size: 12px;"></i>
                            </div>
                            <div class="status-label">Order Placed</div>
                        </div>
                        
                        <div class="status-step" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                            <div class="status-icon" style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto 10px;"></div>
                            <div class="status-label">Supplier Approval</div>
                        </div>
                        
                        <div class="status-step" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                            <div class="status-icon" style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto 10px;"></div>
                            <div class="status-label">Payment</div>
                        </div>
                        
                        <div class="status-step" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                            <div class="status-icon" style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto 10px;"></div>
                            <div class="status-label">Shipping</div>
                        </div>
                        
                        <div class="status-step" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                            <div class="status-icon" style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto 10px;"></div>
                            <div class="status-label">Delivered</div>
                        </div>
                        
                        <!-- Progress bar -->
                        <div class="status-progress" style="position: absolute; top: 15px; left: 15px; right: 15px; height: 2px; background-color: #ddd; z-index: 0;">
                            <div class="status-progress-bar" style="width: 10%; height: 100%; background-color: var(--primary-color);"></div>
                        </div>
                    </div>
                </div>
                
                <div class="order-next-steps">
                    <h4>Next Steps</h4>
                    <p>Your order is now pending supplier approval. Once approved, you will be notified to upload your payment slip.</p>
                    <p>You can track the status of your order in the <a href="orders.php">My Orders</a> section.</p>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="browse-products.php" class="btn btn-primary">Continue Shopping</a>
                <a href="orders.php" class="btn btn-secondary">View All Orders</a>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
