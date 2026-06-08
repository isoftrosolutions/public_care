<?php
require_once __DIR__ . '/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    jsonError('Product ID is required.', 400);
}

$conn = getDB();
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    jsonError('Product not found.', 404);
}

$product['image_url'] = $product['image_url'] ?: '';
$product['price'] = (float)$product['price'];
$product['compare_price'] = $product['compare_price'] ? (float)$product['compare_price'] : null;
$product['rating'] = (float)$product['rating'];
$product['reviews_count'] = (int)$product['reviews_count'];
$product['stock'] = (int)$product['stock'];
$product['is_bestseller'] = (bool)$product['is_bestseller'];

$reviews_result = $conn->query("
    SELECT r.*, u.full_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.product_id = $id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reviews = [];
if ($reviews_result) {
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
}
$product['reviews'] = $reviews;

jsonResponse(['success' => true, 'data' => $product]);
