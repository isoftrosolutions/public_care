<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$credential = $input['credential'] ?? $_POST['credential'] ?? '';

if (!$credential) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'No credential provided.']);
    exit;
}

$clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';

$response = @file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential));
if ($response === false) {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Token verification failed.']);
    exit;
}

$payload = json_decode($response, true);

if (($payload['aud'] ?? '') !== $clientId) {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Token audience mismatch.']);
    exit;
}

if (empty($payload['email'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Email not provided by Google.']);
    exit;
}

$googleId = $payload['sub'] ?? '';
$email = $payload['email'];
$name = $payload['name'] ?? explode('@', $email)[0];

$db = getDB();

$stmt = $db->prepare("SELECT id, full_name, role FROM users WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $stmt = $db->prepare("SELECT id, full_name, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $stmt = $db->prepare("UPDATE users SET google_id = ? WHERE id = ?");
        $stmt->bind_param("si", $googleId, $user['id']);
        $stmt->execute();
    } else {
        $randomPass = bin2hex(random_bytes(20));
        $hashed = password_hash($randomPass, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role, google_id) VALUES (?, ?, ?, 'customer', ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed, $googleId);
        $stmt->execute();
        $user = [
            'id' => $stmt->insert_id,
            'full_name' => $name,
            'role' => 'customer',
        ];
    }
}

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

$redirect = $_SESSION['redirect_after_login'] ?? ($user['role'] === 'admin' ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/index.php');
unset($_SESSION['redirect_after_login']);

echo json_encode(['success' => true, 'redirect' => $redirect]);
exit;
