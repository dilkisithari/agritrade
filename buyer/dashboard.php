<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Get buyer's orders count
$orders_query = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = $buyer_id";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Get pending orders count
$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = $buyer_id AND status != 'delivered'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders_count = mysqli_fetch_assoc($pending_orders_result)['count'];

// Get delivered orders count
$delivered_orders_query = "SELECT COUNT(*) as count FROM orders WHERE buyer_id = $buyer_id AND status = 'delivered'";
$delivered_orders_result = mysqli_query($conn, $delivered_orders_query);
$delivered_orders_count = mysqli_fetch_assoc($delivered_orders_result)['count'];

// Get recent orders with delivery_status
$recent_orders_query = "SELECT o.* FROM orders o 
                        WHERE o.buyer_id = $buyer_id 
                        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Buyer Dashboard</h3>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="browse-products.php"><i class="fas fa-search"></i> Browse Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li><a href="shopping_cart.php"><i class="fas fa-shopping-basket"></i> Shopping Cart</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="buyer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $orders_count; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-clock"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $pending_orders_count; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $delivered_orders_count; ?></h3>
                        <p>Delivered Orders</p>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Delivery Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>Rs.<?php echo $order['total_price']; ?></td>
                                <td>
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending':
                                            echo '<span class="status status-pending">Pending</span>';
                                            break;
                                        case 'supplier_approved':
                                            echo '<span class="status status-approved">Supplier Approved</span>';
                                            break;
                                        case 'payment_uploaded':
                                            echo '<span class="status status-pending">Payment Uploaded</span>';
                                            break;
                                        case 'payment_approved':
                                            echo '<span class="status status-approved">Payment Approved</span>';
                                            break;
                                        default:
                                            echo '<span class="status status-pending">Unknown</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    switch ($order['delivery_status']) {
                                        case 'Pending':
                                            echo '<span class="status status-pending">Pending</span>';
                                            break;
                                        case 'Picked Up':
                                            echo '<span class="status status-approved">Picked Up</span>';
                                            break;
                                        case 'Delivered':
                                            echo '<span class="status status-delivered">Delivered</span>';
                                            break;
                                        default:
                                            echo '<span class="status status-pending">Unknown</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-view">View</a>
                                    <?php if ($order['status'] == 'supplier_approved'): ?>
                                        <a href="upload-payment.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-edit">Upload Payment</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-container">
                <h3>Featured Products</h3>
                <div class="products-grid" style="padding: 20px;">
                    <?php
                    $featured_products_query = "SELECT p.*, u.username FROM products p 
                                              JOIN users u ON p.supplier_id = u.id 
                                              WHERE p.featured = 1 
                                              ORDER BY p.created_at DESC LIMIT 4";
                    $featured_products_result = mysqli_query($conn, $featured_products_query);
                    
                    while ($product = mysqli_fetch_assoc($featured_products_result)):
                    ?>
                        <div class="product-card">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                            <?php else: ?>
                                <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder">
                            <?php endif; ?>
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="price">Rs.<?php echo $product['price']; ?></p>
                            <p>Seller: <?php echo $product['username']; ?></p>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
