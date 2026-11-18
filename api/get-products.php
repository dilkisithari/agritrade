<?php
header('Content-Type: application/json');
session_start();
include '../config/database.php';

// Only buyers can fetch products
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'products' => [],
        'total_pages' => 0
    ]);
    exit();
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$sort = $_GET['sort'] ?? 'name_asc';

// Base query with join
$whereClauses = ['1=1']; // always true for easier AND concatenation
$params = [];
$types = '';

// Add search filter
if ($search !== '') {
    $whereClauses[] = "(p.name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

// Add category filter
if ($category !== '') {
    $whereClauses[] = "p.category = ?";
    $params[] = $category;
    $types .= 's';
}

$whereSql = implode(' AND ', $whereClauses);

// Count total products
$countSql = "SELECT COUNT(*) as total 
             FROM products p 
             JOIN users u ON p.supplier_id = u.id
             WHERE $whereSql";
$stmt = mysqli_prepare($conn, $countSql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$countResult = mysqli_stmt_get_result($stmt);
$total = 0;
if ($row = mysqli_fetch_assoc($countResult)) {
    $total = (int)$row['total'];
}
mysqli_stmt_close($stmt);

$total_pages = ceil($total / $limit);

// Build main query
$orderBy = match ($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_desc' => 'p.name DESC',
    'newest' => 'p.created_at DESC',
    default => 'p.name ASC',
};

$selectSql = "SELECT p.*, u.username 
              FROM products p 
              JOIN users u ON p.supplier_id = u.id
              WHERE $whereSql
              ORDER BY $orderBy
              LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $selectSql);

// Bind params + limit and offset
if ($types !== '') {
    // add limit and offset types
    $fullTypes = $types . 'ii';
    $fullParams = [...$params, $limit, $offset];
    mysqli_stmt_bind_param($stmt, $fullTypes, ...$fullParams);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Prefix image_url with relative path if exists, else placeholder
    $row['image_url'] = !empty($row['image_url']) ? '../' . $row['image_url'] : '../assets/images/product-placeholder.jpg';
    $products[] = $row;
}

mysqli_stmt_close($stmt);
$conn->close();

echo json_encode([
    'success' => true,
    'products' => $products,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_products' => $total
]);
