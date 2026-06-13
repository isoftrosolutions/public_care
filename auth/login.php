<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $error = 'Invalid form submission. Please try again.';
    return;
}

$form_data['email'] = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$form_data['email'] || !$password) {
    $error = 'Please fill in all fields.';
    return;
}

$db = getDB();
$stmt = $db->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $form_data['email']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    if ($user['role'] === 'admin') {
        $redirect = BASE_URL . '/admin/dashboard.php';
    } else {
        $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php';
    }
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
} else {
    $error = 'Invalid email or password.';
}
