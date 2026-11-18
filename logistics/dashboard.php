<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'logistics') {
    header("Location: ../auth/login.php");
    exit();
}

$logistics_id = $_SESSION['user_id'];

// Fetch order counts
$query = "SELECT delivery_status, COUNT(*) as count FROM orders WHERE logistics_id = $logistics_id GROUP BY delivery_status";
$result = mysqli_query($conn, $query);

$total = 0;
$pending = 0;
$picked_up = 0;
$delivered = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $total += $row['count'];
    switch ($row['delivery_status']) {
        case 'Pending': $pending = $row['count']; break;
        case 'Picked Up': $picked_up = $row['count']; break;
        case 'Delivered': $delivered = $row['count']; break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logistics Dashboard - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard { display: flex; }
        .dashboard-content { flex: 1; padding: 20px; }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 30px;
        }

        .card {
            background: linear-gradient(135deg, #f9f9f9, #ffffff);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        .card-icon {
            font-size: 36px;
            padding: 20px;
            border-radius: 50%;
            color: white;
        }

        .bg-blue { background: linear-gradient(135deg, #36D1DC, #5B86E5); }
        .bg-orange { background: linear-gradient(135deg, #FFB75E, #ED8F03); }
        .bg-cyan { background: linear-gradient(135deg, #43CEA2, #185A9D); }
        .bg-green { background: linear-gradient(135deg, #56ab2f, #a8e063); }

        .card-info h3 {
            font-size: 36px;
            margin: 0;
            color: #333;
        }

        .card-info p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #666;
        }

        @media (max-width: 768px) {
            .cards-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <div class="sidebar">
        <h3>Logistics Dashboard</h3>
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="available_orders.php"><i class="fas fa-box-open"></i> Available Orders</a></li>
            <li><a href="assigned_orders.php"><i class="fas fa-truck"></i> Assigned Orders</a></li>
            <li><a href="delivery_history.php"><i class="fas fa-history"></i> Delivery History</a></li>
            <li><a href="logistics_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2>Dashboard Overview</h2>

        <div class="cards-container">
            <div class="card">
                <div class="card-icon bg-blue">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="card-info">
                    <h3><?php echo $total; ?></h3>
                    <p>Total Assigned Orders</p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon bg-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-info">
                    <h3><?php echo $pending; ?></h3>
                    <p>Pending</p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon bg-cyan">
                    <i class="fas fa-box"></i>
                </div>
                <div class="card-info">
                    <h3><?php echo $picked_up; ?></h3>
                    <p>Picked Up</p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon bg-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-info">
                    <h3><?php echo $delivered; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
