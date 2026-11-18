<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle platform settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_platform_settings'])) {
        $platform_name = mysqli_real_escape_string($conn, $_POST['platform_name']);
        $commission_rate = (float) $_POST['commission_rate'];
        $support_email = mysqli_real_escape_string($conn, $_POST['support_email']);

        $query = "UPDATE settings SET 
                    platform_name = '$platform_name', 
                    commission_rate = $commission_rate, 
                    support_email = '$support_email' 
                  WHERE id = 1";
        mysqli_query($conn, $query);
        $_SESSION['success_message'] = "Platform settings updated successfully.";
    }

    if (isset($_POST['update_security_settings'])) {
        $min_password_length = (int) $_POST['min_password_length'];
        $session_timeout = (int) $_POST['session_timeout'];
        $max_login_attempts = (int) $_POST['max_login_attempts'];

        $query = "UPDATE settings SET 
                    min_password_length = $min_password_length, 
                    session_timeout = $session_timeout, 
                    max_login_attempts = $max_login_attempts 
                  WHERE id = 1";
        mysqli_query($conn, $query);
        $_SESSION['success_message'] = "Security settings updated successfully.";
    }

    header("Location: admin-settings.php");
    exit();
}

// Load current settings
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM settings WHERE id = 1"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- ✅ Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .settings-container {
            display: flex;
            gap: 40px;
            padding: 20px;
        }
        .settings-box {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .settings-box h3 {
            margin-bottom: 10px;
        }
        .settings-box label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        .settings-box input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .settings-box button {
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .alert-success {
            background: #d4edda;
            padding: 10px;
            color: #155724;
            margin: 10px 20px;
            border-radius: 5px;
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
                <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="market-prices.php"><i class="fas fa-chart-line"></i> Market Prices</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Settings</h2>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert-success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Platform Settings -->
                <form method="post" class="settings-box">
                    <h3>Platform Settings</h3>
                    <p>Configure platform-wide settings</p>

                    <label for="platform_name">Platform Name</label>
                    <input type="text" name="platform_name" id="platform_name" value="<?php echo htmlspecialchars($settings['platform_name']); ?>">

                    <label for="commission_rate">Commission Rate (%)</label>
                    <input type="number" name="commission_rate" id="commission_rate" value="<?php echo htmlspecialchars($settings['commission_rate']); ?>">

                    <label for="support_email">Support Email</label>
                    <input type="email" name="support_email" id="support_email" value="<?php echo htmlspecialchars($settings['support_email']); ?>">

                    <button type="submit" name="save_platform_settings">Save Settings</button>
                </form>

                <!-- Security Settings -->
                <form method="post" class="settings-box">
                    <h3>Security Settings</h3>
                    <p>Manage platform security configurations</p>

                    <label for="min_password_length">Minimum Password Length</label>
                    <input type="number" name="min_password_length" id="min_password_length" value="<?php echo htmlspecialchars($settings['min_password_length']); ?>">

                    <label for="session_timeout">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" id="session_timeout" value="<?php echo htmlspecialchars($settings['session_timeout']); ?>">

                    <label for="max_login_attempts">Max Login Attempts</label>
                    <input type="number" name="max_login_attempts" id="max_login_attempts" value="<?php echo htmlspecialchars($settings['max_login_attempts']); ?>">

                    <button type="submit" name="update_security_settings">Update Security Settings</button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
