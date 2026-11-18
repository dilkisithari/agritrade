<?php
session_start();
include '../config/database.php';

// ✅ Check if user is logged in and is a logistics user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_id = $_SESSION['user_id'];

// ✅ Fetch delivered orders assigned to this logistics user
$query = "
    SELECT o.id, o.created_at, u.full_name AS buyer_name, u.address, u.phone, o.total_price, o.delivery_status
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    WHERE o.logistics_id = $logistics_id AND o.delivery_status = 'Delivered'
    ORDER BY o.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery History - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard { display: flex; }
        .dashboard-content { flex: 1; padding: 20px; }
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .styled-table th, .styled-table td {
            border: 1px solid #ddd;
            padding: 12px;
        }
        .styled-table th {
            background-color: #f2f2f2;
        }
        .alert { padding: 10px; margin-top: 20px; }
        .alert-info { background-color: #e7f3fe; color: #31708f; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Logistics Dashboard</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="available_orders.php"><i class="fas fa-box-open"></i> Available Orders</a></li>
            <li><a href="assigned_orders.php"><i class="fas fa-truck"></i> Assigned Orders</a></li>
            <li><a href="delivery_history.php" class="active"><i class="fas fa-history"></i> Delivery History</a></li>
            <li><a href="logistics_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h2>Delivery History</h2>
        </div>

        <?php if (!$result): ?>
            <div class="alert alert-danger">Query error: <?php echo mysqli_error($conn); ?></div>
        <?php elseif (mysqli_num_rows($result) === 0): ?>
            <div class="alert alert-info">No delivered orders found.</div>
        <?php else: ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Buyer</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Total (Rs.)</th>
                        <th>Delivered At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#ORD<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo number_format($row['total_price'], 2); ?></td>
                            <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                            <td><span style="color: green;"><?php echo ucfirst($row['delivery_status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
