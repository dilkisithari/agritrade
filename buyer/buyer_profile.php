<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$query = "SELECT * FROM users WHERE id = $buyer_id LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
$buyer = mysqli_fetch_assoc($result);

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $updateQuery = "UPDATE users SET 
        username = '$username',
        email = '$email',
        phone = '$phone',
        address = '$address'
        WHERE id = $buyer_id";

    if (mysqli_query($conn, $updateQuery)) {
        header("Location: buyer_profile.php");
        exit();
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-content {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .profile-box {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .profile-box h2 {
            margin-bottom: 25px;
        }

        .profile-field {
            margin-bottom: 15px;
            text-align: left;
        }

        .profile-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .profile-field input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f9f9f9;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #888;
        }

        .btn-secondary:hover {
            background-color: #666;
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
            <li><a href="payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
            <li><a href="buyer_profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <div class="profile-box">
            <h2>My Profile</h2>
            <form method="POST">
                <div class="profile-field">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($buyer['username']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($buyer['email']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($buyer['phone']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($buyer['address']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label>Joined On</label>
                    <input type="text" value="<?php echo date('M d, Y', strtotime($buyer['created_at'])); ?>" readonly>
                </div>

                <button type="button" class="btn" id="edit-btn"><i class="fas fa-edit"></i> Edit Profile</button>
                <button type="submit" class="btn btn-secondary" id="save-btn" style="display: none;"><i class="fas fa-save"></i> Save</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    document.getElementById('edit-btn').addEventListener('click', function() {
        const inputs = document.querySelectorAll('input[name="username"], input[name="email"], input[name="phone"], input[name="address"]');
        inputs.forEach(input => input.removeAttribute('readonly'));
        document.getElementById('save-btn').style.display = 'inline-block';
        this.style.display = 'none';
    });
</script>

</body>
</html>
