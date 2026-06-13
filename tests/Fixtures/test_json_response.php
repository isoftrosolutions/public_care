<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION = ['csrf_token' => 'test'];

require_once __DIR__ . '/../../api/helpers.php';

$action = $argv[1] ?? 'response';

if ($action === 'error') {
    jsonError($argv[2] ?? 'Error', (int)($argv[3] ?? 400));
} elseif ($action === 'unicode') {
    jsonResponse(['msg' => 'होम'], 200);
} elseif ($action === 'auth-exit') {
    $_SESSION = ['csrf_token' => 'test'];
    requireAuth();
} elseif ($action === 'auth-return') {
    $_SESSION = [
        'user_id' => (int)($argv[2] ?? 0),
        'user_name' => $argv[3] ?? '',
        'role' => $argv[4] ?? 'customer',
        'csrf_token' => 'token',
    ];
    $result = requireAuth();
    echo json_encode($result) . "\n";
} else {
    jsonResponse(['key' => 'value'], 200);
}
