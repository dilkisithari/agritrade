<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is supplier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

$supplier_id = $_SESSION['user_id'];

// Get supplier's products count
$products_query = "SELECT COUNT(*) as count FROM products WHERE supplier_id = $supplier_id";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['count'];

// Get supplier's orders count
$orders_query = "SELECT COUNT(*) as count FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN products p ON oi.product_id = p.id 
                WHERE p.supplier_id = $supplier_id";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Get pending orders count
$pending_orders_query = "SELECT COUNT(*) as count FROM orders o 
                        JOIN order_items oi ON o.id = oi.order_id 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE p.supplier_id = $supplier_id AND o.status = 'pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders_count = mysqli_fetch_assoc($pending_orders_result)['count'];

// Get recent orders with delivery_status
$recent_orders_query = "SELECT DISTINCT o.*, o.delivery_status, u.username, u.full_name FROM orders o 
                        JOIN order_items oi ON o.id = oi.order_id 
                        JOIN products p ON oi.product_id = p.id 
                        JOIN users u ON o.buyer_id = u.id 
                        WHERE p.supplier_id = $supplier_id 
                        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - AgriTrade</title>
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
                <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">2</span>
                </div>
            </div>
            
<div class="dashboard-cards">
    <div class="dashboard-card">
        <i class="fas fa-box"></i>
        <div class="dashboard-card-content">
            <h3><?php echo $products_count; ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    
    <div class="dashboard-card">
        <i class="fas fa-shopping-cart"></i>
        <div class="dashboard-card-content">
            <h3><?php echo $orders_count; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
</div>


            
<div class="table-container">
    <h3>Recent Orders</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Amount</th>
                <th>Delivery</th> <!-- Kept delivery column -->
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo $order['full_name']; ?> (<?php echo $order['username']; ?>)</td>
                    <td>Rs.<?php echo $order['total_price']; ?></td>
                    <td>
                        <?php
                        $d = $order['delivery_status'];
                        if ($d == 'Pending') {
                            echo '<span class="status status-pending">Pending</span>';
                        } elseif ($d == 'Picked Up') {
                            echo '<span class="status status-approved">Picked Up</span>';
                        } elseif ($d == 'Delivered') {
                            echo '<span class="status status-delivered">Delivered</span>';
                        } else {
                            echo '<span class="status status-pending">N/A</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td class="action-buttons">
                        <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-view">View</a>
                        <!-- Removed approve buttons that depend on status -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

            
            <div class="table-container">
                <h3>My Products</h3>
                <div style="text-align: right; margin-bottom: 10px;">
                    <a href="add-product.php" class="btn btn-primary">Add New Product</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products_query = "SELECT * FROM products WHERE supplier_id = $supplier_id ORDER BY created_at DESC LIMIT 5";
                        $products_result = mysqli_query($conn, $products_query);
                        
                        while ($product = mysqli_fetch_assoc($products_result)):
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo ucfirst($product['category']); ?></td>
                                <td>Rs.<?php echo $product['price']; ?></td>
                                <td><?php echo $product['quantity']; ?></td>
                                <td class="action-buttons">
                                    <a href="products.php" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="products.php" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
