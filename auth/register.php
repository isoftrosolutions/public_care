<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $errors['general'] = 'Invalid form submission. Please try again.';
    return;
}

$form_data['full_name'] = trim($_POST['full_name'] ?? '');
$form_data['email'] = trim($_POST['email'] ?? '');
$form_data['mobile'] = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$form_data['full_name']) {
    $errors['full_name'] = 'Full name is required.';
}
if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'A valid email address is required.';
}
if (!$form_data['mobile']) {
    $errors['mobile'] = 'Mobile number is required.';
}
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, and a number.';
}
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

if (!empty($errors)) {
    return;
}

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $form_data['email']);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    $errors['email'] = 'An account with this email already exists.';
    return;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')");
$stmt->bind_param("ssss", $form_data['full_name'], $form_data['email'], $form_data['mobile'], $hashed);
if ($stmt->execute()) {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $form_data['full_name'];
    $_SESSION['role'] = 'customer';
    header('Location: ' . BASE_URL . '/index.php');
    exit;
} else {
    $errors['general'] = 'Registration failed. Please try again.';
}
