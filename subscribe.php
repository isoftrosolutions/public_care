<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ' . BASE_URL . '/index.php?newsletter=error');
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . BASE_URL . '/index.php?newsletter=invalid');
    exit;
}

$db = getDB();
$db->query("CREATE TABLE IF NOT EXISTS subscribers (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$stmt = $db->prepare("INSERT INTO subscribers (email) VALUES (?)");
$stmt->bind_param('s', $email);
if ($stmt->execute()) {
    header('Location: ' . BASE_URL . '/index.php?newsletter=success');
} else {
    header('Location: ' . BASE_URL . '/index.php?newsletter=exists');
}
exit;
