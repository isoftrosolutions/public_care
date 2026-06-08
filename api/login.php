<?php
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('POST method required.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$password = $input['password'] ?? $_POST['password'] ?? '';

if (!$email || !$password) {
    jsonError('Email and password are required.', 400);
}

$db = getDB();
$stmt = $db->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    jsonError('Invalid email or password.', 401);
}

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

jsonResponse([
    'success' => true,
    'data' => [
        'user_id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
    ],
]);
