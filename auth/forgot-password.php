<?php
session_start();
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section style="padding: 50px 0;">
            <div class="container" style="max-width:600px;">
                <h2>Forgot your password?</h2>
                <p style="margin:15px 0;">Automatic password reset by email is not set up yet. Please contact the AgriTrade administrator (admin@agritrade.com) and they will reset your password for you.</p>
                <a href="login.php" class="btn btn-primary">Back to Login</a>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
