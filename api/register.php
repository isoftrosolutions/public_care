<?php
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('POST method required.', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$full_name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$mobile = trim($input['mobile'] ?? '');
$password = $input['password'] ?? '';

if (!$full_name) {
    jsonError('Full name is required.', 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Valid email is required.', 400);
}
if (strlen($password) < 8) {
    jsonError('Password must be at least 8 characters.', 400);
}

$db = getDB();

$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    jsonError('An account with this email already exists.', 409);
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')");
$stmt->bind_param("ssss", $full_name, $email, $mobile, $hashed);

if (!$stmt->execute()) {
    jsonError('Registration failed.', 500);
}

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['user_id'] = $stmt->insert_id;
$_SESSION['user_name'] = $full_name;
$_SESSION['role'] = 'customer';

jsonResponse([
    'success' => true,
    'data' => [
        'user_id' => $stmt->insert_id,
        'full_name' => $full_name,
        'email' => $email,
        'role' => 'customer',
    ],
], 201);
