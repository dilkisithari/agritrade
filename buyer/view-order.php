<?php
session_start();
include '../config/database.php';

// Check if logged in and role buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Validate order id from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['id']);

// Fetch order, make sure belongs to this buyer
$order_sql = "SELECT * FROM orders WHERE id = $order_id AND buyer_id = $buyer_id LIMIT 1";
$order_res = mysqli_query($conn, $order_sql);

if (!$order_res || mysqli_num_rows($order_res) == 0) {
    die("Order not found or access denied.");
}

$order = mysqli_fetch_assoc($order_res);

// Fetch order items — assuming you have an `order_items` table with order_id, product_id, quantity, price, etc.
$items_sql = "
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
";
$items_res = mysqli_query($conn, $items_sql);

if (!$items_res) {
    die("Failed to get order items: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order #<?php echo $order_id; ?> Details - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .order-details-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .order-info {
            margin-bottom: 30px;
            font-size: 16px;
            color: #555;
        }
        .order-info strong {
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f8f8f8;
            font-weight: 600;
            color: #444;
        }
        img.product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
        }
        .total-row {
            font-weight: 700;
            font-size: 18px;
            background-color: #f9f9f9;
        }
        .back-link {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #218838;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
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
                <h2>Order Details</h2>
            </div>

            <div class="order-details-container">
                <div class="order-info">
                    <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>Price (Rs.)</th>
                            <th>Subtotal (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        while ($item = mysqli_fetch_assoc($items_res)): 
                            $subtotal = $item['quantity'] * $item['price'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="4" style="text-align:right;">Total:</td>
                            <td>Rs. <?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <a href="orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Orders</a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
