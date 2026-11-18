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

$user_id = intval($data['user_id']);
$username = mysqli_real_escape_string($conn, $data['username']);
$role = mysqli_real_escape_string($conn, $data['role']);

if ($user_id <= 0 || empty($username) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if user exists with the provided credentials
$user_query = "SELECT * FROM users WHERE id = $user_id AND username = '$username' AND role = '$role'";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

// User is valid
echo json_encode(['success' => true, 'message' => 'Token verified']);
?>
