<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$database   = "agritrade";

// Try the port this project was set up with (3307) and fall back to the MySQL default (3306)
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($servername, $username, $password, "", 3307);
if ($conn->connect_errno) {
    $conn = @new mysqli($servername, $username, $password, "", 3306);
}
if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error . ". Make sure MySQL is running in XAMPP.");
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database`");
mysqli_select_db($conn, $database);
mysqli_set_charset($conn, "utf8mb4");

// The pages were written for the schema in docs/agritrade.sql.
// If the database has a different/older structure, send the user to the one-click setup page.
if (!defined('AGRI_SKIP_SCHEMA_CHECK')) {
    $ok = false;
    $chk = mysqli_query($conn, "SELECT COUNT(*) AS c FROM information_schema.columns
        WHERE table_schema = '$database' AND table_name = 'orders' AND column_name IN ('total_price','supplier_id','delivery_status')");
    if ($chk) {
        $row = mysqli_fetch_assoc($chk);
        $ok = ((int)$row['c'] === 3);
    }
    if (!$ok) {
        $root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $base = (strpos($root, $doc) === 0) ? substr($root, strlen($doc)) : '';
        header('Content-Type: text/html; charset=utf-8');
        die('<div style="font-family:Arial;max-width:600px;margin:60px auto;padding:20px;border:1px solid #ccc;border-radius:8px">'
          . '<h2>Database needs to be set up</h2>'
          . '<p>Your MySQL database has an old/different table structure than this project expects.</p>'
          . '<p><a href="' . $base . '/setup-database.php" style="background:#4caf50;color:#fff;padding:10px 18px;border-radius:5px;text-decoration:none">Set up database now</a></p></div>');
    }
}

// If the logged-in user no longer exists (e.g. database was re-imported), end the old session
// so pages don't try to read data of a missing user.
if (!defined('AGRI_SKIP_SCHEMA_CHECK') && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
    $__uid = (int)$_SESSION['user_id'];
    $__u = mysqli_query($conn, "SELECT id FROM users WHERE id = $__uid LIMIT 1");
    if ($__u && mysqli_num_rows($__u) === 0) {
        $_SESSION = [];
        session_destroy();
        $__root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $__doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $__base = (strpos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '';
        header('Location: ' . $__base . '/auth/login.php');
        exit();
    }
}