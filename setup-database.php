<?php
// One-click database setup: imports docs/agritrade.sql (the schema all pages are written for).
define('AGRI_SKIP_SCHEMA_CHECK', true);
include 'config/database.php';

$msg = '';
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = file_get_contents(__DIR__ . '/docs/agritrade.sql');
    if ($sql === false) {
        $msg = 'Could not read docs/agritrade.sql';
    } else {
        // Drop all existing tables so the import starts clean
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
        $tables = mysqli_query($conn, "SHOW TABLES");
        while ($t = mysqli_fetch_row($tables)) {
            mysqli_query($conn, "DROP TABLE IF EXISTS `" . $t[0] . "`");
        }
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

        $ok = mysqli_multi_query($conn, $sql);
        $err = '';
        if ($ok) {
            do {
                if ($r = mysqli_store_result($conn)) mysqli_free_result($r);
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            if (mysqli_errno($conn)) $err = mysqli_error($conn);
        } else {
            $err = mysqli_error($conn);
        }
        if ($err) {
            $msg = 'Import error: ' . $err;
        } else {
            // make sure the admin login is admin / admin123
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = 'admin'");
            mysqli_stmt_bind_param($stmt, "s", $hash);
            mysqli_stmt_execute($stmt);
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>AgriTrade Database Setup</title></head>
<body style="font-family:Arial;max-width:600px;margin:60px auto;padding:20px;border:1px solid #ccc;border-radius:8px">
<h2>AgriTrade database setup</h2>
<?php if ($done): ?>
    <p style="color:green"><b>Done!</b> Database imported. Admin login: <b>admin / admin123</b></p>
    <p><a href="index.php">Go to home page</a></p>
<?php else: ?>
    <?php if ($msg) echo '<p style="color:red">' . htmlspecialchars($msg) . '</p>'; ?>
    <p><b>Warning:</b> this deletes all existing tables in the <code>agritrade</code> database and re-creates them from <code>docs/agritrade.sql</code>. Users registered in the old database will need to register again.</p>
    <form method="post"><button type="submit" style="background:#4caf50;color:#fff;border:0;padding:10px 18px;border-radius:5px;cursor:pointer">Import database now</button></form>
<?php endif; ?>
</body></html>
