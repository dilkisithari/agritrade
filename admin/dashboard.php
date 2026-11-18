<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get counts for dashboard
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_count = mysqli_fetch_assoc($users_result)['count'];

$suppliers_query = "SELECT COUNT(*) as count FROM users WHERE role = 'supplier'";
$suppliers_result = mysqli_query($conn, $suppliers_query);
$suppliers_count = mysqli_fetch_assoc($suppliers_result)['count'];

$buyers_query = "SELECT COUNT(*) as count FROM users WHERE role = 'buyer'";
$buyers_result = mysqli_query($conn, $buyers_query);
$buyers_count = mysqli_fetch_assoc($buyers_result)['count'];

$logistics_query = "SELECT COUNT(*) as count FROM users WHERE role = 'logistics'";
$logistics_result = mysqli_query($conn, $logistics_query);
$logistics_count = mysqli_fetch_assoc($logistics_result)['count'];

$products_query = "SELECT COUNT(*) as count FROM products";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['count'];

$orders_query = "SELECT COUNT(*) as count FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.username, u.full_name FROM orders o 
                        JOIN users u ON o.buyer_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Admin Dashboard</h3>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="market-prices.php"><i class="fas fa-chart-line"></i> Market Prices</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Welcome, Admin!</h2>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <i class="fas fa-users"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $users_count; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-user-tie"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $suppliers_count; ?></h3>
                        <p>Suppliers</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-user"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $buyers_count; ?></h3>
                        <p>Buyers</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-truck"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $logistics_count; ?></h3>
                        <p>Logistics</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-box"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $products_count; ?></h3>
                        <p>Products</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="dashboard-card-content">
                        <h3><?php echo $orders_count; ?></h3>
                        <p>Orders</p>
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
                            <th>Delivery Status</th>
                            <th>Date</th>
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
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-container">
                <h3>Market Prices</h3>
                <div style="text-align: right; margin-bottom: 10px;">
                    <a href="add-market-price.php" class="btn btn-primary">Add Market Price</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $market_prices_query = "SELECT mp.*, u.username FROM market_prices mp 
                                              JOIN users u ON mp.updated_by = u.id 
                                              ORDER BY mp.updated_at DESC LIMIT 5";
                        $market_prices_result = mysqli_query($conn, $market_prices_query);
                        
                        while ($price = mysqli_fetch_assoc($market_prices_result)):
                        ?>
                            <tr>
                                <td><?php echo $price['product_name']; ?></td>
                                <td><?php echo ucfirst($price['category']); ?></td>
                                <td>$<?php echo $price['price']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($price['updated_at'])); ?> by <?php echo $price['username']; ?></td>
                                <td class="action-buttons">
                                    <a href="market-prices.php?edit=<?php echo $price['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="market-prices.php?delete=<?php echo $price['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this price?')">Delete</a>
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
