<?php
session_start();
include '../config/database.php';

// ✅ Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// ✅ Fetch all orders
$orders_query = "SELECT * FROM orders WHERE buyer_id = $buyer_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .orders-container {
            max-width: 900px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .order-card {
            background-color: #fff;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.07);
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .order-info {
            flex: 1 1 220px;
            min-width: 180px;
        }

        .order-info span {
            font-weight: 600;
            color: #555;
            margin-right: 8px;
        }

        .order-status {
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            color: white;
            text-align: center;
            min-width: 100px;
            user-select: none;
        }
        .status-pending { background-color: #f0ad4e; }
        .status-approved { background-color: #5cb85c; }
        .status-shipped { background-color: #0275d8; }
        .status-delivered { background-color: #5cb85c; }
        .status-cancelled { background-color: #d9534f; }
        .status-unknown { background-color: #6c757d; }

        .order-actions {
            flex: 1 1 260px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-view {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.25s;
        }
        .btn-view:hover {
            background-color: #0056b3;
        }

        .upload-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .upload-form input[type="file"] {
            font-size: 14px;
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            background-color: #f9f9f9;
        }
        .btn-upload {
            background-color: #28a745;
            color: white;
            padding: 8px 14px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-upload:hover {
            background-color: #218838;
        }
        .uploaded-msg {
            color: #28a745;
            font-size: 14px;
            font-weight: 600;
            user-select: none;
        }

        @media (max-width: 600px) {
            .order-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .order-actions {
                justify-content: flex-start;
                gap: 10px;
            }
        }
    </style>
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
                <h2>My Orders</h2>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
            </div>

            <div class="orders-container">
                <?php if (mysqli_num_rows($orders_result) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                        <?php
                            // Status styling logic
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
                        <div class="order-card">
                            <div class="order-info">
                                <div><span>Order ID:</span> #<?php echo $order['id']; ?></div>
                                <div><span>Total:</span> Rs. <?php echo number_format($order['total_price'], 2); ?></div>
                                <div><span>Created:</span> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                            </div>

                            <div class="order-status <?php echo $status_class; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </div>

                            <div class="order-actions">
                                <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>

                                <?php if ($status_lower === 'pending' && empty($order['payment_slip'])): ?>
                                    <form action="upload-slip.php" method="POST" enctype="multipart/form-data" class="upload-form" style="margin:0;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="file" name="slip" required>
                                        <button type="submit" class="btn-upload" title="Upload Payment Slip">
                                            <i class="fas fa-upload"></i> Upload Slip
                                        </button>
                                    </form>
                                <?php elseif (!empty($order['payment_slip'])): ?>
                                    <span class="uploaded-msg" title="Payment slip uploaded">Slip uploaded ✅</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; font-size: 16px;">No orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
