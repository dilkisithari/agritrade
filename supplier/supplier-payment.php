<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

$supplier_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);
    $action = $_POST['action'];
    $new_status = ($action === 'confirm') ? 'confirmed' : 'rejected';

    // Update payment status
    $update = "UPDATE payments SET payment_status = '$new_status' WHERE id = $payment_id";
    mysqli_query($conn, $update);

    // If confirmed, update the related order status
    if ($new_status === 'confirmed') {
        $get_order_query = "SELECT order_id FROM payments WHERE id = $payment_id";
        $order_result = mysqli_query($conn, $get_order_query);
        if ($order = mysqli_fetch_assoc($order_result)) {
            $order_id = $order['order_id'];
            mysqli_query($conn, "UPDATE orders SET status = 'confirmed' WHERE id = $order_id");
        }
    }
}

// Get payments for supplier's orders
$query = "
SELECT p.id, p.order_id, o.total_price AS amount, o.payment_slip, p.payment_status AS status, p.created_at,
       o.buyer_id
FROM payments p
JOIN orders o ON p.order_id = o.id
JOIN order_items oi ON o.id = oi.order_id
JOIN products pr ON oi.product_id = pr.id
WHERE pr.supplier_id = $supplier_id
GROUP BY p.id
ORDER BY p.created_at DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Payments - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .btn-confirm {
            background-color: #28a745;
        }
        .btn-reject {
            background-color: #dc3545;
        }
        .payment-slip {
            max-width: 100px;
            height: auto;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            color: white;
        }
        .status-confirmed {
            background-color: #28a745;
        }
        .status-rejected {
            background-color: #dc3545;
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
                <li><a href="supplier-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="supplier-payment.php" class= "active"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="supplier_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h2>Payments</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Amount</th>
                        <th>Slip</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td>User #<?php echo $row['buyer_id']; ?></td>
                            <td>Rs. <?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <?php if (!empty($row['payment_slip'])): ?>
                                    <a href="../uploads/slips/<?php echo $row['payment_slip']; ?>" target="_blank">
                                        <img src="../uploads/slips/<?php echo $row['payment_slip']; ?>" class="payment-slip">
                                    </a>
                                <?php else: ?>
                                    <span>No Slip</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn-action btn-confirm">Confirm</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-action btn-reject">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
