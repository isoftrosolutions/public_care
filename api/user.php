<?php
require_once __DIR__ . '/helpers.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $db = getDB();

    if (!empty($input['full_name'])) {
        $name = trim($input['full_name']);
        $stmt = $db->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $user['user_id']);
        $stmt->execute();
        $_SESSION['user_name'] = $name;
    }

    if (!empty($input['mobile'])) {
        $mobile = trim($input['mobile']);
        $stmt = $db->prepare("UPDATE users SET mobile = ? WHERE id = ?");
        $stmt->bind_param("si", $mobile, $user['user_id']);
        $stmt->execute();
    }
}

$db = getDB();
$stmt = $db->prepare("SELECT id, full_name, email, mobile, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    jsonError('User not found.', 404);
}

$profile['id'] = (int)$profile['id'];

jsonResponse(['success' => true, 'data' => $profile]);
