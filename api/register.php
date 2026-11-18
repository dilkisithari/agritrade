<?php
header('Content-Type: application/json');
include '../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
$role = mysqli_real_escape_string($conn, $_POST['role']);
$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$address = mysqli_real_escape_string($conn, $_POST['address']);

// Validate input
$errors = [];

if (empty($username)) {
    $errors[] = "Username is required";
}

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if (empty($role)) {
    $errors[] = "Role is required";
}

if (empty($full_name)) {
    $errors[] = "Full name is required";
}

// Check if username already exists
$check_username = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $check_username);

if (mysqli_num_rows($result) > 0) {
    $errors[] = "Username already exists";
}

// Check if email already exists
$check_email = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $check_email);

if (mysqli_num_rows($result) > 0) {
    $errors[] = "Email already exists";
}

// If there are errors, return them
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database
$query = "INSERT INTO users (username, email, password, role, full_name, phone, address) 
          VALUES ('$username', '$email', '$hashed_password', '$role', '$full_name', '$phone', '$address')";

if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($conn)]);
}
?>
