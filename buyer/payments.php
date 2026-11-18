<?php
session_start();
include '../config/database.php';

// ✅ Check if user is a logged-in buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// ✅ Fetch buyer payments with total_price from orders
$payments_query = "
    SELECT p.*, o.id AS order_id, o.total_price 
    FROM payments p 
    JOIN orders o ON p.order_id = o.id 
    WHERE o.buyer_id = $buyer_id 
    ORDER BY p.created_at DESC
";

$payments_result = mysqli_query($conn, $payments_query);
if (!$payments_result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payments - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .payments-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .payment-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 20px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .payment-card:hover {
            transform: scale(1.01);
        }

        .payment-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .status-pending {
            background-color: #f0ad4e;
        }

        .status-approved {
            background-color: #5cb85c;
        }

        .status-cancelled {
            background-color: #d9534f;
        }

        .payment-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .payment-detail {
            margin: 6px 0;
            font-size: 15px;
            color: #555;
        }

        .payment-detail span {
            font-weight: 600;
            color: #222;
            display: inline-block;
            width: 130px;
        }

        @media (max-width: 768px) {
            .payments-container {
                grid-template-columns: 1fr;
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
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
            <li><a href="shopping_cart.php"><i class="fas fa-shopping-basket"></i> Shopping Cart</a></li>
            <li><a href="payments.php" class="active"><i class="fas fa-money-bill"></i> Payments</a></li>
            <li><a href="buyer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h2>My Payments</h2>
            <div class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-count">3</span>
            </div>
        </div>

        <div class="payments-container">
            <?php if (mysqli_num_rows($payments_result) > 0): ?>
                <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                    <div class="payment-card">
                        <?php
                            $status = strtolower($payment['payment_status']);
                            $status_class = 'status-pending';
                            $status_label = 'Pending';
                            if ($status === 'confirmed') {
                                $status_class = 'status-approved';
                                $status_label = 'Confirmed';
                            } elseif ($status === 'rejected') {
                                $status_class = 'status-cancelled';
                                $status_label = 'Rejected';
                            }
                        ?>
                        <div class="payment-status <?php echo $status_class; ?>">
                            <?php echo $status_label; ?>
                        </div>
                        <div class="payment-title">Payment ID: #<?php echo $payment['id']; ?></div>
                        <div class="payment-detail"><span>Order ID:</span> #<?php echo $payment['order_id']; ?></div>
                        <div class="payment-detail"><span>Total Price:</span> Rs. <?php echo number_format($payment['total_price'], 2); ?></div>
                        <div class="payment-detail"><span>Payment Method:</span> <?php echo htmlspecialchars($payment['method']); ?></div>
                        <div class="payment-detail"><span>Proof:</span>
                            <?php if (!empty($payment['proof'])): ?>
                                <a href="../uploads/<?php echo htmlspecialchars($payment['proof']); ?>" target="_blank">View Proof</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </div>
                        <div class="payment-detail"><span>Date:</span> <?php echo date('M d, Y', strtotime($payment['created_at'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; font-size: 16px;">No payments found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
