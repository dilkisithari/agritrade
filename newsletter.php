<?php
session_start();
include 'config/database.php';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$msg = 'Please enter a valid email address.';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $msg = 'You are already subscribed. Thank you!';
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO newsletter_subscribers (email) VALUES (?)");
            mysqli_stmt_bind_param($ins, "s", $email);
            $msg = mysqli_stmt_execute($ins) ? 'Thank you for subscribing!' : 'Sorry, something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - AgriTrade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <section style="padding: 50px 0;">
            <div class="container" style="max-width:600px;">
                <h2>Newsletter</h2>
                <p style="margin:15px 0;"><?php echo htmlspecialchars($msg); ?></p>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
