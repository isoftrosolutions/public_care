<?php
require_once __DIR__ . '/helpers.php';

$conn = getDB();
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = min(50, isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12);

$where = [];
if ($category > 0) {
    $where[] = "p.category_id = " . (int)$category;
}
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $where[] = "(p.name LIKE '%$safe%' OR p.description LIKE '%$safe%')";
}
if ($min_price > 0) {
    $where[] = "p.price >= " . (float)$min_price;
}
if ($max_price > 0) {
    $where[] = "p.price <= " . (float)$max_price;
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$order = 'ORDER BY p.created_at DESC';
switch ($sort) {
    case 'price_asc': $order = 'ORDER BY p.price ASC'; break;
    case 'price_desc': $order = 'ORDER BY p.price DESC'; break;
    case 'rating': $order = 'ORDER BY p.rating DESC'; break;
    case 'name': $order = 'ORDER BY p.name ASC'; break;
}

$count_result = $conn->query("SELECT COUNT(*) as total FROM products p $where_clause");
$total = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, (int)ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$result = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where_clause
    $order
    LIMIT $per_page OFFSET $offset
");

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = $row['image_url'] ?: '';
        $row['price'] = (float)$row['price'];
        $row['compare_price'] = $row['compare_price'] ? (float)$row['compare_price'] : null;
        $row['rating'] = (float)$row['rating'];
        $row['reviews_count'] = (int)$row['reviews_count'];
        $row['stock'] = (int)$row['stock'];
        $row['is_bestseller'] = (bool)$row['is_bestseller'];
        $products[] = $row;
    }
}

jsonResponse([
    'success' => true,
    'data' => $products,
    'pagination' => [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
    ],
]);
