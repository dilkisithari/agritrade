<?php
header('Content-Type: application/json');
include '../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$email = mysqli_real_escape_string($conn, $data['email']);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Check if newsletter table exists, create if not
$check_table_query = "SHOW TABLES LIKE 'newsletter_subscribers'";
$table_result = mysqli_query($conn, $check_table_query);

if (mysqli_num_rows($table_result) == 0) {
    $create_table_query = "CREATE TABLE newsletter_subscribers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_table_query);
}

// Check if email already subscribed
$check_query = "SELECT * FROM newsletter_subscribers WHERE email = '$email'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter']);
    exit;
}

// Add email to subscribers
$insert_query = "INSERT INTO newsletter_subscribers (email) VALUES ('$email')";

if (mysqli_query($conn, $insert_query)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to our newsletter']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to subscribe: ' . mysqli_error($conn)]);
}
?>
