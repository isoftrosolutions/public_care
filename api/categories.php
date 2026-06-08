<?php
require_once __DIR__ . '/helpers.php';

$conn = getDB();
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$categories = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = $row['image_url'] ?: '';
        $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE category_id = " . (int)$row['id']);
        $row['product_count'] = $count ? (int)$count->fetch_assoc()['c'] : 0;
        $categories[] = $row;
    }
}

jsonResponse(['success' => true, 'data' => $categories]);
