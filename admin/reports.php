<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Example queries to get report data
// Total number of orders
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$totalOrdersResult = mysqli_query($conn, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total_orders'] ?? 0;

// Total revenue (sum of order totals)
$totalRevenueQuery = "SELECT SUM(total_price) AS total_revenue FROM orders";
$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
$totalRevenue = mysqli_fetch_assoc($totalRevenueResult)['total_revenue'] ?? 0.00;

// Recent orders (last 10)
$recentOrdersQuery = "
    SELECT o.id, o.created_at, o.total_price, u.username AS customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
";
$recentOrdersResult = mysqli_query($conn, $recentOrdersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-content {
            padding: 40px 20px;
            width: 100%;
        }
        .report-summary {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }
        .summary-box {
            background: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px #45a049(0,0,0,0.05);
            flex: 1;
            text-align: center;
        }
        .summary-box h3 {
            font-size: 2.2em;
            margin-bottom: 5px;
        }
        .summary-box p {
            color: #666;
            font-weight: 600;
        }
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 12px #45a049(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        table.report-table th, table.report-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        table.report-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .sidebar ul li a.active {
            background-color: #45a049;
            color: #fff;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <div class="sidebar">
        <h3>Admin Dashboard</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="market-prices.php"><i class="fas fa-chart-line"></i> Market Prices</a></li>
            <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2><i class="fas fa-chart-bar"></i> Reports</h2>

        <div class="report-summary">
            <div class="summary-box">
                <h3><?php echo number_format($totalOrders); ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="summary-box">
                <h3>Rs.<?php echo number_format($totalRevenue, 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <h3>Recent Orders</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recentOrdersResult) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($recentOrdersResult)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td>Rs.<?php echo number_format($order['total_price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
