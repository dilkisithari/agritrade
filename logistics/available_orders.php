<?php
session_start();
include '../config/database.php';

// ✅ Check if logistics is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_id = $_SESSION['user_id'];

// ✅ Fetch all confirmed orders not assigned
$query = "
    SELECT o.id, o.created_at, u.full_name AS buyer_name, u.address, u.phone, o.total_price
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    WHERE LOWER(o.status) = 'confirmed' AND o.logistics_id IS NULL
    ORDER BY o.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Orders - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard { display: flex; }
        .dashboard-content { flex: 1; padding: 20px; }

        .order-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .order-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-left: 5px solid #28a745;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .order-card h3 {
            margin-top: 0;
            color: #333;
        }

        .order-details p {
            margin: 6px 0;
        }

        .order-meta {
            font-size: 14px;
            color: #777;
        }

        .btn-accept {
            display: inline-block;
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-accept:hover {
            background-color: #218838;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-info {
            background-color: #e7f3fe;
            color: #31708f;
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <div class="sidebar">
        <h3>Logistics Dashboard</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="available_orders.php" class="active"><i class="fas fa-box-open"></i> Available Orders</a></li>
            <li><a href="assigned_orders.php"><i class="fas fa-truck"></i> Assigned Orders</a></li>
            <li><a href="delivery_history.php"><i class="fas fa-history"></i> Delivery History</a></li>
            <li><a href="logistics_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2>Available Orders</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="order-cards">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="order-card">
                        <h3>#ORD<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                        <div class="order-details">
                            <p><strong>Buyer:</strong> <?php echo htmlspecialchars($row['buyer_name']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                            <p><strong>Total:</strong> Rs. <?php echo number_format($row['total_price'], 2); ?></p>
                            <p class="order-meta"><i class="far fa-clock"></i> <?php echo date('Y-m-d', strtotime($row['created_at'])); ?></p>
                        </div>
                        <a class="btn-accept" href="accept-order.php?order_id=<?php echo $row['id']; ?>"><i class="fas fa-check-circle"></i> Accept Order</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No confirmed orders available.</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
