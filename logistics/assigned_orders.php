<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_id = $_SESSION['user_id'];

// Handle delivery status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['delivery_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['delivery_status']);

    $update_query = "UPDATE orders 
                     SET delivery_status = '$status', 
                         status = IF('$status' = 'Delivered', 'delivered', status) 
                     WHERE id = $order_id AND logistics_id = $logistics_id";
    mysqli_query($conn, $update_query);
    $_SESSION['message'] = "Delivery status updated successfully.";
    header("Location: assigned_orders.php");
    exit();
}

// Fetch all assigned orders
$query = "SELECT o.id, o.created_at, o.delivery_status, u.full_name AS buyer_name, u.address, u.phone, o.total_price
          FROM orders o
          JOIN users u ON o.buyer_id = u.id
          WHERE o.logistics_id = $logistics_id
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);

// Count orders by status
$total = $pending = $picked_up = $delivered = 0;
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
    $total++;
    switch ($row['delivery_status']) {
        case 'Pending':
            $pending++; break;
        case 'Picked Up':
            $picked_up++; break;
        case 'Delivered':
            $delivered++; break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Orders - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard { display: flex; }
        .dashboard-content { flex: 1; padding: 20px; }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            font-size: 32px;
            color: #333;
        }

        .card p {
            color: #777;
            margin: 5px 0 0;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
        }

        .styled-table th, .styled-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .styled-table th {
            background-color: #f8f8f8;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-picked { background-color: #17a2b8; color: #fff; }
        .badge-delivered { background-color: #28a745; color: #fff; }

        select, button {
            padding: 6px 10px;
            font-size: 14px;
        }

        .btn-update {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .alert-success {
            background-color: #d4edda;
            padding: 10px;
            color: #155724;
            margin-bottom: 20px;
            border-radius: 6px;
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
            <li><a href="available_orders.php"><i class="fas fa-box-open"></i> Available Orders</a></li>
            <li><a href="assigned_orders.php" class="active"><i class="fas fa-truck"></i> Assigned Orders</a></li>
            <li><a href="delivery_history.php"><i class="fas fa-history"></i> Delivery History</a></li>
            <li><a href="logistics_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2>Assigned Orders</h2>

        <!-- Cards -->
        <!-- <div class="cards-container">
            <div class="card">
                <h3><?php echo $total; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="card">
                <h3><?php echo $pending; ?></h3>
                <p>Pending</p>
            </div>
            <div class="card">
                <h3><?php echo $picked_up; ?></h3>
                <p>Picked Up</p>
            </div>
            <div class="card">
                <h3><?php echo $delivered; ?></h3>
                <p>Delivered</p>
            </div>
        </div> -->

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Total (Rs.)</th>
                        <th>Accepted Date</th>
                        <th>Delivery Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td>#ORD<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo number_format($row['total_price'], 2); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php
                                    $badge = '';
                                    if ($row['delivery_status'] == 'Pending') {
                                        $badge = 'badge badge-pending';
                                    } elseif ($row['delivery_status'] == 'Picked Up') {
                                        $badge = 'badge badge-picked';
                                    } elseif ($row['delivery_status'] == 'Delivered') {
                                        $badge = 'badge badge-delivered';
                                    }
                                ?>
                                <span class="<?php echo $badge; ?>"><?php echo $row['delivery_status']; ?></span>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 8px;" action="">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="delivery_status">
                                        <option value="Pending" <?php if ($row['delivery_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Picked Up" <?php if ($row['delivery_status'] == 'Picked Up') echo 'selected'; ?>>Picked Up</option>
                                        <option value="Delivered" <?php if ($row['delivery_status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert-info">No assigned orders found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
