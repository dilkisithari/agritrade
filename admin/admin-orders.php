<?php
session_start();
include '../config/database.php';

// ✅ Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all orders for admin view
$orders_query = "SELECT * FROM orders ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Orders - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .orders-table-container {
            padding: 20px;
        }

        table.orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .orders-table th, .orders-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 700;
        }

        .orders-table td {
            color: #444;
            font-size: 15px;
        }

        .order-status {
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: white;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }

        .status-pending { background-color: #f0ad4e; }
        .status-approved { background-color: #5cb85c; }
        .status-shipped { background-color: #0275d8; }
        .status-delivered { background-color: #5cb85c; }
        .status-cancelled { background-color: #d9534f; }
        .status-unknown { background-color: #6c757d; }

        .btn-actions a {
            text-decoration: none;
            margin-right: 8px;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
            transition: 0.3s;
        }

        .btn-view {
            background-color: #007bff;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }

        .btn-edit {
            background-color: #17a2b8;
        }

        .btn-edit:hover {
            background-color: #11707c;
        }

        @media (max-width: 768px) {
            .orders-table th, .orders-table td {
                font-size: 13px;
                padding: 10px;
            }

            .btn-actions a {
                font-size: 12px;
                padding: 4px 8px;
            }
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
                <li><a href="admin-orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="market-prices.php"><i class="fas fa-chart-line"></i> Market Prices</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>All Orders</h2>
            </div>

            <div class="orders-table-container">
                <?php if (mysqli_num_rows($orders_result) > 0): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer ID</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <!-- <th>Actions</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)):
                                $status_lower = strtolower($order['status']);
                                switch ($status_lower) {
                                    case 'pending': $status_class = 'status-pending'; break;
                                    case 'confirmed': $status_class = 'status-approved'; break;
                                    case 'shipped': $status_class = 'status-shipped'; break;
                                    case 'delivered': $status_class = 'status-delivered'; break;
                                    case 'cancelled': $status_class = 'status-cancelled'; break;
                                    default: $status_class = 'status-unknown';
                                }
                            ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['buyer_id']; ?></td>
                                    <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
                                    <td><span class="order-status <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <!-- <td class="btn-actions">
                                        <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> View</a>
                                        <a href="edit-order.php?id=<?php echo $order['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    </td> -->
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center; font-size: 18px;">No orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
