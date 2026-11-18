<?php
session_start();
include '../config/database.php';

// ✅ Ensure supplier is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

$supplier_id = $_SESSION['user_id'];

// ✅ Fetch orders related to this supplier
$query = "
    SELECT o.id, o.buyer_id, o.total_price, o.status, o.created_at
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.supplier_id = $supplier_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Supplier Orders - AgriTrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ✅ Fonts and Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f5f6fa;
        }

        .dashboard-content {
            padding: 40px 20px;
            width: 100%;
        }

        .dashboard-header h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .orders-table-container {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        table.orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .orders-table th, .orders-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 600;
        }

        .orders-table td {
            color: #444;
            font-size: 15px;
        }

        .order-status {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
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
        .status-unknown { background-color: #5cb85c; }

        @media (max-width: 768px) {
            .orders-table th, .orders-table td {
                font-size: 12px;
                padding: 10px;
            }

            .dashboard-header h2 {
                font-size: 18px;
            }
        }
    </style>
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
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h2><i class="fas fa-shopping-basket"></i> Orders for Your Products</h2>
        </div>

        <div class="orders-table-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer ID</th>
                            <th>Total Price (Rs.)</th>
                            <th>Status</th>
                            <th>Ordered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)):
                            $status_class = match (strtolower($order['status'])) {
                                'pending' => 'status-pending',
                                'confirmed' => 'status-approved',
                                'shipped' => 'status-shipped',
                                'delivered' => 'status-delivered',
                                'cancelled' => 'status-cancelled',
                                default => 'status-unknown'
                            };
                        ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['buyer_id']; ?></td>
                                <td><?php echo number_format($order['total_price'], 2); ?></td>
                                <td><span class="order-status <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; font-size: 16px;">No orders found for your products.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
